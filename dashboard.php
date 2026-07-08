<?php
require_once __DIR__ . '/includes/bootstrap.php';
quizai_require_login();
$db = quizai_db();
$stats = quizai_site_stats($db);
$quizzes = quizai_recent_quizzes($db, 5);
$attempts = quizai_recent_attempts($db, 5);

quizai_render_start('Dashboard', 'app', 'dashboard');
?>
<div class="dashboard-grid">
    <div class="stat-card"><div class="eyebrow">Learners</div><div class="stat-value"><?php echo number_format($stats['users']); ?></div><div class="stats-subtitle">Active accounts and demo users.</div></div>
    <div class="stat-card"><div class="eyebrow">Quizzes</div><div class="stat-value"><?php echo number_format($stats['quizzes']); ?></div><div class="stats-subtitle">Published and draft quiz records.</div></div>
    <div class="stat-card"><div class="eyebrow">Attempts</div><div class="stat-value"><?php echo number_format($stats['attempts']); ?></div><div class="stats-subtitle">Tracked quiz submissions.</div></div>
    <div class="stat-card"><div class="eyebrow">Categories</div><div class="stat-value"><?php echo number_format($stats['categories']); ?></div><div class="stats-subtitle">Content groupings for quizzes.</div></div>
</div>

<div class="content-grid">
    <section class="card col-7">
        <div class="section-title">Recent quizzes</div>
        <table class="table">
            <thead><tr><th>Title</th><th>Status</th><th>Questions</th><th>Created</th></tr></thead>
            <tbody>
            <?php foreach ($quizzes as $quiz) : ?>
                <tr>
                    <td><?php echo quizai_h($quiz['title']); ?></td>
                    <td><?php echo quizai_h($quiz['status']); ?></td>
                    <td><?php echo quizai_h($quiz['questions']); ?></td>
                    <td><?php echo quizai_h($quiz['created_at']); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <section class="card col-5">
        <div class="section-title">Quick actions</div>
        <div class="metric-stack">
            <a class="primary-button" href="quiz-create.php">Generate new quiz</a>
            <a class="secondary-button" href="take-quiz.php">Take a quiz</a>
            <a class="secondary-button" href="history.php">Review history</a>
            <a class="secondary-button" href="profile.php">Edit profile</a>
        </div>
    </section>

    <section class="table-card col-12">
        <div class="section-title">Recent attempts</div>
        <table class="table">
            <thead><tr><th>Learner</th><th>Quiz</th><th>Score</th><th>Finished</th></tr></thead>
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
