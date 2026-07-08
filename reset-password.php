<?php
require_once __DIR__ . '/includes/bootstrap.php';
$db = quizai_db();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = trim($_POST['token'] ?? '');
    $password = (string) ($_POST['password'] ?? '');

    if (strlen($password) < 6) {
        $message = 'Password must be at least 6 characters.';
    } elseif ($db) {
        $stmt = $db->prepare('SELECT user_id FROM password_resets WHERE token = ? AND expires_at > NOW() ORDER BY id DESC LIMIT 1');
        $stmt->bind_param('s', $token);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($row) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $userId = (int) $row['user_id'];
            $stmt = $db->prepare('UPDATE users SET password = ? WHERE id = ?');
            $stmt->bind_param('si', $hash, $userId);
            $stmt->execute();
            $stmt->close();

            $safeToken = $db->real_escape_string($token);
            $db->query("DELETE FROM password_resets WHERE token = '{$safeToken}'");
            $message = 'Password updated successfully.';
        } else {
            $message = 'Reset token is invalid or expired.';
        }
    } else {
        $message = 'Reset tokens require a live MySQL database.';
    }
}

quizai_render_start('Reset Password', 'auth');
?>
<div class="auth-card">
    <h2>Set a new password</h2>
    <p>Paste the reset token and choose a new password.</p>
    <?php if ($message) : ?>
        <div class="flash flash-info"><?php echo quizai_h($message); ?></div>
    <?php endif; ?>
    <form method="post">
        <div class="field">
            <label>Reset token</label>
            <input name="token" required placeholder="Paste token here">
        </div>
        <div class="field">
            <label>New password</label>
            <input name="password" type="password" required placeholder="New password">
        </div>
        <div class="field-row">
            <button class="primary-button" type="submit">Update password</button>
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
    $token = trim($_POST['token'] ?? '');
    $password = (string) ($_POST['password'] ?? '');

    if (strlen($password) < 6) {
        $message = 'Password must be at least 6 characters.';
    } elseif ($db) {
        $stmt = $db->prepare('SELECT user_id FROM password_resets WHERE token = ? AND expires_at > NOW() ORDER BY id DESC LIMIT 1');
        $stmt->bind_param('s', $token);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($row) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare('UPDATE users SET password = ? WHERE id = ?');
            $stmt->bind_param('si', $hash, $row['user_id']);
            $stmt->execute();
            $stmt->close();
            $safeToken = $db->real_escape_string($token);
            $db->query("DELETE FROM password_resets WHERE token = '$safeToken'");
            $message = 'Password updated successfully.';
        } else {
            $message = 'Reset token is invalid or expired.';
        }
    } else {
        $message = 'Reset tokens require a live MySQL database.';
    }
}

quizai_render_start('Reset Password', 'auth');
?>
<div class="auth-card">
    <h2>Set a new password</h2>
    <p>Paste the reset token and choose a new password.</p>
    <?php if ($message) : ?><div class="flash flash-info"><?php echo quizai_h($message); ?></div><?php endif; ?>
    <form method="post">
        <div class="field"><label>Reset token</label><input name="token" required placeholder="Paste token here"></div>
        <div class="field"><label>New password</label><input name="password" type="password" required placeholder="New password"></div>
        <div class="field-row"><button class="primary-button" type="submit">Update password</button><a class="secondary-button" href="login.php">Back to login</a></div>
    </form>
</div>
<?php quizai_render_end('auth'); ?>
