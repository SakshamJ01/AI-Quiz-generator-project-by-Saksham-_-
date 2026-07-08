<?php
require_once __DIR__ . '/includes/bootstrap.php';
quizai_render_start('Verify Email', 'auth');
?>
<div class="auth-card">
    <h2>Email verification</h2>
    <p>This screen matches the design set and can later be wired to token verification.</p>
    <div class="flash flash-info">Verification flow scaffold is in place.</div>
    <div class="field-row">
        <a class="primary-button" href="login.php">Continue to login</a>
        <a class="secondary-button" href="signup.php">Back to sign up</a>
    </div>
</div>
<?php quizai_render_end('auth'); ?>
<?php
require_once __DIR__ . '/includes/bootstrap.php';
quizai_render_start('Verify Email', 'auth');
?>
<div class="auth-card">
    <h2>Email verification</h2>
    <p>This screen matches the design set and can later be wired to token verification.</p>
    <div class="flash flash-info">Verification flow scaffold is in place.</div>
    <div class="field-row"><a class="primary-button" href="login.php">Continue to login</a><a class="secondary-button" href="signup.php">Back to sign up</a></div>
</div>
<?php quizai_render_end('auth'); ?>
