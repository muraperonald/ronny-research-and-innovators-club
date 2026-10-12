<?php
/**
 * R-RIC · shared layout for PHP pages (process-join.php, members.php).
 * Outputs the same <head>, navbar and footer as the static HTML pages.
 */

const NAV_ITEMS = [
    'index.html'   => 'Home',
    'about.html'   => 'About Us',
    'events.html'  => 'Events',
    'gallery.html' => 'Gallery',
    'contact.html' => 'Join / Contact',
    'support.html' => 'Support Us',
];

function page_header(string $title, string $active = ''): void
{
    ?>
<!doctype html>
<html lang="en" data-theme="light">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="noindex">
        <meta name="theme-color" content="#1B1F3B">

        <title><?= e($title) ?> | R-RIC · Ronny Research &amp; Innovation Club</title>

        <link rel="icon" type="image/png" href="images/favicon.png">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,wght@0,400;0,500;0,700;1,400&display=swap" rel="stylesheet">

        <link href="css/bootstrap.min.css" rel="stylesheet">
        <link href="css/bootstrap-icons.css" rel="stylesheet">
        <link href="css/templatemo-tiya-golf-club.css" rel="stylesheet">
        <link href="css/custom-styles.css?v=<?= filemtime(dirname(__DIR__) . '/css/custom-styles.css') ?>" rel="stylesheet">

        <script src="js/main.js?v=<?= filemtime(dirname(__DIR__) . '/js/main.js') ?>"></script>
    </head>

    <body>

        <a class="skip-link" href="#main-content">Skip to main content</a>

        <header class="site-header">
            <nav class="navbar navbar-expand-lg" aria-label="Main navigation">
                <div class="container">
                    <a class="navbar-brand d-flex align-items-center" href="index.html">
                        <img src="images/logo.svg" class="navbar-brand-image img-fluid" alt="R-RIC logo" width="48" height="48">
                        <span class="navbar-brand-text">
                            Ronny Research
                            <small>&amp; Innovation Club</small>
                        </span>
                    </a>

                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>

                    <div class="collapse navbar-collapse" id="navbarNav">
                        <ul class="navbar-nav ms-lg-auto">
                            <?php foreach (NAV_ITEMS as $href => $label): ?>
                                <li class="nav-item">
                                    <a class="nav-link<?= $href === $active ? ' active" aria-current="page' : '' ?>" href="<?= $href ?>"><?= $label ?></a>
                                </li>
                            <?php endforeach; ?>
                        </ul>

                        <div class="nav-actions d-flex align-items-center gap-2 ms-lg-3">
                            <button type="button" class="theme-toggle" id="themeToggle" aria-label="Switch to dark mode" aria-pressed="false">
                                <i class="bi-moon-stars-fill"></i>
                            </button>

                            <a class="btn custom-btn custom-border-btn" href="contact.html#join">Join R-RIC</a>
                        </div>
                    </div>
                </div>
            </nav>
        </header>

        <main id="main-content">

            <section class="hero-section hero-50 hero-contact d-flex justify-content-center align-items-center" aria-labelledby="page-title">
                <div class="section-overlay"></div>

                <div class="container">
                    <div class="row">
                        <div class="col-lg-8 col-12">
                            <h1 id="page-title" class="text-white mb-0"><?= e($title) ?></h1>
                        </div>
                    </div>
                </div>

                <svg viewBox="0 0 1962 178" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path fill="#ffffff" d="M 0 114 C 118.5 114 118.5 167 237 167 L 237 167 L 237 0 L 0 0 Z" stroke-width="0"></path> <path fill="#ffffff" d="M 236 167 C 373 167 373 128 510 128 L 510 128 L 510 0 L 236 0 Z" stroke-width="0"></path> <path fill="#ffffff" d="M 509 128 C 607 128 607 153 705 153 L 705 153 L 705 0 L 509 0 Z" stroke-width="0"></path><path fill="#ffffff" d="M 704 153 C 812 153 812 113 920 113 L 920 113 L 920 0 L 704 0 Z" stroke-width="0"></path><path fill="#ffffff" d="M 919 113 C 1048.5 113 1048.5 148 1178 148 L 1178 148 L 1178 0 L 919 0 Z" stroke-width="0"></path><path fill="#ffffff" d="M 1177 148 C 1359.5 148 1359.5 129 1542 129 L 1542 129 L 1542 0 L 1177 0 Z" stroke-width="0"></path><path fill="#ffffff" d="M 1541 129 C 1751.5 129 1751.5 138 1962 138 L 1962 138 L 1962 0 L 1541 0 Z" stroke-width="0"></path></svg>
            </section>
    <?php
}

function page_footer(): void
{
    ?>
        </main>

        <footer class="site-footer">
            <div class="container">
                <div class="row">
                    <div class="col-lg-4 col-12 me-auto mb-5 mb-lg-0">
                        <a class="navbar-brand d-flex align-items-center" href="index.html">
                            <img src="images/logo.svg" class="navbar-brand-image img-fluid" alt="" width="48" height="48">
                            <span class="navbar-brand-text">
                                Ronny Research
                                <small>&amp; Innovation Club</small>
                            </span>
                        </a>
                        <p class="footer-tagline">A student-run community where we research, build and launch ideas that matter on campus and beyond.</p>
                    </div>

                    <nav class="col-lg-2 col-md-4 col-6 mb-4 mb-lg-0" aria-label="Footer navigation">
                        <h5 class="site-footer-title mb-3">Explore</h5>
                        <ul class="footer-links">
                            <?php foreach (NAV_ITEMS as $href => $label): ?>
                                <li><a href="<?= $href ?>"><?= $label ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </nav>

                    <div class="col-lg-3 col-md-4 col-6 mb-4 mb-lg-0">
                        <h5 class="site-footer-title mb-3">Contact</h5>
                        <p class="mb-2"><a href="mailto:ronaldmurape@gmail.com">ronaldmurape@gmail.com</a></p>
                        <p><a href="tel:+263712499890">+263 712 499 890</a></p>
                    </div>

                    <div class="col-lg-2 col-md-4 col-12">
                        <p class="copyright-text mb-1">&copy; <span id="year"><?= date('Y') ?></span> R-RIC. Designed by mronny</p>
                        <p class="copyright-text">Designer base: @muraperonnydesigns</p>
                    </div>
                </div>
            </div>

            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320" aria-hidden="true"><path fill="#0E9384" fill-opacity="1" d="M0,224L34.3,192C68.6,160,137,96,206,90.7C274.3,85,343,139,411,144C480,149,549,107,617,122.7C685.7,139,754,213,823,240C891.4,267,960,245,1029,224C1097.1,203,1166,181,1234,160C1302.9,139,1371,117,1406,106.7L1440,96L1440,320L1405.7,320C1371.4,320,1303,320,1234,320C1165.7,320,1097,320,1029,320C960,320,891,320,823,320C754.3,320,686,320,617,320C548.6,320,480,320,411,320C342.9,320,274,320,206,320C137.1,320,69,320,34,320L0,320Z"></path></svg>
        </footer>

        <button type="button" class="back-to-top" id="backToTop" aria-label="Back to top">
            <i class="bi-arrow-up"></i>
        </button>

        <script src="js/jquery.min.js"></script>
        <script src="js/bootstrap.bundle.min.js"></script>
        <script src="js/jquery.sticky.js"></script>
    </body>
</html>
    <?php
}
