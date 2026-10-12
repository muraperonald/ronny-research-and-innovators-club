<?php
/**
 * R-RIC · members.php: the public "member wall".
 *
 * Retrieves registrations from MySQL (or the JSON fallback) and lists them.
 * Only first names + last initials and non-sensitive fields are shown;
 * student IDs, e-mails and phone numbers are never displayed publicly.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/layout.php';

$members = fetch_members();

// Tally members per tier with a loop (shown as summary badges).
$tierCounts = array_fill_keys(array_keys(TIERS), 0);
foreach ($members as $member) {
    if (isset($tierCounts[$member['tier']])) {
        $tierCounts[$member['tier']]++;
    }
}

page_header('Member wall', 'contact.html');
?>
            <section class="section-padding">
                <div class="container">
                    <div class="row mb-4 align-items-end">
                        <div class="col-lg-7 col-12">
                            <span class="section-label">Our community</span>
                            <h2 class="mb-2"><?= count($members) ?> registered <?= count($members) === 1 ? 'member' : 'members' ?></h2>
                            <p class="mb-0">Storage in use: <strong><?= get_db() !== null ? 'MySQL database' : 'JSON file fallback' ?></strong></p>
                        </div>
                        <div class="col-lg-5 col-12 text-lg-end mt-3 mt-lg-0">
                            <?php foreach ($tierCounts as $tier => $total): ?>
                                <span class="event-badge position-static d-inline-block mb-1 event-badge-<?= $tier === 'explorer' ? 'meetup' : ($tier === 'builder' ? 'workshop' : 'talk') ?>">
                                    <?= TIERS[$tier] ?>: <?= $total ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <?php if (count($members) === 0): ?>
                        <div class="empty-state text-center">
                            <i class="bi-people display-6 d-block mb-3"></i>
                            <p class="mb-3">No members yet. Be the first!</p>
                            <a href="contact.html#join" class="btn custom-btn">Join R-RIC</a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <caption class="table-caption">Newest members first. Names are shortened for privacy.</caption>
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Member</th>
                                        <th scope="col">Programme</th>
                                        <th scope="col">Tier</th>
                                        <th scope="col">Interests</th>
                                        <th scope="col">Joined</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($members as $member): ?>
                                        <tr>
                                            <td><?= (int) $member['id'] ?></td>
                                            <th scope="row"><?= public_name($member['full_name']) ?></th>
                                            <td><?= $member['course'] ?>, <?= (int) $member['year_of_study'] === 5 ? 'PG' : 'Yr ' . (int) $member['year_of_study'] ?></td>
                                            <td><?= TIERS[$member['tier']] ?? '' ?></td>
                                            <td><?= $member['interests'] ?></td>
                                            <td><?= e(date('j M Y', strtotime($member['created_at']))) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
<?php
page_footer();
