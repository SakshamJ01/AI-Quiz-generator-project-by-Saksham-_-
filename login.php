<?php
require_once __DIR__ . '/includes/bootstrap.php';
$db = quizai_db();

if (quizai_current_user()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = (string) ($_POST['password'] ?? '');

    if ($db) {
        $stmt = $db->prepare('SELECT id, name, email, password, role FROM users WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($user && (password_verify($password, $user['password']) || hash_equals($user['password'], $password))) {
            quizai_login_user($user);
            quizai_flash('Welcome back, ' . $user['name'] . '.', 'success');
            header('Location: ' . ($user['role'] === 'admin' ? 'admin/index.php' : 'dashboard.php'));
            exit;
        }
    } elseif ($email === 'admin@quizai.test' && $password === 'password123') {
        quizai_login_user(['id' => 1, 'name' => 'QuizAI Admin', 'email' => $email, 'role' => 'admin']);
        header('Location: admin/index.php');
        exit;
    } elseif ($email === 'learner@quizai.test' && $password === 'password123') {
        quizai_login_user(['id' => 2, 'name' => 'Demo Learner', 'email' => $email, 'role' => 'learner']);
        header('Location: dashboard.php');
        exit;
    }

    $error = 'Invalid email or password.';
}

quizai_render_start('Login', 'auth');
?>
<div class="auth-card">
    <div class="brand-block" style="margin-bottom: 18px;">
        <div class="brand-mark">Q</div>
        <div>
            <div class="brand-name">QuizAI</div>
            <div class="brand-subtitle">Welcome back</div>
        </div>
    </div>
    <h2>Sign in to continue</h2>
    <p>Use the demo account or connect your own MySQL database through XAMPP.</p>
    <?php echo quizai_flash(); ?>
    <?php if ($error) : ?>
        <div class="flash flash-error"><?php echo quizai_h($error); ?></div>
    <?php endif; ?>
    <form method="post">
        <div class="field">
            <label>Email</label>
            <input name="email" type="email" required placeholder="name@example.com">
        </div>
        <div class="field password-field">
            <label>Password</label>
            <div class="field-row" style="align-items: center;">
                <input name="password" type="password" required placeholder="••••••••" style="flex: 1;">
                <button class="ghost-button password-toggle" type="button"><span class="material-symbols-outlined">visibility_off</span></button>
            </div>
        </div>
        <div class="field-row" style="justify-content: space-between; align-items: center;">
            <label style="display: inline-flex; gap: 8px; align-items: center; margin: 0;"><input type="checkbox" name="remember"> Remember me</label>
            <a class="muted" href="forgot-password.php">Forgot password?</a>
        </div>
        <div class="field-row">
            <button class="primary-button" type="submit">Login</button>
            <a class="secondary-button" href="signup.php">Create account</a>
        </div>
    </form>
    <p class="center-note" style="margin-top: 16px;">Demo credentials: admin@quizai.test / password123</p>
</div>
<?php quizai_render_end('auth'); ?>
<?php
require_once __DIR__ . '/includes/bootstrap.php';
$db = quizai_db();

if (quizai_current_user()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = (string) ($_POST['password'] ?? '');

    if ($db) {
        $stmt = $db->prepare('SELECT id, name, email, password, role FROM users WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($user && (password_verify($password, $user['password']) || hash_equals($user['password'], $password))) {
            quizai_login_user($user);
            quizai_flash('Welcome back, ' . $user['name'] . '.', 'success');
            header('Location: ' . ($user['role'] === 'admin' ? 'admin/index.php' : 'dashboard.php'));
            exit;
        }
    } elseif ($email === 'admin@quizai.test' && $password === 'password123') {
        quizai_login_user(['id' => 1, 'name' => 'QuizAI Admin', 'email' => $email, 'role' => 'admin']);
        header('Location: admin/index.php');
        exit;
    } elseif ($email === 'learner@quizai.test' && $password === 'password123') {
        quizai_login_user(['id' => 2, 'name' => 'Demo Learner', 'email' => $email, 'role' => 'learner']);
        header('Location: dashboard.php');
        exit;
    }

    $error = 'Invalid email or password.';
}

quizai_render_start('Login', 'auth');
?>
<div class="auth-card">
    <div class="brand-block" style="margin-bottom: 18px;"><div class="brand-mark">Q</div><div><div class="brand-name">QuizAI</div><div class="brand-subtitle">Welcome back</div></div></div>
    <h2>Sign in to continue</h2>
    <p>Use the demo account or connect your own MySQL database through XAMPP.</p>
    <?php echo quizai_flash(); ?>
    <?php if ($error) : ?><div class="flash flash-error"><?php echo quizai_h($error); ?></div><?php endif; ?>
    <form method="post">
        <div class="field"><label>Email</label><input name="email" type="email" required placeholder="name@example.com"></div>
        <div class="field password-field"><label>Password</label><div class="field-row" style="align-items: center;"><input name="password" type="password" required placeholder="••••••••" style="flex: 1;"><button class="ghost-button password-toggle" type="button"><span class="material-symbols-outlined">visibility_off</span></button></div></div>
        <div class="field-row" style="justify-content: space-between; align-items: center;"><label style="display: inline-flex; gap: 8px; align-items: center; margin: 0;"><input type="checkbox" name="remember"> Remember me</label><a class="muted" href="forgot-password.php">Forgot password?</a></div>
        <div class="field-row"><button class="primary-button" type="submit">Login</button><a class="secondary-button" href="signup.php">Create account</a></div>
    </form>
    <p class="center-note" style="margin-top: 16px;">Demo credentials: admin@quizai.test / password123</p>
</div>
<?php quizai_render_end('auth'); ?>
