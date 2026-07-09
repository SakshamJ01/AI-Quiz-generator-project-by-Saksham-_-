<?php
require_once __DIR__ . '/../includes/bootstrap.php';
quizai_require_login('admin');
$db = quizai_db();

if ($db && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save_setting') {
        $settingKey = trim($_POST['setting_key'] ?? '');
        $settingValue = trim($_POST['setting_value'] ?? '');

        if ($settingKey !== '') {
            $stmt = $db->prepare('INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');
            $stmt->bind_param('ss', $settingKey, $settingValue);
            $stmt->execute();
            $stmt->close();
            quizai_flash('Setting saved.', 'success');
        } else {
            quizai_flash('Setting key is required.', 'error');
        }

        header('Location: ' . quizai_base_url('admin/settings.php'));
        exit;
    }

    if ($action === 'delete_setting') {
        $settingKey = trim($_POST['setting_key'] ?? '');

        if ($settingKey !== '') {
            $stmt = $db->prepare('DELETE FROM system_settings WHERE setting_key = ?');
            $stmt->bind_param('s', $settingKey);
            $stmt->execute();
            $stmt->close();
            quizai_flash('Setting deleted.', 'success');
        }

        header('Location: ' . quizai_base_url('admin/settings.php'));
        exit;
    }
}

$settings = [];
if ($db) {
    $settings = $db->query('SELECT setting_key, setting_value FROM system_settings ORDER BY setting_key ASC')->fetch_all(MYSQLI_ASSOC);
}

quizai_render_start('System Settings', 'app', 'settings');
?>
<div class="content-grid">
    <?php if (!$db) : ?>
        <section class="flash flash-info col-12">MySQL is not connected. Settings can be viewed, but save/delete actions need the database online.</section>
    <?php endif; ?>
    <section class="card col-5">
        <div class="section-title">Add or update setting</div>
        <form method="post">
            <input type="hidden" name="action" value="save_setting">
            <div class="field"><label>Key</label><input name="setting_key" required placeholder="site_name"></div>
            <div class="field"><label>Value</label><textarea name="setting_value" required placeholder="Value to store"></textarea></div>
            <button class="primary-button" type="submit">Save setting</button>
        </form>
    </section>

    <section class="table-card col-7">
        <div class="section-title">Stored settings</div>
        <table class="table">
            <thead><tr><th>Key</th><th>Value</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($settings as $setting) : ?>
                <tr>
                    <td><?php echo quizai_h($setting['setting_key']); ?></td>
                    <td><?php echo quizai_h($setting['setting_value']); ?></td>
                    <td>
                        <form method="post" onsubmit="return confirm('Delete this setting?');" style="display:inline; margin:0;">
                            <input type="hidden" name="action" value="delete_setting">
                            <input type="hidden" name="setting_key" value="<?php echo quizai_h($setting['setting_key']); ?>">
                            <button class="danger-button" type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</div>
<?php quizai_render_end('app'); ?>
