<?php
require_once __DIR__ . '/includes/bootstrap.php';
$db = quizai_db();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if ($db) {
        $stmt = $db->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($user) {
            $token = bin2hex(random_bytes(16));
            $expires = date('Y-m-d H:i:s', time() + 3600);
            $stmt = $db->prepare('INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)');
            $userId = (int) $user['id'];
            $stmt->bind_param('iss', $userId, $token, $expires);
            $stmt->execute();
            $stmt->close();
        }
    }

    $message = 'If the email exists, a reset link would be generated in a production setup.';
}

quizai_render_start('Forgot Password', 'auth');
?>
<div class="auth-card">
    <h2>Reset your password</h2>
    <p>Enter your email and we will prepare a reset token in MySQL.</p>
    <?php if ($message) : ?>
        <div class="flash flash-info"><?php echo quizai_h($message); ?></div>
    <?php endif; ?>
    <form method="post">
        <div class="field">
            <label>Email</label>
            <input name="email" type="email" required placeholder="name@example.com">
        </div>
        <div class="field-row">
            <button class="primary-button" type="submit">Send reset link</button>
            <a class="secondary-button" href="login.php">Back to login</a>
        </div>
    </form>
</div>
<?php quizai_render_end('auth'); ?>
<?php
require_once __DIR__ . '/includes/bootstrap.php';
$db = quizai_db();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if ($db) {
        $stmt = $db->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($user) {
            $token = bin2hex(random_bytes(16));
            $expires = date('Y-m-d H:i:s', time() + 3600);
            $stmt = $db->prepare('INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)');
            $stmt->bind_param('iss', $user['id'], $token, $expires);
            $stmt->execute();
            $stmt->close();
        }
    }

    $message = 'If the email exists, a reset link would be generated in a production setup.';
}

quizai_render_start('Forgot Password', 'auth');
?>
<div class="auth-card">
    <h2>Reset your password</h2>
    <p>Enter your email and we will prepare a reset token in MySQL.</p>
    <?php if ($message) : ?><div class="flash flash-info"><?php echo quizai_h($message); ?></div><?php endif; ?>
    <form method="post">
        <div class="field"><label>Email</label><input name="email" type="email" required placeholder="name@example.com"></div>
        <div class="field-row"><button class="primary-button" type="submit">Send reset link</button><a class="secondary-button" href="login.php">Back to login</a></div>
    </form>
</div>
<?php quizai_render_end('auth'); ?>
