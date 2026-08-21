<?php
require_once 'includes/bootstrap.php';
require_login();
$connection = db();

$attempt_id = (int) ($_GET['attempt'] ?? 0);
$user_id = (int) $_SESSION['user_id'];
$attempt_stmt = $connection->prepare("SELECT * FROM quiz_attempts WHERE id = ? AND user_id = ?");
$attempt_stmt->bind_param('ii', $attempt_id, $user_id);
$attempt_stmt->execute();
$attempt = $attempt_stmt->get_result()->fetch_assoc();

if (!$attempt) {
    http_response_code(404);
    exit('Result not found.');
}

$question_stmt = $connection->prepare("SELECT q.*, a.selected_answer, a.is_correct FROM quiz_questions q INNER JOIN quiz_answers a ON a.question_id = q.id WHERE q.attempt_id = ? ORDER BY q.question_order");
$question_stmt->bind_param('i', $attempt_id);
$question_stmt->execute();
$questions = $question_stmt->get_result();
$percentage = $attempt['total'] > 0 ? round(($attempt['score'] / $attempt['total']) * 100) : 0;
$grade = $percentage >= 90 ? 'A' : ($percentage >= 80 ? 'B' : ($percentage >= 70 ? 'C' : ($percentage >= 60 ? 'D' : 'Needs practice')));
?>
<!DOCTYPE html>
<html>
<head>
    <title>Quiz Results - Quiz AI</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="app-shell">
        <aside class="sidebar">
            <div class="brand"><span class="brand-mark">Q</span> QuizWhizAI</div>
            <div class="sidebar-line"></div>
            <nav class="side-nav">
                <a href="dashboard.php"><span class="menu-icon">D</span>Dashboard</a>
                <a class="active" href="take-quiz.php"><span class="menu-icon">Q</span>Take Quiz</a>
                <a href="books.php"><span class="menu-icon">B</span>Offline Books</a>
                <a href="login.php?logout=1"><span class="menu-icon">L</span>Logout</a>
            </nav>
            <div class="sidebar-bottom">PHP + MySQL project</div>
        </aside>
        <main class="main-area">
            <header class="topbar"><span class="topbar-title">Student Panel</span><div class="profile"><span class="avatar">SA</span><span><b><?php echo h($_SESSION['user_name']); ?></b><small>Student account</small></span></div></header>
            <div class="content">
                <div class="page-heading"><div><p class="eyebrow">QUIZ COMPLETE</p><h1><?php echo h($attempt['topic']); ?> Results</h1></div><a class="quiet-link" href="dashboard.php">Dashboard &rarr;</a></div>
                <div class="result-summary card">
                    <div><span class="stat-label">Your score</span><strong><?php echo $attempt['score']; ?> / <?php echo $attempt['total']; ?></strong><small><?php echo $percentage; ?> percent correct</small></div>
                    <div class="grade-badge"><span>Grade</span><strong><?php echo h($grade); ?></strong></div>
                </div>
                <div class="result-actions"><a class="button-link" href="take-quiz.php">Try again</a><a class="secondary-link" href="take-quiz.php">Generate another quiz</a></div>
                <div class="card review-card"><div class="section-heading"><div><p class="eyebrow">ANSWER REVIEW</p><h2>Correct and incorrect answers</h2></div></div>
                        <?php if ($questions->num_rows == 0) { ?>
                            <p class="empty-review">This older attempt only stored the final score, so detailed answer review is unavailable. New quizzes will include full review data.</p>
                        <?php } ?>
                        <?php while ($question = $questions->fetch_assoc()) { $options = json_decode($question['options_json'], true); ?>
                        <article class="review-question <?php echo $question['is_correct'] ? 'review-correct' : 'review-incorrect'; ?>">
                            <p><b><?php echo $question['question_order']; ?>. <?php echo h($question['question_text']); ?></b><span class="answer-status"><?php echo $question['is_correct'] ? 'Correct' : 'Incorrect'; ?></span></p>
                            <p class="answer-line"><b>Your answer:</b> <?php echo isset($options[$question['selected_answer']]) ? h($options[$question['selected_answer']]) : 'No answer'; ?></p>
                            <p class="answer-line"><b>Correct answer:</b> <?php echo h($options[$question['correct_answer']]); ?></p>
                            <p class="explanation"><b>Why:</b> <?php echo h($question['explanation']); ?></p>
                        </article>
                    <?php } ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>