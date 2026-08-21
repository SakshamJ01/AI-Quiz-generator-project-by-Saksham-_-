<?php
require_once 'includes/bootstrap.php';
require_login();
$connection = db();

$user_id = $_SESSION['user_id'];
$attempts = $connection->query("SELECT * FROM quiz_attempts WHERE user_id = $user_id ORDER BY id DESC LIMIT 5");
$analytics = $connection->query("SELECT COUNT(*) AS total, COALESCE(ROUND(AVG(score / total * 100)), 0) AS average_percentage, COALESCE(MAX(score / total * 100), 0) AS best_percentage FROM quiz_attempts WHERE user_id = $user_id")->fetch_assoc();
$strongest = $connection->query("SELECT topic, ROUND(AVG(score / total * 100)) AS percentage FROM quiz_attempts WHERE user_id = $user_id GROUP BY topic ORDER BY percentage DESC, COUNT(*) DESC LIMIT 1")->fetch_assoc();
$weakest = $connection->query("SELECT topic, ROUND(AVG(score / total * 100)) AS percentage FROM quiz_attempts WHERE user_id = $user_id GROUP BY topic ORDER BY percentage ASC, COUNT(*) DESC LIMIT 1")->fetch_assoc();
$chart_attempts = $connection->query("SELECT topic, score, total, created_at FROM quiz_attempts WHERE user_id = $user_id ORDER BY id DESC LIMIT 8");
$chart_data = [];
while ($chart_row = $chart_attempts->fetch_assoc()) {
    $chart_row['percentage'] = $chart_row['total'] > 0 ? round(($chart_row['score'] / $chart_row['total']) * 100) : 0;
    $chart_data[] = $chart_row;
}
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
                <a href="books.php"><span class="menu-icon">B</span>Offline Books</a>
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

                <div class="stats analytics-stats">
                    <div class="stat-card"><div><span class="stat-label">Total Quizzes</span><strong><?php echo $analytics['total']; ?></strong><small>Completed attempts</small></div><span class="stat-icon purple">Q</span></div>
                    <div class="stat-card"><div><span class="stat-label">Average Score</span><strong><?php echo $analytics['average_percentage']; ?>%</strong><small>Across all quizzes</small></div><span class="stat-icon blue">A</span></div>
                    <div class="stat-card"><div><span class="stat-label">Best Score</span><strong><?php echo $analytics['best_percentage']; ?>%</strong><small>Your highest result</small></div><span class="stat-icon green">B</span></div>
                </div>

                <div class="analytics-row">
                    <div class="card topic-insights"><div class="section-heading"><div><p class="eyebrow">TOPIC INSIGHTS</p><h2>Strengths and focus areas</h2></div></div><div class="insight-list"><div><span>Strongest topic</span><b><?php echo $strongest ? h($strongest['topic']) : 'No data yet'; ?></b><small><?php echo $strongest ? $strongest['percentage'] . '%' : ''; ?></small></div><div><span>Needs practice</span><b><?php echo $weakest ? h($weakest['topic']) : 'No data yet'; ?></b><small><?php echo $weakest ? $weakest['percentage'] . '%' : ''; ?></small></div></div></div>
                    <div class="card chart-card"><div class="section-heading"><div><p class="eyebrow">PROGRESS</p><h2>Score over time</h2></div></div><div class="score-chart"><?php if (count($chart_data) == 0) { ?><p class="chart-empty">Complete a quiz to see your progress.</p><?php } else { ?><?php foreach (array_reverse($chart_data) as $chart_row) { ?><div class="chart-column"><div class="chart-bar" style="height: <?php echo max(8, $chart_row['percentage']); ?>%"><span><?php echo $chart_row['percentage']; ?>%</span></div><small><?php echo h(substr($chart_row['topic'], 0, 12)); ?></small></div><?php } ?><?php } ?></div></div>
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
                        <th>Details</th>
                    </tr>

                    <?php while ($row = $attempts->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo h($row['topic']); ?></td>
                        <td><?php echo $row['score']; ?> / <?php echo $row['total']; ?></td>
                        <td><?php echo $row['created_at']; ?></td>
                        <td><a class="table-link" href="results.php?attempt=<?php echo $row['id']; ?>">Review</a></td>
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
