<?php
require_once 'includes/bootstrap.php';
$connection = db();
$error = '';
$email_value = 'student@test.com';

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}

if (is_logged_in()) {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $email_value = $email;

    $stmt = $connection->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Invalid email or password';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login - Quiz AI</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="page">
        <div class="login-card">
            <div class="logo">Q</div>
            <h1>Quiz AI</h1>
            <p class="subtitle">Login with database account</p>

            <?php if ($error != '') { ?>
                <p class="error"><?php echo h($error); ?></p>
            <?php } ?>

            <form method="post">
                <label>Email</label>
                <input type="email" name="email" value="<?php echo h($email_value); ?>" required>

                <label>Password</label>
                <input type="password" name="password" value="12345" required>

                <button type="submit">Login</button>
            </form>

            <p class="form-link-row">
                New user? <a class="quiet-link" href="register.php">Create account</a>
            </p>

            <div class="note">
                <p><b>Demo email:</b> student@test.com</p>
                <p><b>Demo password:</b> 12345</p>
            </div>
        </div>
    </div>
</body>
</html>
