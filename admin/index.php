<?php
require_once __DIR__ . '/../includes/bootstrap.php';
quizai_require_login('admin');
$db = quizai_db();
$stats = quizai_site_stats($db);
$attempts = quizai_recent_attempts($db, 5);

quizai_render_start('Admin Dashboard', 'app', 'admin-dashboard');
?>
<div class="dashboard-grid">
    <div class="stat-card"><div class="eyebrow">Users</div><div class="stat-value"><?php echo number_format($stats['users']); ?></div><div class="stats-subtitle">Managed accounts.</div></div>
    <div class="stat-card"><div class="eyebrow">Quizzes</div><div class="stat-value"><?php echo number_format($stats['quizzes']); ?></div><div class="stats-subtitle">Content library.</div></div>
    <div class="stat-card"><div class="eyebrow">Attempts</div><div class="stat-value"><?php echo number_format($stats['attempts']); ?></div><div class="stats-subtitle">Activity stream.</div></div>
    <div class="stat-card"><div class="eyebrow">Categories</div><div class="stat-value"><?php echo number_format($stats['categories']); ?></div><div class="stats-subtitle">Taxonomy controls.</div></div>
</div>

<div class="content-grid">
    <section class="table-card col-12">
        <div class="section-title">Latest attempts</div>
        <table class="table">
            <thead><tr><th>User</th><th>Quiz</th><th>Score</th><th>Finished</th></tr></thead>
            <tbody>
            <?php foreach ($attempts as $attempt) : ?>
                <tr>
                    <td><?php echo quizai_h($attempt['name']); ?></td>
                    <td><?php echo quizai_h($attempt['title']); ?></td>
                    <td><?php echo quizai_h($attempt['score']); ?></td>
                    <td><?php echo quizai_h($attempt['finished_at']); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</div>
<?php quizai_render_end('app'); ?>
