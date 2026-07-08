<?php
require_once __DIR__ . '/includes/bootstrap.php';
quizai_require_login();
$user = quizai_current_user();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = 'Profile changes are ready for database wiring.';
    quizai_flash('Profile updated.', 'success');
}

quizai_render_start('Profile Settings', 'app', 'profile');
?>
<div class="content-grid">
    <section class="card col-7">
        <div class="section-title">Profile</div>
        <?php if ($message) : ?><div class="flash flash-info"><?php echo quizai_h($message); ?></div><?php endif; ?>
        <form method="post">
            <div class="field"><label>Name</label><input value="<?php echo quizai_h($user['name'] ?? ''); ?>" name="name"></div>
            <div class="field"><label>Email</label><input value="<?php echo quizai_h($user['email'] ?? ''); ?>" name="email" type="email"></div>
            <div class="field-row">
                <button class="primary-button" type="submit">Save changes</button>
                <a class="secondary-button" href="logout.php">Logout</a>
            </div>
        </form>
    </section>

    <aside class="card col-5">
        <div class="section-title">Account summary</div>
        <div class="metric-stack">
            <div><strong>Role</strong><div class="muted"><?php echo quizai_h($user['role'] ?? 'visitor'); ?></div></div>
            <div><strong>Status</strong><div class="muted">Connected to the app shell</div></div>
            <div><strong>Security</strong><div class="muted">Password reset and verification pages included</div></div>
        </div>
    </aside>
</div>
<?php quizai_render_end('app'); ?>
