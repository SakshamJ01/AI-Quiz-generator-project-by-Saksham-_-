<?php
require_once __DIR__ . '/includes/bootstrap.php';
quizai_require_login();
$db = quizai_db();
$attempts = quizai_recent_attempts($db, 10);
$quizzes = quizai_recent_quizzes($db, 6);

quizai_render_start('History', 'app', 'history');
?>
<div class="content-grid">
    <section class="table-card col-7">
        <div class="section-title">Quiz history</div>
        <table class="table">
            <thead><tr><th>Quiz</th><th>Score</th><th>Finished</th></tr></thead>
            <tbody>
            <?php foreach ($attempts as $attempt) : ?>
                <tr>
                    <td><?php echo quizai_h($attempt['title']); ?></td>
                    <td><?php echo quizai_h($attempt['score']); ?></td>
                    <td><?php echo quizai_h($attempt['finished_at']); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <section class="card col-5">
        <div class="section-title">Recent quizzes</div>
        <div class="metric-stack">
            <?php foreach ($quizzes as $quiz) : ?>
                <div>
                    <strong><?php echo quizai_h($quiz['title']); ?></strong>
                    <div class="muted"><?php echo quizai_h($quiz['status']); ?>, <?php echo quizai_h($quiz['questions']); ?> questions</div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</div>
<?php quizai_render_end('app'); ?>
