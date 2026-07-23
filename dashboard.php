<?php
require_once 'includes/bootstrap.php';
require_login();
$connection = db();

$user_id = $_SESSION['user_id'];
$attempts = $connection->query("SELECT * FROM quiz_attempts WHERE user_id = $user_id ORDER BY id DESC LIMIT 5");
$total_attempts = $connection->query("SELECT COUNT(*) AS total FROM quiz_attempts WHERE user_id = $user_id")->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - Quiz AI</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="app-shell">
        <aside class="sidebar">
            <div class="brand"><span class="brand-mark">Q</span> QuizWhizAI</div>
            <div class="sidebar-line"></div>
            <nav class="side-nav">
                <a class="active" href="dashboard.php"><span class="menu-icon">D</span>Dashboard</a>
                <a href="take-quiz.php"><span class="menu-icon">Q</span>Take Quiz</a>
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
                <div class="page-heading">
                    <div><p class="eyebrow">OVERVIEW</p><h1>Dashboard</h1></div>
                    <a class="button-link" href="take-quiz.php">+ Take a quiz</a>
                </div>

                <div class="stats">
                    <div class="stat-card"><div><span class="stat-label">Total Attempts</span><strong><?php echo $total_attempts['total']; ?></strong><small>Saved in your account</small></div><span class="stat-icon purple">Q</span></div>
                    <div class="stat-card"><div><span class="stat-label">Quiz Mode</span><strong>Topic based</strong><small>Enter any subject</small></div><span class="stat-icon blue">T</span></div>
                    <div class="stat-card"><div><span class="stat-label">Project Type</span><strong>PHP + MySQL</strong><small>Running through XAMPP</small></div><span class="stat-icon green">D</span></div>
                </div>

                <div class="card table-card">
                    <div class="section-heading"><div><h2>Recent Quiz Attempts</h2><p>Your latest submitted quizzes</p></div><a href="take-quiz.php">New quiz &rarr;</a></div>

            <?php if ($attempts->num_rows == 0) { ?>
                <div class="empty-state"><span>Q</span><p>No quiz attempts yet. Start by taking your first quiz.</p><a class="button-link" href="take-quiz.php">Create your first quiz</a></div>
            <?php } else { ?>
                <table>
                    <tr>
                        <th>Topic</th>
                        <th>Score</th>
                        <th>Date</th>
                    </tr>

                    <?php while ($row = $attempts->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo h($row['topic']); ?></td>
                        <td><?php echo $row['score']; ?> / <?php echo $row['total']; ?></td>
                        <td><?php echo $row['created_at']; ?></td>
                    </tr>
                    <?php } ?>
                </table>
                <?php } ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
