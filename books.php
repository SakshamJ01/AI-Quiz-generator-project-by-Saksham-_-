<?php
require_once 'includes/bootstrap.php';
require_login();

$show_fallback = isset($_SESSION['reading_topic']) || isset($_SESSION['reading_error']);
$topic = $_SESSION['reading_topic'] ?? '';
$error_message = $_SESSION['reading_error'] ?? '';
unset($_SESSION['reading_topic'], $_SESSION['reading_error']);

$books = [];

$library_file = __DIR__ . '/uploads/library.json';
$library_data = json_decode(file_get_contents($library_file), true);

if (!is_array($library_data)) {
    $library_data = [];
}

$library_message = '';

if (isset($_POST['delete_book'])) {
    $file_to_delete = $_POST['book_file'] ?? '';

    foreach ($library_data as $index => $book) {
        if (!isset($book['file']) || $book['file'] !== $file_to_delete) {
            continue;
        }

        $book_path = __DIR__ . '/' . $book['file'];
        $cover_path = isset($book['cover']) ? __DIR__ . '/' . $book['cover'] : '';

        if (is_file($book_path)) {
            unlink($book_path);
        }

        if ($cover_path != '' && is_file($cover_path)) {
            unlink($cover_path);
        }

        array_splice($library_data, $index, 1);
        file_put_contents($library_file, json_encode($library_data, JSON_PRETTY_PRINT), LOCK_EX);
        $library_message = 'The book was removed from your library.';
        break;
    }
}

foreach ($library_data as $book) {
    if (isset($book['title'], $book['file'], $book['cover'])) {
        $books[] = $book;
    }
}

$upload_message = '';
$upload_success = '';

