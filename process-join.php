<?php
/**
 * R-RIC · process-join.php
 *
 * Handles every form on the site (form_type decides which):
 *   join      → contact.html #joinForm      → table `members`
 *   contact   → contact.html #contactForm   → table `messages`
 *   donation  → support.html #donationForm  → table `donations`
 *
 * Flow: accept POST only → sanitize (trim + htmlspecialchars) → validate
 * server-side → save with PDO prepared statements (or JSON fallback) →
 * render a confirmation or error page.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/layout.php';

// 1. Only accept POST requests; anything else goes back to the form.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: contact.html#join');
    exit;
}

$type = post('form_type');
$backLinks = [
    'join'     => ['contact.html#join', 'contact.html'],
    'contact'  => ['contact.html#message', 'contact.html'],
    'donation' => ['support.html#donate', 'support.html'],
];

if (!isset($backLinks[$type])) {
    http_response_code(400);
    page_header('Invalid request');
    echo '<section class="section-padding"><div class="container"><p>Unknown form. Please use the forms on our <a href="contact.html">contact page</a>.</p></div></section>';
    page_footer();
    exit;
}

[$backUrl, $activePage] = $backLinks[$type];

// 2. Honeypot: bots fill the hidden "website" field. Pretend it worked.
if (post('website') !== '') {
    header('Location: index.html');
    exit;
}

// 3. Sanitize + validate.
switch ($type) {
    case 'join':
        [$data, $errors] = validate_join();
        break;
    case 'contact':
        [$data, $errors] = validate_contact();
        break;
    default:
        [$data, $errors] = validate_donation();
}

// 4. Save (only when valid).
$result = null;
if (count($errors) === 0) {
    $result = save_submission($type, $data);
    if (isset($result['error'])) {
        $errors['duplicate'] = $result['error'];
    }
}

// 5. Render the response page.
if (count($errors) > 0) {
    http_response_code(422);
    page_header('Please check your details', $activePage);
    ?>
            <section class="section-padding">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-8 col-12 mx-auto">
                            <div class="join-panel">
                                <div class="form-summary" role="alert">
                                    <i class="bi-exclamation-triangle-fill me-2"></i>
                                    We could not save your submission because of <?= count($errors) ?> <?= count($errors) === 1 ? 'problem' : 'problems' ?>:
                                </div>

                                <ul class="mb-4">
                                    <?php foreach ($errors as $message): ?>
                                        <li><?= e($message) ?></li>
                                    <?php endforeach; ?>
                                </ul>

                                <button type="button" class="btn custom-btn me-2" onclick="history.back()">
                                    <i class="bi-arrow-left me-1"></i>Go back and fix
                                </button>
                                <a href="<?= e($backUrl) ?>" class="btn custom-btn custom-border-btn">Start again</a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
    <?php
    page_footer();
    exit;
}

// Success. Every value in $data was passed through clean() (htmlspecialchars)
// in includes/functions.php, so it is safe to echo directly.
$firstName = explode(' ', $data['full_name'])[0];

if ($type === 'join') {
    $memberCount = count_rows('members');
    page_header('Welcome to R-RIC!', $activePage);
    ?>
            <section class="section-padding">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-8 col-12 mx-auto">
                            <div class="join-panel text-center mb-4">
                                <i class="bi-check-circle-fill display-4 text-success d-block mb-3"></i>
                                <h2 class="mb-3">Welcome aboard, <?= $firstName ?>!</h2>
                                <p class="mb-1">Your registration <strong>#<?= $result['id'] ?></strong> has been saved.</p>
                                <p class="mb-0">You are one of <strong><?= $memberCount ?></strong> registered <?= $memberCount === 1 ? 'member' : 'members' ?>. We'll e-mail <strong><?= $data['email'] ?></strong> within two working days.</p>
                                <?php if ($data['event_interest']): ?>
                                    <p class="rsvp-note mt-3 mb-0"><i class="bi-calendar-check me-1"></i>Your seat request for <strong><?= $data['event_interest'] ?></strong> has been noted.</p>
                                <?php endif; ?>
                            </div>

                            <div class="table-responsive mb-4">
                                <table class="table">
                                    <caption class="table-caption">Summary of your registration</caption>
                                    <tbody>
                                        <tr><th scope="row">Name</th><td><?= $data['full_name'] ?></td></tr>
                                        <tr><th scope="row">Student ID</th><td><?= $data['student_id'] ?></td></tr>
                                        <tr><th scope="row">E-mail</th><td><?= $data['email'] ?></td></tr>
                                        <tr><th scope="row">Programme</th><td><?= $data['course'] ?>, <?= $data['year_of_study'] === 5 ? 'Postgraduate' : 'Year ' . $data['year_of_study'] ?></td></tr>
                                        <tr><th scope="row">Tier</th><td><?= TIERS[$data['tier']] ?></td></tr>
                                        <tr><th scope="row">Interests</th><td><?= $data['interests'] ?></td></tr>
                                        <tr><th scope="row">Saved to</th><td><?= $result['storage'] ?></td></tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="text-center">
                                <a href="members.php" class="btn custom-btn me-2 mb-2">See the member wall</a>
                                <a href="events.html" class="btn custom-btn custom-border-btn mb-2">Browse events</a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
    <?php
} elseif ($type === 'contact') {
    page_header('Message sent', $activePage);
    ?>
            <section class="section-padding">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-7 col-12 mx-auto">
                            <div class="join-panel text-center">
                                <i class="bi-envelope-check-fill display-4 text-success d-block mb-3"></i>
                                <h2 class="mb-3">Thanks, <?= $firstName ?>!</h2>
                                <p>Your message about <strong><?= $data['subject'] ?></strong> (reference <strong>#<?= $result['id'] ?></strong>) has reached the committee. We'll reply to <strong><?= $data['email'] ?></strong> within two working days.</p>
                                <blockquote class="text-start border-start border-3 ps-3 my-4"><?= nl2br($data['message']) ?></blockquote>
                                <a href="index.html" class="btn custom-btn me-2 mb-2">Back to home</a>
                                <a href="events.html" class="btn custom-btn custom-border-btn mb-2">Browse events</a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
    <?php
} else {
    page_header('Thank you for your pledge', $activePage);
    ?>
            <section class="section-padding">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-7 col-12 mx-auto">
                            <div class="join-panel text-center mb-4">
                                <i class="bi-heart-fill display-4 text-danger d-block mb-3"></i>
                                <h2 class="mb-3">Thank you, <?= $firstName ?>!</h2>
                                <p>Your pledge of <strong>$<?= number_format($data['amount']) ?></strong> to <strong><?= e(FUNDS[$data['fund']]) ?></strong> has been recorded (reference <strong>#<?= $result['id'] ?></strong>).</p>
                                <p class="mb-0">No money has been taken. We'll e-mail <strong><?= $data['email'] ?></strong> to confirm.</p>
                            </div>

                            <aside class="meeting-card">
                                <h3 class="h5 mb-3">How to complete your gift (<?= e(PAYMENT_METHODS[$data['payment_method']]) ?>)</h3>
                                <?php if ($data['payment_method'] === 'ecocash'): ?>
                                    <p class="mb-0">Send <strong>$<?= number_format($data['amount']) ?></strong> via EcoCash to <strong>+263 712 499 890</strong> and use reference <strong>RRIC-<?= $result['id'] ?></strong>.</p>
                                <?php elseif ($data['payment_method'] === 'bank'): ?>
                                    <p class="mb-0">The treasurer will e-mail you the club's bank details. Please quote reference <strong>RRIC-<?= $result['id'] ?></strong>.</p>
                                <?php else: ?>
                                    <p class="mb-0">Bring your gift to any Wednesday meetup (4-6 PM, Innovation Hub, Room 204) and quote reference <strong>RRIC-<?= $result['id'] ?></strong> for a receipt.</p>
                                <?php endif; ?>
                            </aside>

                            <div class="text-center mt-4">
                                <a href="index.html" class="btn custom-btn">Back to home</a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
    <?php
}

page_footer();
