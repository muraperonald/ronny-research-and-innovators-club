<?php
/**
 * R-RIC · shared PHP helpers
 *
 *   clean()             trim + htmlspecialchars a submitted value
 *   get_db()            PDO connection (or null when MySQL is unavailable)
 *   validate_*()        server-side validation, mirrors js/main.js
 *   save_submission()   insert via PDO prepared statements, JSON fallback
 *   fetch_members()     read members back for members.php
 */

require_once __DIR__ . '/config.php';

/* ---------------------------------------------------------------------
   Sanitizing
   --------------------------------------------------------------------- */

/** Raw POST value as a trimmed string ('' when missing or not a string). */
function post(string $key): string
{
    $value = $_POST[$key] ?? '';
    return is_string($value) ? trim($value) : '';
}

/**
 * Sanitize a value for storage and display: trim whitespace and convert
 * HTML special characters to entities so no submitted markup can ever run.
 * Validation runs on the trimmed raw value first; clean() is applied to
 * what gets stored, so stored values are already safe to echo.
 */
function clean(string $value): string
{
    return htmlspecialchars(trim($value), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Escape helper for any value that did NOT come through clean(). */
function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}


/* ---------------------------------------------------------------------
   Database (PDO)
   --------------------------------------------------------------------- */

/**
 * Returns a shared PDO connection, creating the tables on first use.
 * Returns null (instead of crashing) when MySQL is not configured, so the
 * caller can fall back to JSON storage.
 */
function get_db(): ?PDO
{
    static $pdo = null;
    static $tried = false;

    if ($tried) {
        return $pdo;
    }
    $tried = true;

    if (!extension_loaded('pdo_mysql')) {
        return null;
    }

    $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';

    try {
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_TIMEOUT            => 5,
        ];

        if (DB_SSL !== '') {
            $options[PDO::MYSQL_ATTR_SSL_CA] = DB_SSL === 'true'
                ? '/etc/ssl/certs/ca-certificates.crt'
                : DB_SSL;
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = true;
        }

        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        create_tables($pdo);
    } catch (PDOException $ex) {
        error_log('R-RIC DB unavailable, using JSON fallback: ' . $ex->getMessage());
        $pdo = null;
    }

    return $pdo;
}

