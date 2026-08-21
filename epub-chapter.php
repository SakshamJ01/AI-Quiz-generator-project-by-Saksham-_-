<?php
require_once 'includes/bootstrap.php';
require_login();

$requested_file = $_GET['book'] ?? '';
$chapter_number = max(0, (int) ($_GET['chapter'] ?? 0));
$library_data = json_decode(file_get_contents(__DIR__ . '/uploads/library.json'), true);
$book_path = '';

if (is_array($library_data)) {
    foreach ($library_data as $book) {
        if (isset($book['file']) && $book['file'] === $requested_file && strtolower(pathinfo($book['file'], PATHINFO_EXTENSION)) === 'epub') {
            $book_path = __DIR__ . '/' . $book['file'];
            break;
        }
    }
}

if ($book_path == '' || !is_file($book_path) || !function_exists('exec')) {
    http_response_code(404);
    exit('EPUB chapter not found.');
}

$entries = [];
$status = 0;
exec('tar.exe -tf ' . escapeshellarg($book_path) . ' 2>&1', $entries, $status);
$chapters = array_values(array_filter($entries, function ($entry) {
    return preg_match('/\.(xhtml|html|htm)$/i', $entry);
}));

if ($status !== 0 || !isset($chapters[$chapter_number])) {
    http_response_code(404);
    exit('EPUB chapter not found.');
}

$content = [];
$extract_status = 0;
exec('tar.exe -xOf ' . escapeshellarg($book_path) . ' ' . escapeshellarg($chapters[$chapter_number]) . ' 2>&1', $content, $extract_status);

if ($extract_status !== 0) {
    http_response_code(500);
    exit('EPUB chapter could not be read.');
}

header('Content-Type: text/html; charset=UTF-8');
header("Content-Security-Policy: default-src 'none'; style-src 'unsafe-inline'; img-src data:");
$html = implode(PHP_EOL, $content);
$html = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $html);
$reader_styles = '<style>html{background:#f7f4ef}body{max-width:720px;margin:0 auto;padding:58px 54px 90px;background:#fff;color:#29272d;font-family:Georgia,"Times New Roman",serif;font-size:18px;line-height:1.8;box-shadow:0 0 30px rgba(40,35,30,.08)}h1,h2,h3,h4{color:#1e1c22;line-height:1.2;font-family:Arial,Helvetica,sans-serif}h1{font-size:32px;margin:0 0 28px}h2{font-size:25px;margin:38px 0 14px}h3{font-size:20px;margin:30px 0 10px}p{margin:0 0 18px}a{color:#7040a4}img{max-width:100%;height:auto}table{max-width:100%;display:block;overflow:auto}pre{max-width:100%;overflow:auto;padding:14px;background:#f2eee8;font-size:14px}blockquote{margin:24px 0;padding-left:20px;border-left:3px solid #c9a66b;color:#615b53}@media(max-width:600px){body{padding:34px 22px 60px;font-size:17px}h1{font-size:27px}}</style>';
$html = preg_replace('/<head(.*?)>/is', '<head$1>' . $reader_styles, $html, 1);
echo $html;