<?php
require_once 'includes/bootstrap.php';
require_login();

$requested_file = $_GET['book'] ?? '';
$library_file = __DIR__ . '/uploads/library.json';
$library_data = json_decode(file_get_contents($library_file), true);

$books = [];

if (is_array($library_data)) {
    foreach ($library_data as $book) {
        if (isset($book['title'], $book['file'])) {
            $books[] = ['title' => $book['title'], 'file' => $book['file']];
        }
    }
}

$selected_book = null;
foreach ($books as $book) {
    if (hash_equals($book['file'], $requested_file)) {
        $selected_book = $book;
        break;
    }
}

$extension = $selected_book ? strtolower(pathinfo($selected_book['file'], PATHINFO_EXTENSION)) : '';
$book_path = $selected_book ? __DIR__ . '/' . $selected_book['file'] : '';
$epub_chapters = [];

if ($extension == 'epub' && function_exists('exec')) {
    $archive_entries = [];
    $archive_status = 0;
    exec('tar.exe -tf ' . escapeshellarg($book_path) . ' 2>&1', $archive_entries, $archive_status);

    if ($archive_status === 0) {
        foreach ($archive_entries as $entry) {
            if (preg_match('/\.(xhtml|html|htm)$/i', $entry)) {
                $epub_chapters[] = $entry;
            }
        }
    }
}

if (!$selected_book || !is_file($book_path) || !in_array($extension, ['pdf', 'epub', 'html'], true)) {
    http_response_code(404);
    exit('Book not found.');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo h($selected_book['title']); ?> - Quiz AI</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="reader-page">
    <header class="reader-header">
        <a class="quiet-link" href="books.php">&larr; Back to library</a>
        <h1><?php echo h($selected_book['title']); ?></h1>
        <span class="reader-format"><?php echo strtoupper(h($extension)); ?></span>
    </header>

    <main class="reader-shell">
        <?php if ($extension == 'epub') { ?>
            <div class="reader-toolbar">
                <?php $chapter_number = max(0, min((int) ($_GET['chapter'] ?? 0), count($epub_chapters) - 1)); ?>
                <?php if ($chapter_number > 0) { ?><a class="reader-button" href="reader.php?book=<?php echo rawurlencode($selected_book['file']); ?>&chapter=<?php echo $chapter_number - 1; ?>">&larr; Previous</a><?php } else { ?><span></span><?php } ?>
                <span class="reader-progress">Chapter <?php echo $chapter_number + 1; ?> <i>of</i> <?php echo count($epub_chapters); ?></span>
                <?php if ($chapter_number < count($epub_chapters) - 1) { ?><a class="reader-button" href="reader.php?book=<?php echo rawurlencode($selected_book['file']); ?>&chapter=<?php echo $chapter_number + 1; ?>">Next &rarr;</a><?php } else { ?><span></span><?php } ?>
            </div>
            <iframe class="epub-reader" src="epub-chapter.php?book=<?php echo rawurlencode($selected_book['file']); ?>&chapter=<?php echo $chapter_number; ?>" title="<?php echo h($selected_book['title']); ?>"></iframe>
        <?php } elseif ($extension == 'pdf') { ?>
            <iframe class="pdf-reader" src="<?php echo h($selected_book['file']); ?>" title="<?php echo h($selected_book['title']); ?>"></iframe>
        <?php } else { ?>
            <iframe class="html-reader" src="<?php echo h($selected_book['file']); ?>" title="<?php echo h($selected_book['title']); ?>"></iframe>
        <?php } ?>
    </main>
</body>
</html>