if (isset($_POST['upload_book'])) {
    $title = trim($_POST['title'] ?? '');
    $book_file = $_FILES['book_file'] ?? null;
    $cover_file = $_FILES['cover_file'] ?? null;
    $max_book_size = 20 * 1024 * 1024;
    $max_cover_size = 5 * 1024 * 1024;

    if ($title == '' || !$book_file || !$cover_file) {
        $upload_message = 'Add a book title, PDF or EPUB file, and cover image.';
    } elseif ($book_file['error'] != UPLOAD_ERR_OK || $cover_file['error'] != UPLOAD_ERR_OK) {
        $upload_message = 'The upload did not complete. Please try again.';
    } elseif ($book_file['size'] > $max_book_size || $cover_file['size'] > $max_cover_size) {
        $upload_message = 'The PDF or EPUB must be 20 MB or smaller and the cover must be 5 MB or smaller.';
    } else {
        $book_extension = strtolower(pathinfo($book_file['name'], PATHINFO_EXTENSION));
        $book_mime = mime_content_type($book_file['tmp_name']);
        $cover_info = @getimagesize($cover_file['tmp_name']);

        $valid_pdf = $book_extension == 'pdf' && $book_mime == 'application/pdf';
        $valid_epub = $book_extension == 'epub' && in_array($book_mime, ['application/epub+zip', 'application/zip'], true);
        $valid_epub_archive = true;

        if ($valid_epub && function_exists('exec')) {
            $archive_output = [];
            $archive_status = 0;
            exec('tar.exe -tf ' . escapeshellarg($book_file['tmp_name']) . ' 2>&1', $archive_output, $archive_status);
            $valid_epub_archive = $archive_status === 0;
        }

        if (!$valid_pdf && (!$valid_epub || !$valid_epub_archive)) {
            if ($valid_epub && !$valid_epub_archive) {
                $upload_message = 'This EPUB file is incomplete or corrupted. Download it again and re-upload it.';
            } else {
            $upload_message = 'Only valid PDF or EPUB books can be uploaded.';
            }
        } elseif ($cover_info === false || !in_array($cover_info['mime'], ['image/jpeg', 'image/png', 'image/webp'], true)) {
            $upload_message = 'The cover must be a JPG, PNG, or WebP image.';
        } else {
            $book_id = 'book_' . date('YmdHis') . '_' . bin2hex(random_bytes(4));
            $book_path = __DIR__ . '/uploads/books/' . $book_id . '.' . $book_extension;
            $cover_extension = $cover_info['mime'] == 'image/png' ? 'png' : ($cover_info['mime'] == 'image/webp' ? 'webp' : 'jpg');
            $cover_path = __DIR__ . '/uploads/covers/' . $book_id . '.' . $cover_extension;

            if (move_uploaded_file($book_file['tmp_name'], $book_path) && move_uploaded_file($cover_file['tmp_name'], $cover_path)) {
                $library_data[] = [
                    'title' => $title,
                    'description' => 'Uploaded offline book',
                    'file' => 'uploads/books/' . $book_id . '.' . $book_extension,
                    'cover' => 'uploads/covers/' . $book_id . '.' . $cover_extension,
                    'label' => 'Read book'
                ];
                file_put_contents($library_file, json_encode($library_data, JSON_PRETTY_PRINT), LOCK_EX);
                $books[] = end($library_data);
                $upload_success = 'Your book was added to the library.';
            } else {
                $upload_message = 'The files could not be saved. Check the uploads folder permissions.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Reading Library - Quiz AI</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="app-shell">
        <aside class="sidebar">
            <div class="brand"><span class="brand-mark">Q</span> QuizWhizAI</div>
            <div class="sidebar-line"></div>
            <nav class="side-nav">
                <a href="dashboard.php"><span class="menu-icon">D</span>Dashboard</a>
                <a href="take-quiz.php"><span class="menu-icon">Q</span>Take Quiz</a>
                <a class="active" href="books.php"><span class="menu-icon">B</span>Reading Library</a>
                <a href="login.php?logout=1"><span class="menu-icon">L</span>Logout</a>
            </nav>
            <div class="sidebar-bottom">PHP + MySQL project</div>
        </aside>

        <main class="main-area">
            <header class="topbar">
                <span class="topbar-title">Student Panel</span>
                <div class="profile"><span class="avatar">SA</span><span><b><?php echo h($_SESSION['user_name']); ?></b><small>Student account</small></span></div>
            </header>

            <div class="content">
                <div class="page-heading"><div><p class="eyebrow">OFFLINE READING</p><h1>Your Book Library</h1><p class="library-subtitle">Read your saved books anytime, even when quiz generation is unavailable.</p></div><a class="quiet-link" href="take-quiz.php">Take a quiz &rarr;</a></div>

                <?php if ($show_fallback) { ?>
                    <div class="card reading-notice">
                        <span class="fallback-spark">PLAN B ACTIVATED</span>
                        <h2>Ahh, bad day bro... no money for the API key 😂</h2>
                        <p>Don’t worry, I got you. Your offline books are ready while we could not create a quiz about <b><?php echo h($topic); ?></b>.</p>
                        <small class="fallback-detail"><?php echo h($error_message); ?></small>
                    </div>
                <?php } ?>

                <div class="card upload-card">
                    <div><p class="eyebrow">ADD TO LIBRARY</p><h2>Upload a book</h2><p>Choose a PDF or EPUB file and its cover image.</p></div>
                    <?php if ($upload_message != '') { ?><p class="error upload-feedback"><?php echo h($upload_message); ?></p><?php } ?>
                    <?php if ($upload_success != '') { ?><p class="success-message upload-feedback"><?php echo h($upload_success); ?></p><?php } ?>
                    <form method="post" enctype="multipart/form-data" class="book-upload-form">
                        <div><label for="title">Book title</label><input id="title" type="text" name="title" placeholder="Example: Learn JavaScript" required></div>
                        <div><label for="book_file">Book PDF or EPUB</label><input id="book_file" type="file" name="book_file" accept="application/pdf,.epub" required></div>
                        <div><label for="cover_file">Cover image</label><input id="cover_file" type="file" name="cover_file" accept="image/jpeg,image/png,image/webp" required></div>
                        <button type="submit" name="upload_book">Add book</button>
                    </form>
                </div>

                <?php if ($library_message != '') { ?><p class="success-message library-feedback"><?php echo h($library_message); ?></p><?php } ?>

                <div class="book-grid">
                    <?php foreach ($books as $book) { ?>
                        <article class="card book-card">
                            <?php if ($book['cover'] != '') { ?>
                                <img class="book-cover" src="<?php echo h($book['cover']); ?>" alt="Cover of <?php echo h($book['title']); ?>">
                            <?php } else { ?>
                                <span class="book-mark">BOOK</span>
                            <?php } ?>
                            <h2><?php echo h($book['title']); ?></h2>
                            <p><?php echo h($book['description']); ?></p>
                            <div class="book-actions">
                                <a class="button-link" href="reader.php?book=<?php echo rawurlencode($book['file']); ?>"><?php echo h($book['label']); ?></a>
                                <?php if (strpos($book['file'], 'uploads/') === 0) { ?>
                                    <form method="post" onsubmit="return confirm('Remove this book from your library?');">
                                        <input type="hidden" name="book_file" value="<?php echo h($book['file']); ?>">
                                        <button class="remove-button" type="submit" name="delete_book">Remove</button>
                                    </form>
                                <?php } ?>
                            </div>
                        </article>
                    <?php } ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>