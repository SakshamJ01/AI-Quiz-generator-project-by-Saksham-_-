<?php
require_once 'includes/bootstrap.php';
require_login();
$connection = db();

$quiz = $_SESSION['quiz'] ?? null;
$message = '';

if (isset($_POST['generate'])) {
    $topic = trim($_POST['topic']);
    $difficulty = $_POST['difficulty'] ?? 'beginner';
    $question_count = (int) ($_POST['question_count'] ?? 5);
    $quiz = generate_ai_quiz($topic, $difficulty, $question_count, $message);

    if ($quiz === null) {
        $_SESSION['reading_topic'] = $topic == '' ? 'General Knowledge' : $topic;
        $_SESSION['reading_error'] = $message;
        unset($_SESSION['quiz']);
        header('Location: books.php');
        exit;
    }

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

    $connection->begin_transaction();

    try {
        $stmt = $connection->prepare("INSERT INTO quiz_attempts (user_id, topic, score, total) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('isii', $user_id, $topic, $score, $total);
        $stmt->execute();
        $attempt_id = $connection->insert_id;

        $question_stmt = $connection->prepare("INSERT INTO quiz_questions (attempt_id, question_order, question_text, options_json, correct_answer, explanation) VALUES (?, ?, ?, ?, ?, ?)");
        $answer_stmt = $connection->prepare("INSERT INTO quiz_answers (attempt_id, question_id, selected_answer, is_correct) VALUES (?, ?, ?, ?)");

        foreach ($quiz['questions'] as $index => $question) {
            $selected_answer = isset($answers[$index]) ? (int) $answers[$index] : -1;
            $is_correct = $selected_answer === (int) $question['answer'] ? 1 : 0;
            $question_order = $index + 1;
            $options_json = json_encode($question['options']);
            $question_text = $question['question'];
            $explanation = $question['explanation'];

            $question_stmt->bind_param('iissis', $attempt_id, $question_order, $question_text, $options_json, $question['answer'], $explanation);
            $question_stmt->execute();
            $question_id = $connection->insert_id;
            $answer_stmt->bind_param('iiii', $attempt_id, $question_id, $selected_answer, $is_correct);
            $answer_stmt->execute();
        }

        $connection->commit();
        unset($_SESSION['quiz']);
        header('Location: results.php?attempt=' . $attempt_id);
        exit;
    } catch (Throwable $exception) {
        $connection->rollback();
        $message = 'The result could not be saved. Please try submitting again.';
    }
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
                    <p>Choose your difficulty and quiz length, then generate focused questions using AI.</p>
                    <?php if ($message != '') { ?>
                        <p class="info-message"><?php echo h($message); ?></p>
                    <?php } ?>
                    <form method="post" class="topic-form">
                        <div><label>Topic</label><input type="text" name="topic" placeholder="Example: HTML, CSS, PHP, Science" required></div>
                        <div><label>Difficulty</label><select name="difficulty"><option value="beginner">Beginner</option><option value="intermediate">Intermediate</option><option value="advanced">Advanced</option></select></div>
                        <div><label>Questions</label><select name="question_count"><option value="5">5</option><option value="10">10</option><option value="15">15</option></select></div>
                        <button type="submit" name="generate">Generate quiz</button>
                    </form>
                </div>

        <?php if ($quiz) { ?>
            <div class="card quiz-card">
                <div class="section-heading"><div><p class="eyebrow">READY TO ANSWER</p><h2><?php echo h($quiz['topic']); ?> Quiz</h2></div><span class="question-count"><span id="current-question">1</span> / <?php echo count($quiz['questions']); ?></span></div>

                <form method="post" id="quiz-form" class="step-quiz-form">
                    <?php foreach ($quiz['questions'] as $index => $question) { ?>
                        <div class="question quiz-step<?php echo $index === 0 ? ' active-step' : ''; ?>" data-step="<?php echo $index; ?>">
                            <p><b><?php echo $index + 1; ?>. <?php echo h($question['question']); ?></b></p>

                            <?php foreach ($question['options'] as $option_index => $option) { ?>
                                <label class="option">
                                    <input type="radio" name="answer[<?php echo $index; ?>]" value="<?php echo $option_index; ?>">
                                    <?php echo h($option); ?>
                                </label>
                            <?php } ?>
                        </div>
                    <?php } ?>

                    <p id="quiz-validation" class="quiz-validation" role="alert"></p>
                    <div class="quiz-navigation">
                        <button type="button" id="previous-question" class="secondary-quiz-button">&larr; Previous</button>
                        <button type="button" id="next-question">Next &rarr;</button>
                        <button type="submit" name="submit_quiz" id="submit-quiz">Submit Quiz</button>
                    </div>
                </form>
            </div>
        <?php } ?>
            </div>
        </main>
    </div>
</body>
<?php if ($quiz) { ?>
<script>
    const quizSteps = Array.from(document.querySelectorAll('.quiz-step'));
    const quizForm = document.getElementById('quiz-form');
    const previousQuestion = document.getElementById('previous-question');
    const nextQuestion = document.getElementById('next-question');
    const submitQuiz = document.getElementById('submit-quiz');
    const currentQuestion = document.getElementById('current-question');
    const quizValidation = document.getElementById('quiz-validation');
    let activeStep = 0;

    function hasAnswer(stepIndex) {
        return Boolean(quizSteps[stepIndex].querySelector('input[type="radio"]:checked'));
    }

    function showStep(stepIndex) {
        activeStep = stepIndex;
        quizSteps.forEach(function (step, index) {
            step.classList.toggle('active-step', index === activeStep);
        });
        currentQuestion.textContent = activeStep + 1;
        previousQuestion.disabled = activeStep === 0;
        nextQuestion.hidden = activeStep === quizSteps.length - 1;
        submitQuiz.hidden = activeStep !== quizSteps.length - 1;
        quizValidation.textContent = '';
    }

    function validateStep() {
        if (hasAnswer(activeStep)) {
            quizValidation.textContent = '';
            return true;
        }
        quizValidation.textContent = 'Please choose an answer before continuing.';
        return false;
    }

    previousQuestion.addEventListener('click', function () {
        if (activeStep > 0) {
            showStep(activeStep - 1);
        }
    });

    nextQuestion.addEventListener('click', function () {
        if (validateStep() && activeStep < quizSteps.length - 1) {
            showStep(activeStep + 1);
        }
    });

    quizForm.addEventListener('submit', function (event) {
        const unansweredStep = quizSteps.findIndex(function (step) {
            return !step.querySelector('input[type="radio"]:checked');
        });

        if (unansweredStep !== -1) {
            event.preventDefault();
            showStep(unansweredStep);
            quizValidation.textContent = 'Please answer every question before submitting.';
        }
    });

    showStep(0);
</script>
<?php } ?>
</html>