function create_tables(PDO $pdo): void
{
    $pdo->exec("CREATE TABLE IF NOT EXISTS members (
        id             INT UNSIGNED  NOT NULL AUTO_INCREMENT,
        full_name      VARCHAR(80)   NOT NULL,
        student_id     VARCHAR(20)   NOT NULL,
        email          VARCHAR(120)  NOT NULL,
        phone          VARCHAR(20)   NULL,
        course         VARCHAR(80)   NOT NULL,
        year_of_study  TINYINT UNSIGNED NOT NULL,
        tier           ENUM('explorer','builder','innovator') NOT NULL DEFAULT 'explorer',
        interests      VARCHAR(255)  NOT NULL,
        motivation     TEXT          NULL,
        event_interest VARCHAR(120)  NULL,
        created_at     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY uq_members_student_id (student_id),
        UNIQUE KEY uq_members_email (email)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS messages (
        id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
        full_name   VARCHAR(80)  NOT NULL,
        email       VARCHAR(120) NOT NULL,
        subject     VARCHAR(60)  NOT NULL,
        message     TEXT         NOT NULL,
        created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS donations (
        id              INT UNSIGNED  NOT NULL AUTO_INCREMENT,
        full_name       VARCHAR(80)   NOT NULL,
        email           VARCHAR(120)  NOT NULL,
        amount          DECIMAL(10,2) NOT NULL,
        fund            ENUM('general','lab','prizes','travel') NOT NULL DEFAULT 'general',
        payment_method  ENUM('ecocash','bank','cash') NOT NULL,
        message         VARCHAR(300)  NULL,
        is_anonymous    TINYINT(1)    NOT NULL DEFAULT 0,
        created_at      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
}


/* ---------------------------------------------------------------------
   Validation (each returns [cleanData, errors])
   --------------------------------------------------------------------- */

const COURSES = [
    'BSc. Computer Science',
    'BSc. Informatics & Computer Science',
    'BSc. Information Technology',
    'BSc. Software Engineering',
    'Business Information Technology',
    'Electrical & Electronic Engineering',
    'Data Science & Statistics',
    'Other programme',
];

const INTERESTS = [
    'Research Methods', 'Web Development', 'AI & Data Science',
    'Cybersecurity', 'Robotics & IoT', 'UI/UX Design',
];

const TIERS = ['explorer' => 'Explorer', 'builder' => 'Builder', 'innovator' => 'Innovator'];

const SUBJECTS = [
    'General enquiry', 'Research collaboration', 'Partnership or sponsorship',
    'Event or workshop idea', 'Photos for the gallery', 'Other',
];

const FUNDS = [
    'general' => 'Where needed most',
    'lab'     => 'Robotics & IoT lab kits',
    'prizes'  => 'Hackathon & symposium prizes',
    'travel'  => 'Conference travel',
];

const PAYMENT_METHODS = ['ecocash' => 'EcoCash', 'bank' => 'Bank transfer', 'cash' => 'Cash at a club meeting'];

function validate_name(string $name, array &$errors): void
{
    if ($name === '') {
        $errors['full_name'] = 'Please enter your full name.';
    } elseif (count(preg_split('/\s+/', $name)) < 2) {
        $errors['full_name'] = 'Please enter both your first and last name.';
    } elseif (!preg_match(NAME_PATTERN, $name)) {
        $errors['full_name'] = 'Names may only contain letters, spaces, apostrophes, dots and hyphens.';
    }
}

function validate_join(): array
{
    $errors = [];

    $name       = post('full_name');
    $studentId  = strtoupper(post('student_id'));
    $email      = strtolower(post('email'));
    $phone      = post('phone');
    $course     = post('course');
    $year       = post('year_of_study');
    $tier       = post('tier');
    $motivation = post('motivation');
    $event      = post('event_interest');
    $interests  = $_POST['interests'] ?? [];
    $interests  = is_array($interests) ? array_values(array_intersect($interests, INTERESTS)) : [];

    validate_name($name, $errors);

    if ($studentId === '') {
        $errors['student_id'] = 'Your student ID is required.';
    } elseif (!preg_match(STUDENT_ID_PATTERN, $studentId, $match)) {
        $errors['student_id'] = 'Use the format PROGRAMME/YEAR/NUMBER, e.g. ICS/2024/0123.';
    } else {
        $thisYear = (int) date('Y');
        $intake = (int) $match[1];
        if ($intake < $thisYear - 8 || $intake > $thisYear) {
            $errors['student_id'] = "The intake year must be between " . ($thisYear - 8) . " and $thisYear.";
        }
    }

    if ($email === '') {
        $errors['email'] = 'Your university e-mail address is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match(UNI_EMAIL_PATTERN, $email)) {
        $errors['email'] = 'Use your university e-mail (ending in .ac.xx or .edu).';
    }

    if ($phone !== '' && !preg_match(PHONE_PATTERN, $phone)) {
        $errors['phone'] = 'Enter a valid phone number, e.g. +263 712 345 678.';
    }

    if (!in_array($course, COURSES, true)) {
        $errors['course'] = 'Please choose your programme from the list.';
    }

    if (!in_array($year, ['1', '2', '3', '4', '5'], true)) {
        $errors['year_of_study'] = 'Please choose your year of study.';
    }

    if (!array_key_exists($tier, TIERS)) {
        $errors['tier'] = 'Choose a membership tier.';
    }

    if (count($interests) === 0) {
        $errors['interests'] = 'Pick at least one area of interest.';
    }

    if (mb_strlen($motivation) > 500) {
        $errors['motivation'] = 'Please keep your motivation under 500 characters.';
    }

    if (post('agree') !== 'yes') {
        $errors['agree'] = 'You must agree to the club code of conduct.';
    }

    $data = [
        'full_name'      => clean($name),
        'student_id'     => clean($studentId),
        'email'          => clean($email),
        'phone'          => $phone === '' ? null : clean($phone),
        'course'         => clean($course),
        'year_of_study'  => (int) $year,
        'tier'           => $tier,
        'interests'      => clean(implode(', ', $interests)),
        'motivation'     => $motivation === '' ? null : clean($motivation),
        'event_interest' => $event === '' ? null : clean(mb_substr($event, 0, 120)),
    ];

    return [$data, $errors];
}

function validate_contact(): array
{
    $errors = [];

    $name    = post('full_name');
    $email   = strtolower(post('email'));
    $subject = post('subject');
    $message = post('message');

    validate_name($name, $errors);

    if ($email === '') {
        $errors['email'] = 'Your e-mail address is required so we can reply.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid e-mail address.';
    }

    if (!in_array($subject, SUBJECTS, true)) {
        $errors['subject'] = 'Please choose a subject.';
    }

    $length = mb_strlen($message);
    if ($length < 20) {
        $errors['message'] = "Your message is too short ($length/20 characters minimum).";
    } elseif ($length > 1000) {
        $errors['message'] = 'Please keep your message under 1000 characters.';
    }

    $data = [
        'full_name' => clean($name),
        'email'     => clean($email),
        'subject'   => clean($subject),
        'message'   => clean($message),
    ];

    return [$data, $errors];
}

function validate_donation(): array
{
    $errors = [];

    $name    = post('full_name');
    $email   = strtolower(post('email'));
    $amount  = post('amount');
    $fund    = post('fund');
    $method  = post('payment_method');
    $message = post('message');

    validate_name($name, $errors);

    if ($email === '') {
        $errors['email'] = 'Your e-mail address is required so we can confirm your pledge.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid e-mail address.';
    }

    $amountValue = filter_var($amount, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 10000]]);
    if ($amountValue === false) {
        $errors['amount'] = 'Enter a whole amount between $1 and $10,000.';
    }

    if (!array_key_exists($fund, FUNDS)) {
        $errors['fund'] = 'Please choose a fund.';
    }

    if (!array_key_exists($method, PAYMENT_METHODS)) {
        $errors['payment_method'] = 'Please choose how you would like to pay.';
    }

    if (mb_strlen($message) > 300) {
        $errors['message'] = 'Please keep your message under 300 characters.';
    }

    $data = [
        'full_name'      => clean($name),
        'email'          => clean($email),
        'amount'         => $amountValue === false ? 0 : $amountValue,
        'fund'           => $fund,
        'payment_method' => $method,
        'message'        => $message === '' ? null : clean($message),
        'is_anonymous'   => post('anonymous') === 'yes' ? 1 : 0,
    ];

    return [$data, $errors];
}


/* ---------------------------------------------------------------------
   Storage: MySQL via PDO, JSON fallback
   --------------------------------------------------------------------- */

const TABLES = ['join' => 'members', 'contact' => 'messages', 'donation' => 'donations'];

/**
 * Saves a validated submission.
 * Returns ['id' => int, 'storage' => 'MySQL'|'JSON file'] on success,
 * or ['error' => 'message'] for a duplicate registration.
 */
function save_submission(string $type, array $data): array
{
    $table = TABLES[$type];
    $pdo = get_db();

    if ($pdo !== null) {
        // Column names come from our own whitelist (array keys above), never from user input.
        $columns = array_keys($data);
        $placeholders = array_map(fn ($c) => ':' . $c, $columns);
        $sql = "INSERT INTO $table (" . implode(', ', $columns) . ') VALUES (' . implode(', ', $placeholders) . ')';

        try {
            $stmt = $pdo->prepare($sql);
            foreach ($data as $column => $value) {
                $paramType = is_int($value) ? PDO::PARAM_INT : ($value === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
                $stmt->bindValue(':' . $column, $value, $paramType);
            }
            $stmt->execute();

            return ['id' => (int) $pdo->lastInsertId(), 'storage' => 'MySQL'];
        } catch (PDOException $ex) {
            if ($ex->getCode() === '23000') { // unique key violation
                return ['error' => 'That student ID or e-mail address is already registered. If you think this is a mistake, contact the committee.'];
            }
            error_log('R-RIC insert failed, using JSON fallback: ' . $ex->getMessage());
        }
    }

    return save_to_json($table, $data);
}

function json_path(string $table): string
{
    return DATA_DIR . '/' . $table . '.json';
}

/** Appends a record to data/<table>.json with an exclusive file lock. */
function save_to_json(string $table, array $data): array
{
    if (!is_dir(DATA_DIR) && !mkdir(DATA_DIR, 0755, true)) {
        return ['error' => 'The server could not save your submission. Please try again later.'];
    }

    $handle = fopen(json_path($table), 'c+');
    if ($handle === false) {
        return ['error' => 'The server could not save your submission. Please try again later.'];
    }

    flock($handle, LOCK_EX);
    $contents = stream_get_contents($handle);
    $records = json_decode($contents ?: '[]', true) ?: [];

    if ($table === 'members') {
        foreach ($records as $record) {
            if ($record['student_id'] === $data['student_id'] || $record['email'] === $data['email']) {
                flock($handle, LOCK_UN);
                fclose($handle);
                return ['error' => 'That student ID or e-mail address is already registered. If you think this is a mistake, contact the committee.'];
            }
        }
    }

    $id = count($records) > 0 ? max(array_column($records, 'id')) + 1 : 1;
    $records[] = ['id' => $id] + $data + ['created_at' => date('Y-m-d H:i:s')];

    ftruncate($handle, 0);
    rewind($handle);
    fwrite($handle, json_encode($records, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    fflush($handle);
    flock($handle, LOCK_UN);
    fclose($handle);

    return ['id' => $id, 'storage' => 'JSON file'];
}


/* ---------------------------------------------------------------------
   Retrieval
   --------------------------------------------------------------------- */

/** Returns all members, newest first, from MySQL or the JSON fallback. */
function fetch_members(): array
{
    $pdo = get_db();
    if ($pdo !== null) {
        $stmt = $pdo->query('SELECT id, full_name, course, year_of_study, tier, interests, created_at
                             FROM members ORDER BY created_at DESC, id DESC');
        return $stmt->fetchAll();
    }

    $file = json_path('members');
    if (!is_file($file)) {
        return [];
    }
    $records = json_decode((string) file_get_contents($file), true) ?: [];
    return array_reverse($records);
}

function count_rows(string $table): int
{
    $pdo = get_db();
    if ($pdo !== null) {
        return (int) $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();
    }
    $file = json_path($table);
    return is_file($file) ? count(json_decode((string) file_get_contents($file), true) ?: []) : 0;
}

/** "Amina Yusuf" → "Amina Y." for the public member wall. */
function public_name(string $fullName): string
{
    $decoded = html_entity_decode($fullName, ENT_QUOTES, 'UTF-8');
    $parts = preg_split('/\s+/', trim($decoded));
    $first = array_shift($parts);
    $initial = $parts ? mb_substr(end($parts), 0, 1) . '.' : '';
    return e(trim("$first $initial"));
}
