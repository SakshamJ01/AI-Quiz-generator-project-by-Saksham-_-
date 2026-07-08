<?php
require_once __DIR__ . '/includes/bootstrap.php';
quizai_require_login();

$db = quizai_db();
$user = quizai_current_user();
$editQuiz = null;

if ($db && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save_quiz') {
        $quizId = (int) ($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $categoryId = (int) ($_POST['category_id'] ?? 0);
        $timeLimit = max(1, (int) ($_POST['time_limit'] ?? 10));
        $status = in_array($_POST['status'] ?? 'draft', ['draft', 'published'], true) ? $_POST['status'] : 'draft';
        $createdBy = (int) ($user['id'] ?? 0);

        if ($title !== '' && $categoryId > 0) {
            if ($quizId > 0) {
                $stmt = $db->prepare('UPDATE quizzes SET title = ?, description = ?, category_id = ?, time_limit = ?, status = ? WHERE id = ?');
                $stmt->bind_param('ssiisi', $title, $description, $categoryId, $timeLimit, $status, $quizId);
                $stmt->execute();
                $stmt->close();
                quizai_flash('Quiz updated.', 'success');
            } else {
                $questionCount = 3;
                $stmt = $db->prepare('INSERT INTO quizzes (title, description, category_id, created_by, status, total_questions, time_limit) VALUES (?, ?, ?, ?, ?, ?, ?)');
                $stmt->bind_param('ssiisii', $title, $description, $categoryId, $createdBy, $status, $questionCount, $timeLimit);
                $stmt->execute();
                $quizId = $stmt->insert_id;
                $stmt->close();

                $sampleQuestions = [
                    ['What is the correct PHP opening tag?', ['<?php', '<php>', 'php{', '{{php}}'], 0],
                    ['Which SQL command reads data from a table?', ['SELECT', 'INSERT', 'UPDATE', 'DELETE'], 0],
                    ['Which HTML tag creates a link?', ['<a>', '<div>', '<p>', '<span>'], 0]
                ];
                $questionStmt = $db->prepare('INSERT INTO quiz_questions (quiz_id, question_text, question_type, points, sort_order) VALUES (?, ?, "mcq", 1, ?)');
                $optionStmt = $db->prepare('INSERT INTO quiz_options (question_id, option_text, is_correct, sort_order) VALUES (?, ?, ?, ?)');
                foreach ($sampleQuestions as $questionIndex => $sampleQuestion) {
                    $questionText = $sampleQuestion[0];
                    $sortOrder = $questionIndex + 1;
                    $questionStmt->bind_param('isi', $quizId, $questionText, $sortOrder);
                    $questionStmt->execute();
                    $questionId = $questionStmt->insert_id;

                    foreach ($sampleQuestion[1] as $optionIndex => $optionText) {
                        $isCorrect = $optionIndex === $sampleQuestion[2] ? 1 : 0;
                        $optionOrder = $optionIndex + 1;
                        $optionStmt->bind_param('isii', $questionId, $optionText, $isCorrect, $optionOrder);
                        $optionStmt->execute();
                    }
                }
                $questionStmt->close();
                $optionStmt->close();

                quizai_flash('Quiz created.', 'success');
            }
        } else {
            quizai_flash('Title and category are required.', 'error');
        }

        header('Location: /quiz-create.php' . ($quizId > 0 ? '?edit=' . $quizId : ''));
        exit;
    }

    if ($action === 'delete_quiz') {
        $quizId = (int) ($_POST['id'] ?? 0);
        if ($quizId > 0) {
            $stmt = $db->prepare('DELETE FROM quizzes WHERE id = ?');
            $stmt->bind_param('i', $quizId);
            $stmt->execute();
            $stmt->close();
            quizai_flash('Quiz deleted.', 'success');
        }

        header('Location: /quiz-create.php');
        exit;
    }

    if ($action === 'toggle_status') {
        $quizId = (int) ($_POST['id'] ?? 0);
        $newStatus = ($_POST['status'] ?? 'draft') === 'published' ? 'published' : 'draft';
        if ($quizId > 0) {
            $stmt = $db->prepare('UPDATE quizzes SET status = ? WHERE id = ?');
            $stmt->bind_param('si', $newStatus, $quizId);
            $stmt->execute();
            $stmt->close();
            quizai_flash('Quiz status updated.', 'success');
        }

        header('Location: /quiz-create.php');
        exit;
    }
}

