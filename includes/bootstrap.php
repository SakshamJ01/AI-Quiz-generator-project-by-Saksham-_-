<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('QUIZAI_DB_HOST', getenv('QUIZAI_DB_HOST') ?: '127.0.0.1');
define('QUIZAI_DB_USER', getenv('QUIZAI_DB_USER') ?: 'root');
define('QUIZAI_DB_PASS', getenv('QUIZAI_DB_PASS') ?: '');
define('QUIZAI_DB_NAME', getenv('QUIZAI_DB_NAME') ?: 'quizai');

function quizai_h($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function quizai_flash($message = null, $type = 'success')
{
    if ($message !== null) {
        $_SESSION['flash'] = ['message' => $message, 'type' => $type];
        return '';
    }

    if (empty($_SESSION['flash'])) {
        return '';
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);

    return '<div class="flash flash-' . quizai_h($flash['type']) . '">' . quizai_h($flash['message']) . '</div>';
}

function quizai_db()
{
    static $db = null;
    static $checked = false;

    if ($checked) {
        return $db;
    }

    $checked = true;

    if (!class_exists('mysqli')) {
        return null;
    }

    mysqli_report(MYSQLI_REPORT_OFF);

    try {
        $server = @new mysqli(QUIZAI_DB_HOST, QUIZAI_DB_USER, QUIZAI_DB_PASS);
    } catch (Throwable $exception) {
        return null;
    }

    if ($server->connect_errno) {
        $db = null;
        return null;
    }

    $server->query('CREATE DATABASE IF NOT EXISTS `' . QUIZAI_DB_NAME . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    $server->select_db(QUIZAI_DB_NAME);

    $db = $server;
    $db->set_charset('utf8mb4');
    quizai_ensure_schema($db);
    quizai_seed_data($db);

    return $db;
}

function quizai_ensure_schema($db)
{
    $queries = [
        'CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(120) NOT NULL,
            email VARCHAR(190) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            role ENUM("admin", "learner") NOT NULL DEFAULT "learner",
            email_verified_at DATETIME NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4',
        'CREATE TABLE IF NOT EXISTS password_resets (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            token VARCHAR(128) NOT NULL,
            expires_at DATETIME NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX token_idx (token),
            CONSTRAINT fk_password_resets_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4',
        'CREATE TABLE IF NOT EXISTS quiz_categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(120) NOT NULL,
            description VARCHAR(255) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4',
        'CREATE TABLE IF NOT EXISTS quizzes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(190) NOT NULL,
            description TEXT NULL,
            category_id INT NULL,
            created_by INT NULL,
            status ENUM("draft", "published") NOT NULL DEFAULT "published",
            total_questions INT NOT NULL DEFAULT 0,
            time_limit INT NOT NULL DEFAULT 10,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT fk_quizzes_category FOREIGN KEY (category_id) REFERENCES quiz_categories(id) ON DELETE SET NULL,
            CONSTRAINT fk_quizzes_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4',
        'CREATE TABLE IF NOT EXISTS quiz_questions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            quiz_id INT NOT NULL,
            question_text TEXT NOT NULL,
            question_type VARCHAR(30) NOT NULL DEFAULT "mcq",
            points INT NOT NULL DEFAULT 1,
            sort_order INT NOT NULL DEFAULT 0,
            CONSTRAINT fk_questions_quiz FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4',
        'CREATE TABLE IF NOT EXISTS quiz_options (
            id INT AUTO_INCREMENT PRIMARY KEY,
            question_id INT NOT NULL,
            option_text VARCHAR(255) NOT NULL,
            is_correct TINYINT(1) NOT NULL DEFAULT 0,
            sort_order INT NOT NULL DEFAULT 0,
            CONSTRAINT fk_options_question FOREIGN KEY (question_id) REFERENCES quiz_questions(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4',
        'CREATE TABLE IF NOT EXISTS quiz_attempts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            quiz_id INT NOT NULL,
            user_id INT NULL,
            score INT NOT NULL DEFAULT 0,
            total_score INT NOT NULL DEFAULT 0,
            started_at DATETIME NOT NULL,
            finished_at DATETIME NULL,
            CONSTRAINT fk_attempts_quiz FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE,
            CONSTRAINT fk_attempts_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4',
        'CREATE TABLE IF NOT EXISTS attempt_answers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            attempt_id INT NOT NULL,
            question_id INT NOT NULL,
            option_id INT NULL,
            answer_text VARCHAR(255) NULL,
            is_correct TINYINT(1) NOT NULL DEFAULT 0,
            CONSTRAINT fk_answers_attempt FOREIGN KEY (attempt_id) REFERENCES quiz_attempts(id) ON DELETE CASCADE,
            CONSTRAINT fk_answers_question FOREIGN KEY (question_id) REFERENCES quiz_questions(id) ON DELETE CASCADE,
            CONSTRAINT fk_answers_option FOREIGN KEY (option_id) REFERENCES quiz_options(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4',
        'CREATE TABLE IF NOT EXISTS system_settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(120) NOT NULL UNIQUE,
            setting_value TEXT NOT NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
    ];

    foreach ($queries as $query) {
        $db->query($query);
    }
}

function quizai_seed_data($db)
{
    $userCount = (int) $db->query('SELECT COUNT(*) AS c FROM users')->fetch_assoc()['c'];
    if ($userCount === 0) {
        $adminPassword = password_hash('password123', PASSWORD_DEFAULT);
        $learnerPassword = password_hash('password123', PASSWORD_DEFAULT);

        $stmt = $db->prepare('INSERT INTO users (name, email, password, role, email_verified_at) VALUES (?, ?, ?, ?, NOW())');
        $name = 'QuizAI Admin';
        $email = 'admin@quizai.test';
        $role = 'admin';
        $stmt->bind_param('ssss', $name, $email, $adminPassword, $role);
        $stmt->execute();
        $stmt->close();

        $stmt = $db->prepare('INSERT INTO users (name, email, password, role, email_verified_at) VALUES (?, ?, ?, ?, NOW())');
        $name = 'Demo Learner';
        $email = 'learner@quizai.test';
        $role = 'learner';
        $stmt->bind_param('ssss', $name, $email, $learnerPassword, $role);
        $stmt->execute();
        $stmt->close();
    }

    $categoryCount = (int) $db->query('SELECT COUNT(*) AS c FROM quiz_categories')->fetch_assoc()['c'];
    if ($categoryCount === 0) {
        $categories = [
            ['Programming', 'Software, code, and logic challenges'],
            ['Mathematics', 'Algebra, arithmetic, and problem solving'],
            ['Science', 'General science and reasoning']
        ];

        $stmt = $db->prepare('INSERT INTO quiz_categories (name, description) VALUES (?, ?)');
        foreach ($categories as $category) {
            $stmt->bind_param('ss', $category[0], $category[1]);
            $stmt->execute();
        }
        $stmt->close();
    }

    $quizCount = (int) $db->query('SELECT COUNT(*) AS c FROM quizzes')->fetch_assoc()['c'];
    if ($quizCount === 0) {
        $adminRow = $db->query("SELECT id FROM users WHERE role = 'admin' ORDER BY id ASC LIMIT 1")->fetch_assoc();
        $categoryRow = $db->query('SELECT id FROM quiz_categories ORDER BY id ASC LIMIT 1')->fetch_assoc();
        $adminId = $adminRow ? (int) $adminRow['id'] : 1;
        $categoryId = $categoryRow ? (int) $categoryRow['id'] : 1;

        $stmt = $db->prepare('INSERT INTO quizzes (title, description, category_id, created_by, status, total_questions, time_limit) VALUES (?, ?, ?, ?, "published", ?, ?)');
        $title = 'Introduction to QuizAI';
        $description = 'A starter quiz for the runnable demo.';
        $totalQuestions = 3;
        $timeLimit = 12;
        $stmt->bind_param('ssiiii', $title, $description, $categoryId, $adminId, $totalQuestions, $timeLimit);
        $stmt->execute();
        $quizId = $stmt->insert_id;
        $stmt->close();

        $questions = [
            ['Which keyword starts a PHP class?', ['class', 'object', 'function', 'package'], 0],
            ['What does SQL stand for?', ['Structured Query Language', 'Simple Question List', 'Styled Query Logic', 'System Query Link'], 0],
            ['Which symbol is used for an associative array key in PHP?', ['=>', '::', '->', '**'], 0]
        ];

        $questionStmt = $db->prepare('INSERT INTO quiz_questions (quiz_id, question_text, question_type, points, sort_order) VALUES (?, ?, "mcq", 1, ?)');
        $optionStmt = $db->prepare('INSERT INTO quiz_options (question_id, option_text, is_correct, sort_order) VALUES (?, ?, ?, ?)');
        foreach ($questions as $index => $question) {
            $questionText = $question[0];
            $sortOrder = $index + 1;
            $questionStmt->bind_param('isi', $quizId, $questionText, $sortOrder);
            $questionStmt->execute();
            $questionId = $questionStmt->insert_id;

            foreach ($question[1] as $optionIndex => $optionText) {
                $isCorrect = $optionIndex === $question[2] ? 1 : 0;
                $optionOrder = $optionIndex + 1;
                $optionStmt->bind_param('isii', $questionId, $optionText, $isCorrect, $optionOrder);
                $optionStmt->execute();
            }
        }
        $questionStmt->close();
        $optionStmt->close();
    }

    $settingsCount = (int) $db->query('SELECT COUNT(*) AS c FROM system_settings')->fetch_assoc()['c'];
    if ($settingsCount === 0) {
        $settings = [
            ['site_name', 'QuizAI'],
            ['support_email', 'support@quizai.test']
        ];
        $stmt = $db->prepare('INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?)');
        foreach ($settings as $setting) {
            $stmt->bind_param('ss', $setting[0], $setting[1]);
            $stmt->execute();
        }
        $stmt->close();
    }
}

function quizai_current_user()
{
    return $_SESSION['user'] ?? null;
}

function quizai_login_user($user)
{
    $_SESSION['user'] = [
        'id' => (int) $user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'role' => $user['role']
    ];
}

function quizai_logout_user()
{
    unset($_SESSION['user']);
    session_regenerate_id(true);
}

function quizai_require_login($role = null)
{
    $user = quizai_current_user();
    if (!$user) {
        header('Location: login.php');
        exit;
    }

    if ($role !== null && ($user['role'] ?? '') !== $role) {
        header('Location: dashboard.php');
        exit;
    }
}

function quizai_demo_quiz()
{
    return [
        'id' => 1,
        'title' => 'Introduction to QuizAI',
        'description' => 'A starter quiz for the runnable demo.',
        'time_limit' => 12,
        'questions' => [
            [
                'id' => 1,
                'question_text' => 'Which keyword starts a PHP class?',
                'options' => [
                    ['id' => 11, 'option_text' => 'class', 'is_correct' => 1],
                    ['id' => 12, 'option_text' => 'object', 'is_correct' => 0],
                    ['id' => 13, 'option_text' => 'function', 'is_correct' => 0],
                    ['id' => 14, 'option_text' => 'package', 'is_correct' => 0]
                ]
            ],
            [
                'id' => 2,
                'question_text' => 'What does SQL stand for?',
                'options' => [
                    ['id' => 21, 'option_text' => 'Structured Query Language', 'is_correct' => 1],
                    ['id' => 22, 'option_text' => 'Simple Question List', 'is_correct' => 0],
                    ['id' => 23, 'option_text' => 'Styled Query Logic', 'is_correct' => 0],
                    ['id' => 24, 'option_text' => 'System Query Link', 'is_correct' => 0]
                ]
            ],
            [
                'id' => 3,
                'question_text' => 'Which symbol is used for an associative array key in PHP?',
                'options' => [
                    ['id' => 31, 'option_text' => '=>', 'is_correct' => 1],
                    ['id' => 32, 'option_text' => '::', 'is_correct' => 0],
                    ['id' => 33, 'option_text' => '->', 'is_correct' => 0],
                    ['id' => 34, 'option_text' => '**', 'is_correct' => 0]
                ]
            ]
        ]
    ];
}

function quizai_load_quiz($db, $quizId = null)
{
    if (!$db) {
        return quizai_demo_quiz();
    }

    if ($quizId === null) {
        $row = $db->query('SELECT id FROM quizzes ORDER BY id ASC LIMIT 1')->fetch_assoc();
        $quizId = $row ? (int) $row['id'] : 0;
    }

    if ($quizId <= 0) {
        return quizai_demo_quiz();
    }

    $quizStmt = $db->prepare('SELECT q.*, c.name AS category_name FROM quizzes q LEFT JOIN quiz_categories c ON c.id = q.category_id WHERE q.id = ? LIMIT 1');
    $quizStmt->bind_param('i', $quizId);
    $quizStmt->execute();
    $quiz = $quizStmt->get_result()->fetch_assoc();
    $quizStmt->close();

    if (!$quiz) {
        return quizai_demo_quiz();
    }

    $questionsStmt = $db->prepare('SELECT * FROM quiz_questions WHERE quiz_id = ? ORDER BY sort_order ASC, id ASC');
    $questionsStmt->bind_param('i', $quizId);
    $questionsStmt->execute();
    $questions = $questionsStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $questionsStmt->close();

    foreach ($questions as &$question) {
        $optionStmt = $db->prepare('SELECT * FROM quiz_options WHERE question_id = ? ORDER BY sort_order ASC, id ASC');
        $optionStmt->bind_param('i', $question['id']);
        $optionStmt->execute();
        $question['options'] = $optionStmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $optionStmt->close();
    }
    unset($question);

    $quiz['questions'] = $questions;
    return $quiz;
}

function quizai_site_stats($db)
{
    if (!$db) {
        return ['users' => 1248, 'quizzes' => 432, 'attempts' => 10896, 'categories' => 18];
    }

    $stats = [];
    foreach (['users', 'quizzes', 'quiz_attempts', 'quiz_categories'] as $table) {
        $result = $db->query('SELECT COUNT(*) AS c FROM ' . $table);
        $stats[$table] = (int) ($result ? $result->fetch_assoc()['c'] : 0);
    }

    return [
        'users' => $stats['users'],
        'quizzes' => $stats['quizzes'],
        'attempts' => $stats['quiz_attempts'],
        'categories' => $stats['quiz_categories']
    ];
}

function quizai_recent_quizzes($db, $limit = 5)
{
    if (!$db) {
        return [
            ['title' => 'Introduction to QuizAI', 'status' => 'published', 'questions' => 3, 'created_at' => 'Today'],
            ['title' => 'PHP Basics', 'status' => 'draft', 'questions' => 8, 'created_at' => 'Yesterday']
        ];
    }

    $limit = (int) $limit;
    $result = $db->query('SELECT title, status, total_questions AS questions, DATE_FORMAT(created_at, "%b %d, %Y") AS created_at FROM quizzes ORDER BY id DESC LIMIT ' . $limit);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function quizai_recent_attempts($db, $limit = 5)
{
    if (!$db) {
        return [['name' => 'Demo Learner', 'title' => 'Introduction to QuizAI', 'score' => '3/3', 'finished_at' => 'Today']];
    }

    $limit = (int) $limit;
    $sql = 'SELECT COALESCE(u.name, "Guest") AS name, q.title, CONCAT(a.score, "/", a.total_score) AS score, DATE_FORMAT(a.finished_at, "%b %d, %Y") AS finished_at
            FROM quiz_attempts a
            JOIN quizzes q ON q.id = a.quiz_id
            LEFT JOIN users u ON u.id = a.user_id
            ORDER BY a.id DESC LIMIT ' . $limit;

    $result = $db->query($sql);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function quizai_render_start($title, $mode = 'public', $active = 'dashboard')
{
    $user = quizai_current_user();
    $pageTitle = $title . ' - QuizAI';
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo quizai_h($pageTitle); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script defer src="/assets/js/app.js"></script>
</head>
<body class="<?php echo $mode === 'app' ? 'app-body' : ($mode === 'auth' ? 'auth-body' : 'public-body'); ?>">
    <?php if ($mode === 'app') : ?>
        <div class="app-shell">
            <aside class="sidebar">
                <div class="brand-block">
                    <div class="brand-mark">Q</div>
                    <div>
                        <div class="brand-name">QuizAI</div>
                        <div class="brand-subtitle">AI quiz workflow</div>
                    </div>
                </div>
                <nav class="side-nav">
                    <a class="<?php echo $active === 'dashboard' ? 'active' : ''; ?>" href="/dashboard.php"><span class="material-symbols-outlined">dashboard</span> Dashboard</a>
                    <a class="<?php echo $active === 'quiz-create' ? 'active' : ''; ?>" href="/quiz-create.php"><span class="material-symbols-outlined">auto_awesome</span> Generate Quiz</a>
                    <a class="<?php echo $active === 'take-quiz' ? 'active' : ''; ?>" href="/take-quiz.php"><span class="material-symbols-outlined">quiz</span> Take Quiz</a>
                    <a class="<?php echo $active === 'history' ? 'active' : ''; ?>" href="/history.php"><span class="material-symbols-outlined">history</span> History</a>
                    <a class="<?php echo $active === 'profile' ? 'active' : ''; ?>" href="/profile.php"><span class="material-symbols-outlined">person</span> Profile</a>
                    <?php if (($user['role'] ?? '') === 'admin') : ?>
                        <div class="nav-divider"></div>
                        <a class="<?php echo $active === 'admin-dashboard' ? 'active' : ''; ?>" href="/admin/index.php"><span class="material-symbols-outlined">space_dashboard</span> Admin</a>
                        <a class="<?php echo $active === 'users' ? 'active' : ''; ?>" href="/admin/users.php"><span class="material-symbols-outlined">group</span> Users</a>
                        <a class="<?php echo $active === 'categories' ? 'active' : ''; ?>" href="/admin/categories.php"><span class="material-symbols-outlined">category</span> Categories</a>
                        <a class="<?php echo $active === 'settings' ? 'active' : ''; ?>" href="/admin/settings.php"><span class="material-symbols-outlined">settings</span> Settings</a>
                    <?php endif; ?>
                </nav>
                <div class="sidebar-footer">
                    <div class="sidebar-user"><?php echo quizai_h($user['name'] ?? 'Guest'); ?></div>
                    <div class="sidebar-role"><?php echo quizai_h(ucfirst($user['role'] ?? 'visitor')); ?></div>
                    <a class="ghost-button full-width" href="/logout.php">Logout</a>
                </div>
            </aside>
            <div class="app-panel">
                <header class="topbar">
                    <button class="menu-toggle" type="button"><span class="material-symbols-outlined">menu</span></button>
                    <div>
                        <div class="eyebrow">QuizAI workspace</div>
                        <h1><?php echo quizai_h($title); ?></h1>
                    </div>
                    <div class="topbar-actions">
                        <a class="secondary-button" href="/quiz-create.php">New Quiz</a>
                        <a class="avatar-pill" href="/profile.php"><?php echo quizai_h(strtoupper(substr($user['name'] ?? 'U', 0, 1))); ?></a>
                    </div>
                </header>
                <main class="page-content">
    <?php elseif ($mode === 'auth') : ?>
        <main class="auth-shell">
            <div class="auth-orb auth-orb-a"></div>
            <div class="auth-orb auth-orb-b"></div>
    <?php else : ?>
        <header class="public-topbar">
            <div class="brand-block">
                <div class="brand-mark">Q</div>
                <div>
                    <div class="brand-name">QuizAI</div>
                    <div class="brand-subtitle">AI quiz generation and assessment</div>
                </div>
            </div>
            <nav class="public-nav">
                <a href="/index.php">Home</a>
                <a href="/login.php">Login</a>
                <a href="/signup.php">Sign up</a>
            </nav>
        </header>
        <main>
    <?php endif;
}

function quizai_render_end($mode = 'public')
{
    if ($mode === 'app') {
        echo '</main></div></div>';
    } else {
        echo '</main>';
    }
    ?>
</body>
</html>
    <?php
}
