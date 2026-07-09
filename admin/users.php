<?php
require_once __DIR__ . '/../includes/bootstrap.php';
quizai_require_login('admin');
$db = quizai_db();

if ($db && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create_user') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = in_array($_POST['role'] ?? 'learner', ['admin', 'learner'], true) ? $_POST['role'] : 'learner';
        $password = (string) ($_POST['password'] ?? '');

        if ($name !== '' && $email !== '' && strlen($password) >= 6) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare('INSERT INTO users (name, email, password, role, email_verified_at) VALUES (?, ?, ?, ?, NOW())');
            $stmt->bind_param('ssss', $name, $email, $hash, $role);
            $stmt->execute();
            $stmt->close();
            quizai_flash('User created.', 'success');
        } else {
            quizai_flash('Please fill in name, email, role, and a password of at least 6 characters.', 'error');
        }

        header('Location: ' . quizai_base_url('admin/users.php'));
        exit;
    }

    if ($action === 'update_user') {
        $userId = (int) ($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = in_array($_POST['role'] ?? 'learner', ['admin', 'learner'], true) ? $_POST['role'] : 'learner';
        $password = (string) ($_POST['password'] ?? '');

        if ($userId > 0 && $name !== '' && $email !== '') {
            if ($password !== '') {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare('UPDATE users SET name = ?, email = ?, role = ?, password = ? WHERE id = ?');
                $stmt->bind_param('ssssi', $name, $email, $role, $hash, $userId);
            } else {
                $stmt = $db->prepare('UPDATE users SET name = ?, email = ?, role = ? WHERE id = ?');
                $stmt->bind_param('sssi', $name, $email, $role, $userId);
            }
            $stmt->execute();
            $stmt->close();
            quizai_flash('User updated.', 'success');
        } else {
            quizai_flash('Unable to update user.', 'error');
        }

        header('Location: ' . quizai_base_url('admin/users.php?edit=' . $userId));
        exit;
    }

    if ($action === 'delete_user') {
        $userId = (int) ($_POST['id'] ?? 0);
        $currentUser = quizai_current_user();

        if ($userId > 0 && (int) ($currentUser['id'] ?? 0) !== $userId) {
            $stmt = $db->prepare('DELETE FROM users WHERE id = ?');
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $stmt->close();
            quizai_flash('User deleted.', 'success');
        } else {
            quizai_flash('You cannot delete your own logged-in account.', 'error');
        }

        header('Location: ' . quizai_base_url('admin/users.php'));
        exit;
    }
}

$editUser = null;
if ($db && isset($_GET['edit'])) {
    $editId = (int) $_GET['edit'];
    $stmt = $db->prepare('SELECT id, name, email, role FROM users WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $editId);
    $stmt->execute();
    $editUser = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$users = $db ? $db->query('SELECT id, name, email, role, DATE_FORMAT(created_at, "%b %d, %Y") AS created_at FROM users ORDER BY id DESC')->fetch_all(MYSQLI_ASSOC) : [];

quizai_render_start('Manage Users', 'app', 'users');
?>
<div class="content-grid">
    <?php if (!$db) : ?>
        <section class="flash flash-info col-12">MySQL is not connected. The page still renders, but create/update/delete actions need XAMPP MySQL running to save changes.</section>
    <?php endif; ?>
    <section class="card col-5">
        <div class="section-title"><?php echo $editUser ? 'Edit user' : 'Add user'; ?></div>
        <form method="post">
            <input type="hidden" name="action" value="<?php echo $editUser ? 'update_user' : 'create_user'; ?>">
            <?php if ($editUser) : ?><input type="hidden" name="id" value="<?php echo (int) $editUser['id']; ?>"><?php endif; ?>
            <div class="field"><label>Name</label><input name="name" required value="<?php echo quizai_h($editUser['name'] ?? ''); ?>" placeholder="Full name"></div>
            <div class="field"><label>Email</label><input name="email" type="email" required value="<?php echo quizai_h($editUser['email'] ?? ''); ?>" placeholder="name@example.com"></div>
            <div class="field">
                <label>Role</label>
                <select name="role">
                    <option value="learner" <?php echo (($editUser['role'] ?? 'learner') === 'learner') ? 'selected' : ''; ?>>Learner</option>
                    <option value="admin" <?php echo (($editUser['role'] ?? '') === 'admin') ? 'selected' : ''; ?>>Admin</option>
                </select>
            </div>
            <div class="field"><label>Password <?php echo $editUser ? '(leave blank to keep current)' : ''; ?></label><input name="password" type="password" <?php echo $editUser ? '' : 'required'; ?> placeholder="At least 6 characters"></div>
            <div class="field-row">
                <button class="primary-button" type="submit"><?php echo $editUser ? 'Update user' : 'Create user'; ?></button>
                <?php if ($editUser) : ?><a class="secondary-button" href="<?php echo quizai_h(quizai_base_url('admin/users.php')); ?>">Cancel</a><?php endif; ?>
            </div>
        </form>
    </section>

    <section class="table-card col-7">
        <div class="section-title">Users</div>
        <table class="table">
            <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($users as $user) : ?>
                <tr>
                    <td><?php echo quizai_h($user['name']); ?></td>
                    <td><?php echo quizai_h($user['email']); ?></td>
                    <td><?php echo quizai_h($user['role']); ?></td>
                    <td>
                        <div class="field-row">
                            <a class="ghost-button" href="<?php echo quizai_h(quizai_base_url('admin/users.php?edit=' . (int) $user['id'])); ?>">Edit</a>
                            <form method="post" onsubmit="return confirm('Delete this user?');" style="display:inline; margin:0;">
                                <input type="hidden" name="action" value="delete_user">
                                <input type="hidden" name="id" value="<?php echo (int) $user['id']; ?>">
                                <button class="danger-button" type="submit">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</div>
<?php quizai_render_end('app'); ?>