if ($db && isset($_GET['edit'])) {
    $editId = (int) $_GET['edit'];
    $stmt = $db->prepare('SELECT id, title, description, category_id, status, time_limit FROM quizzes WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $editId);
    $stmt->execute();
    $editQuiz = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$categories = $db ? $db->query('SELECT id, name FROM quiz_categories ORDER BY name ASC')->fetch_all(MYSQLI_ASSOC) : [];
$quizzes = $db ? $db->query('SELECT q.id, q.title, q.status, q.time_limit, c.name AS category_name, DATE_FORMAT(q.created_at, "%b %d, %Y") AS created_at FROM quizzes q LEFT JOIN quiz_categories c ON c.id = q.category_id ORDER BY q.id DESC')->fetch_all(MYSQLI_ASSOC) : [];

quizai_render_start('Generate Quiz', 'app', 'quiz-create');
?>
<div class="content-grid">
    <section class="card col-6">
        <div class="section-title"><?php echo $editQuiz ? 'Edit quiz' : 'Create quiz'; ?></div>
        <p class="card-copy">This page now works as a real quiz manager. You can create, update, publish, and delete quizzes from here.</p>
        <form method="post">
            <input type="hidden" name="action" value="save_quiz">
            <?php if ($editQuiz) : ?><input type="hidden" name="id" value="<?php echo (int) $editQuiz['id']; ?>"><?php endif; ?>
            <div class="field"><label>Quiz title</label><input name="title" required value="<?php echo quizai_h($editQuiz['title'] ?? ''); ?>" placeholder="Example: PHP Fundamentals"></div>
            <div class="field"><label>Description</label><textarea name="description" placeholder="Describe the quiz scope and audience"><?php echo quizai_h($editQuiz['description'] ?? ''); ?></textarea></div>
            <div class="field-row">
                <div class="field" style="flex: 1; min-width: 220px;">
                    <label>Category</label>
                    <select name="category_id" required>
                        <option value="">Select category</option>
                        <?php foreach ($categories as $category) : ?>
                            <option value="<?php echo (int) $category['id']; ?>" <?php echo ((int) ($editQuiz['category_id'] ?? 0) === (int) $category['id']) ? 'selected' : ''; ?>><?php echo quizai_h($category['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field" style="flex: 1; min-width: 220px;"><label>Time limit (minutes)</label><input name="time_limit" type="number" min="1" value="<?php echo (int) ($editQuiz['time_limit'] ?? 10); ?>"></div>
            </div>
            <div class="field-row">
                <div class="field" style="flex: 1; min-width: 220px;">
                    <label>Status</label>
                    <select name="status">
                        <option value="draft" <?php echo (($editQuiz['status'] ?? 'draft') === 'draft') ? 'selected' : ''; ?>>Draft</option>
                        <option value="published" <?php echo (($editQuiz['status'] ?? '') === 'published') ? 'selected' : ''; ?>>Published</option>
                    </select>
                </div>
                <div class="field" style="flex: 1; min-width: 220px;">
                    <label>Questions</label>
                    <input value="3 sample questions" disabled>
                </div>
            </div>
            <div class="field-row">
                <button class="primary-button" type="submit"><?php echo $editQuiz ? 'Update quiz' : 'Create quiz'; ?></button>
                <?php if ($editQuiz) : ?><a class="secondary-button" href="/quiz-create.php">Cancel</a><?php endif; ?>
                <a class="secondary-button" href="/take-quiz.php">Open sample quiz</a>
            </div>
        </form>
    </section>

    <aside class="card col-6">
        <div class="section-title">How it maps to the design</div>
        <div class="metric-stack">
            <div><strong>Prompt input</strong><div class="muted">Starts from title, description, and category.</div></div>
            <div><strong>Question draft</strong><div class="muted">Stores real quiz records in MySQL.</div></div>
            <div><strong>Publish path</strong><div class="muted">You can toggle draft and published from the list.</div></div>
        </div>
    </aside>

    <section class="table-card col-12">
        <div class="section-title">Saved quizzes</div>
        <table class="table">
            <thead><tr><th>Title</th><th>Category</th><th>Status</th><th>Time</th><th>Created</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($quizzes as $quiz) : ?>
                <tr>
                    <td><?php echo quizai_h($quiz['title']); ?></td>
                    <td><?php echo quizai_h($quiz['category_name']); ?></td>
                    <td><?php echo quizai_h($quiz['status']); ?></td>
                    <td><?php echo (int) $quiz['time_limit']; ?> mins</td>
                    <td><?php echo quizai_h($quiz['created_at']); ?></td>
                    <td>
                        <div class="field-row">
                            <a class="ghost-button" href="/quiz-create.php?edit=<?php echo (int) $quiz['id']; ?>">Edit</a>
                            <form method="post" style="display:inline; margin:0;">
                                <input type="hidden" name="action" value="toggle_status">
                                <input type="hidden" name="id" value="<?php echo (int) $quiz['id']; ?>">
                                <input type="hidden" name="status" value="<?php echo $quiz['status'] === 'published' ? 'draft' : 'published'; ?>">
                                <button class="secondary-button" type="submit"><?php echo $quiz['status'] === 'published' ? 'Unpublish' : 'Publish'; ?></button>
                            </form>
                            <form method="post" onsubmit="return confirm('Delete this quiz?');" style="display:inline; margin:0;">
                                <input type="hidden" name="action" value="delete_quiz">
                                <input type="hidden" name="id" value="<?php echo (int) $quiz['id']; ?>">
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