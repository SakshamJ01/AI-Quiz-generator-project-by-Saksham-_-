<?php
require_once 'includes/bootstrap.php';
$connection = db();
$error = '';
$success = '';
$name_value = '';
$email_value = '';

if (is_logged_in()) {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    $name_value = $name;
    $email_value = $email;

    if ($name == '' || $email == '' || $password == '' || $confirm_password == '') {
        $error = 'Please fill all fields';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Enter a valid email address';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } else {
        $stmt = $connection->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = 'This email already exists';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insert = $connection->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            $insert->bind_param('sss', $name, $email, $hashed_password);

            if ($insert->execute()) {
                $success = 'Account created. You can now log in.';
                $name_value = '';
                $email_value = '';
            } else {
                $error = 'Could not create account right now';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register - Quiz AI</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="page">
        <div class="login-card">
            <div class="logo">Q</div>
            <h1>Create Account</h1>
            <p class="subtitle">Register a new database user for this quiz project</p>

            <?php if ($error != '') { ?>
                <p class="error"><?php echo h($error); ?></p>
            <?php } ?>

            <?php if ($success != '') { ?>
                <p class="success-message"><?php echo h($success); ?></p>
            <?php } ?>

            <form method="post">
                <label>Name</label>
                <input type="text" name="name" value="<?php echo h($name_value); ?>" required>

                <label>Email</label>
                <input type="email" name="email" value="<?php echo h($email_value); ?>" required>

                <label>Password</label>
                <input type="password" name="password" required>

                <label>Confirm Password</label>
                <input type="password" name="confirm_password" required>

                <button type="submit">Create Account</button>
            </form>

            <p class="form-link-row">
                Already have an account? <a class="quiet-link" href="login.php">Back to login</a>
            </p>
        </div>
    </div>
</body>
</html>
