<?php
require_once __DIR__ . '/includes/bootstrap.php';
$db = quizai_db();
$error = '';

if (quizai_current_user()) {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = (string) ($_POST['password'] ?? '');

    if (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($db) {
        $stmt = $db->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $exists = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($exists) {
            $error = 'That email is already registered.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare('INSERT INTO users (name, email, password, role, email_verified_at) VALUES (?, ?, ?, "learner", NOW())');
            $stmt->bind_param('sss', $name, $email, $hash);
            $stmt->execute();
            $userId = $stmt->insert_id;
            $stmt->close();
            quizai_login_user(['id' => $userId, 'name' => $name, 'email' => $email, 'role' => 'learner']);
            quizai_flash('Account created successfully.', 'success');
            header('Location: dashboard.php');
            exit;
        }
    } else {
        quizai_login_user(['id' => 99, 'name' => $name, 'email' => $email, 'role' => 'learner']);
        quizai_flash('Demo account created locally.', 'success');
        header('Location: dashboard.php');
        exit;
    }
}

quizai_render_start('Sign Up', 'auth');
?>
<div class="auth-card">
    <div class="brand-block" style="margin-bottom: 18px;">
        <div class="brand-mark">Q</div>
        <div>
            <div class="brand-name">QuizAI</div>
            <div class="brand-subtitle">Create your account</div>
        </div>
    </div>
    <h2>Join the quiz workspace</h2>
    <p>Build, run, and review quizzes with a clean PHP/MySQL stack.</p>
    <?php echo quizai_flash(); ?>
    <?php if ($error) : ?>
        <div class="flash flash-error"><?php echo quizai_h($error); ?></div>
    <?php endif; ?>
    <form method="post">
        <div class="field">
            <label>Name</label>
            <input name="name" required placeholder="Your name">
        </div>
        <div class="field">
            <label>Email</label>
            <input name="email" type="email" required placeholder="name@example.com">
        </div>
        <div class="field">
            <label>Password</label>
            <input name="password" type="password" required placeholder="At least 6 characters">
        </div>
        <div class="field-row">
            <button class="primary-button" type="submit">Create account</button>
            <a class="secondary-button" href="login.php">Back to login</a>
        </div>
    </form>
</div>
<?php quizai_render_end('auth'); ?>
<?php
require_once __DIR__ . '/includes/bootstrap.php';
$db = quizai_db();
$error = '';

if (quizai_current_user()) {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = (string) ($_POST['password'] ?? '');

    if (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($db) {
        $stmt = $db->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $exists = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($exists) {
            $error = 'That email is already registered.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare('INSERT INTO users (name, email, password, role, email_verified_at) VALUES (?, ?, ?, "learner", NOW())');
            $stmt->bind_param('sss', $name, $email, $hash);
            $stmt->execute();
            $userId = $stmt->insert_id;
            $stmt->close();
            quizai_login_user(['id' => $userId, 'name' => $name, 'email' => $email, 'role' => 'learner']);
            quizai_flash('Account created successfully.', 'success');
            header('Location: dashboard.php');
            exit;
        }
    } else {
        quizai_login_user(['id' => 99, 'name' => $name, 'email' => $email, 'role' => 'learner']);
        quizai_flash('Demo account created locally.', 'success');
        header('Location: dashboard.php');
        exit;
    }
}

quizai_render_start('Sign Up', 'auth');
?>
<div class="auth-card">
    <div class="brand-block" style="margin-bottom: 18px;"><div class="brand-mark">Q</div><div><div class="brand-name">QuizAI</div><div class="brand-subtitle">Create your account</div></div></div>
    <h2>Join the quiz workspace</h2>
    <p>Build, run, and review quizzes with a clean PHP/MySQL stack.</p>
    <?php echo quizai_flash(); ?>
    <?php if ($error) : ?><div class="flash flash-error"><?php echo quizai_h($error); ?></div><?php endif; ?>
    <form method="post">
        <div class="field"><label>Name</label><input name="name" required placeholder="Your name"></div>
        <div class="field"><label>Email</label><input name="email" type="email" required placeholder="name@example.com"></div>
        <div class="field"><label>Password</label><input name="password" type="password" required placeholder="At least 6 characters"></div>
        <div class="field-row"><button class="primary-button" type="submit">Create account</button><a class="secondary-button" href="login.php">Back to login</a></div>
    </form>
</div>
<?php quizai_render_end('auth'); ?>
