<?php
require_once __DIR__ . '/../includes/bootstrap.php';
quizai_require_login('admin');
$db = quizai_db();
$editCategory = null;

if ($db && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create_category') {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if ($name !== '') {
            $stmt = $db->prepare('INSERT INTO quiz_categories (name, description) VALUES (?, ?)');
            $stmt->bind_param('ss', $name, $description);
            $stmt->execute();
            $stmt->close();
            quizai_flash('Category created.', 'success');
        } else {
            quizai_flash('Category name is required.', 'error');
        }

        header('Location: /admin/categories.php');
        exit;
    }

    if ($action === 'update_category') {
        $categoryId = (int) ($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if ($categoryId > 0 && $name !== '') {
            $stmt = $db->prepare('UPDATE quiz_categories SET name = ?, description = ? WHERE id = ?');
            $stmt->bind_param('ssi', $name, $description, $categoryId);
            $stmt->execute();
            $stmt->close();
            quizai_flash('Category updated.', 'success');
        } else {
            quizai_flash('Unable to update category.', 'error');
        }

        header('Location: /admin/categories.php?edit=' . $categoryId);
        exit;
    }

    if ($action === 'delete_category') {
        $categoryId = (int) ($_POST['id'] ?? 0);
        if ($categoryId > 0) {
            $stmt = $db->prepare('DELETE FROM quiz_categories WHERE id = ?');
            $stmt->bind_param('i', $categoryId);
            $stmt->execute();
            $stmt->close();
            quizai_flash('Category deleted.', 'success');
        }

        header('Location: /admin/categories.php');
        exit;
    }
}

if ($db && isset($_GET['edit'])) {
    $editId = (int) $_GET['edit'];
    $stmt = $db->prepare('SELECT id, name, description FROM quiz_categories WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $editId);
    $stmt->execute();
    $editCategory = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$categories = $db ? $db->query('SELECT id, name, description, DATE_FORMAT(created_at, "%b %d, %Y") AS created_at FROM quiz_categories ORDER BY id DESC')->fetch_all(MYSQLI_ASSOC) : [];

quizai_render_start('Categories', 'app', 'categories');
?>
<div class="content-grid">
    <?php if (!$db) : ?>
        <section class="flash flash-info col-12">MySQL is not connected. Category changes will only persist after XAMPP MySQL is running.</section>
    <?php endif; ?>
    <section class="card col-6">
        <div class="section-title"><?php echo $editCategory ? 'Edit category' : 'Add category'; ?></div>
        <form method="post">
            <input type="hidden" name="action" value="<?php echo $editCategory ? 'update_category' : 'create_category'; ?>">
            <?php if ($editCategory) : ?><input type="hidden" name="id" value="<?php echo (int) $editCategory['id']; ?>"><?php endif; ?>
            <div class="field"><label>Name</label><input name="name" required value="<?php echo quizai_h($editCategory['name'] ?? ''); ?>" placeholder="New category"></div>
            <div class="field"><label>Description</label><textarea name="description" placeholder="Category description"><?php echo quizai_h($editCategory['description'] ?? ''); ?></textarea></div>
            <div class="field-row">
                <button class="primary-button" type="submit"><?php echo $editCategory ? 'Update category' : 'Save category'; ?></button>
                <?php if ($editCategory) : ?><a class="secondary-button" href="/admin/categories.php">Cancel</a><?php endif; ?>
            </div>
        </form>
    </section>

    <section class="table-card col-6">
        <div class="section-title">Category list</div>
        <table class="table">
            <thead><tr><th>Name</th><th>Description</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($categories as $category) : ?>
                <tr>
                    <td><?php echo quizai_h($category['name']); ?></td>
                    <td><?php echo quizai_h($category['description']); ?></td>
                    <td>
                        <div class="field-row">
                            <a class="ghost-button" href="/admin/categories.php?edit=<?php echo (int) $category['id']; ?>">Edit</a>
                            <form method="post" onsubmit="return confirm('Delete this category?');" style="display:inline; margin:0;">
                                <input type="hidden" name="action" value="delete_category">
                                <input type="hidden" name="id" value="<?php echo (int) $category['id']; ?>">
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
