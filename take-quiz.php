<?php
require_once __DIR__ . '/includes/bootstrap.php';
quizai_require_login();
$db = quizai_db();
$quiz = quizai_load_quiz($db);
$result = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $answers = $_POST['answer'] ?? [];
    $score = 0;
    $total = count($quiz['questions']);

    foreach ($quiz['questions'] as $questionIndex => $question) {
        $selected = $answers[$questionIndex] ?? null;
        foreach ($question['options'] as $optionIndex => $option) {
            if ((string) $option['id'] === (string) $selected && (int) $option['is_correct'] === 1) {
                $score++;
            }
        }
    }

    if ($db) {
        $user = quizai_current_user();
        $userId = (int) ($user['id'] ?? 0);
        $quizId = (int) ($quiz['id'] ?? 1);
        $startedAt = date('Y-m-d H:i:s');
        $finishedAt = date('Y-m-d H:i:s');
        $stmt = $db->prepare('INSERT INTO quiz_attempts (quiz_id, user_id, score, total_score, started_at, finished_at) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('iiiiss', $quizId, $userId, $score, $total, $startedAt, $finishedAt);
        $stmt->execute();
        $attemptId = $stmt->insert_id;
        $stmt->close();

        foreach ($quiz['questions'] as $questionIndex => $question) {
            $selected = $answers[$questionIndex] ?? null;
            foreach ($question['options'] as $option) {
                if ((string) $option['id'] === (string) $selected) {
                    $isCorrect = (int) $option['is_correct'];
                    $questionId = (int) $question['id'];
                    $optionId = (int) $option['id'];
                    $stmt = $db->prepare('INSERT INTO attempt_answers (attempt_id, question_id, option_id, answer_text, is_correct) VALUES (?, ?, ?, ?, ?)');
                    $answerText = $option['option_text'];
                    $stmt->bind_param('iiisi', $attemptId, $questionId, $optionId, $answerText, $isCorrect);
                    $stmt->execute();
                    $stmt->close();
                }
            }
        }
    }

    $result = [
        'score' => $score,
        'total' => $total,
        'percent' => $total ? round(($score / $total) * 100) : 0
    ];
}

quizai_render_start('Take Quiz', 'app', 'take-quiz');
?>
<div class="content-grid">
    <section class="card col-8">
        <div class="section-title"><?php echo quizai_h($quiz['title']); ?></div>
        <p class="card-copy"><?php echo quizai_h($quiz['description']); ?></p>

        <?php if ($result) : ?>
            <div class="flash flash-success">You scored <?php echo (int) $result['score']; ?> out of <?php echo (int) $result['total']; ?> (<?php echo (int) $result['percent']; ?>%).</div>
        <?php endif; ?>

        <form method="post">
            <?php foreach ($quiz['questions'] as $questionIndex => $question) : ?>
                <div class="quiz-question">
                    <label><?php echo quizai_h(($questionIndex + 1) . '. ' . $question['question_text']); ?></label>
                    <?php foreach ($question['options'] as $option) : ?>
                        <label class="quiz-choice">
                            <input type="radio" name="answer[<?php echo (int) $questionIndex; ?>]" value="<?php echo (int) $option['id']; ?>" required>
                            <span><?php echo quizai_h($option['option_text']); ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
            <div class="field-row">
                <button class="primary-button" type="submit">Submit answers</button>
                <a class="secondary-button" href="quiz-create.php">Back to generator</a>
            </div>
        </form>
    </section>

    <aside class="card col-4">
        <div class="section-title">Quiz summary</div>
        <div class="metric-stack">
            <div><strong>Questions</strong><div class="muted"><?php echo count($quiz['questions']); ?> items</div></div>
            <div><strong>Time limit</strong><div class="muted"><?php echo (int) $quiz['time_limit']; ?> minutes</div></div>
            <div><strong>Mode</strong><div class="muted">Multiple choice</div></div>
        </div>
    </aside>
</div>
<?php quizai_render_end('app'); ?>
