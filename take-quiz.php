<?php
require_once 'includes/bootstrap.php';
require_login();
$connection = db();

$quiz = $_SESSION['quiz'] ?? null;
$score = null;
$message = '';

if (isset($_POST['generate'])) {
    $topic = trim($_POST['topic']);
    $quiz = generate_ai_quiz($topic, $message);
    $_SESSION['quiz'] = $quiz;
}

if (isset($_POST['submit_quiz']) && $quiz) {
    $score = 0;
    $answers = $_POST['answer'] ?? [];

    foreach ($quiz['questions'] as $index => $question) {
        if (isset($answers[$index]) && $answers[$index] == $question['answer']) {
            $score++;
        }
    }

    $user_id = $_SESSION['user_id'];
    $topic = $quiz['topic'];
    $total = count($quiz['questions']);

    $stmt = $connection->prepare("INSERT INTO quiz_attempts (user_id, topic, score, total) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('isii', $user_id, $topic, $score, $total);
    $stmt->execute();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Take Quiz - Quiz AI</title>
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
                <a href="login.php?logout=1"><span class="menu-icon">L</span>Logout</a>
            </nav>
            <div class="sidebar-bottom">PHP + MySQL project</div>
        </aside>

        <main class="main-area">
            <header class="topbar">
                <span class="topbar-title">Student Panel</span>
                <div class="profile"><span class="avatar">SA</span><span><b><?php echo h($_SESSION['user_name']); ?></b><small>Student account</small></span></div>
            </header>

            <div class="content">
                <div class="page-heading"><div><p class="eyebrow">QUIZ BUILDER</p><h1>Take a Quiz</h1></div><a class="quiet-link" href="dashboard.php">&larr; Dashboard</a></div>

                <div class="card generator-card">
                    <h2>Choose a topic</h2>
                    <p>Type a subject and the project will create five simple questions for you using AI.</p>
                    <?php if ($message != '') { ?>
                        <p class="info-message"><?php echo h($message); ?></p>
                    <?php } ?>
                    <form method="post" class="topic-form">
                        <div><label>Topic</label><input type="text" name="topic" placeholder="Example: HTML, CSS, PHP, Science" required></div>
                        <button type="submit" name="generate">Generate quiz</button>
                    </form>
                </div>

        <?php if ($quiz) { ?>
            <div class="card quiz-card">
                <div class="section-heading"><div><p class="eyebrow">READY TO ANSWER</p><h2><?php echo h($quiz['topic']); ?> Quiz</h2></div><span class="question-count">5 questions</span></div>

                <?php if ($score !== null) { ?>
                    <p class="success">Your score is <?php echo $score; ?> out of <?php echo count($quiz['questions']); ?>. This result is saved in database.</p>
                <?php } ?>

                <form method="post">
                    <?php foreach ($quiz['questions'] as $index => $question) { ?>
                        <div class="question">
                            <p><b><?php echo $index + 1; ?>. <?php echo h($question['question']); ?></b></p>

                            <?php foreach ($question['options'] as $option_index => $option) { ?>
                                <label class="option">
                                    <input type="radio" name="answer[<?php echo $index; ?>]" value="<?php echo $option_index; ?>" required>
                                    <?php echo h($option); ?>
                                </label>
                            <?php } ?>
                        </div>
                    <?php } ?>

                    <button type="submit" name="submit_quiz">Submit Quiz</button>
                </form>
            </div>
        <?php } ?>
            </div>
        </main>
    </div>
</body>
</html>
