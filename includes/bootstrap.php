<?php
session_start();

require_once __DIR__ . '/config.php';

if (file_exists(__DIR__ . '/config.local.php')) {
    require_once __DIR__ . '/config.local.php';
}

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'quizai_basic';

function h($text)
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

function db()
{
    global $db_host, $db_user, $db_pass, $db_name;

    $connection = new mysqli($db_host, $db_user, $db_pass);

    if ($connection->connect_error) {
        die('Database connection failed. Please start MySQL in XAMPP.');
    }

    $connection->query("CREATE DATABASE IF NOT EXISTS $db_name");
    $connection->select_db($db_name);

    $connection->query("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL
    )");

    $connection->query("CREATE TABLE IF NOT EXISTS quiz_attempts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        topic VARCHAR(100) NOT NULL,
        score INT NOT NULL,
        total INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $check_user = $connection->query("SELECT id FROM users WHERE email = 'student@test.com'");

    if ($check_user->num_rows == 0) {
        $password = password_hash('12345', PASSWORD_DEFAULT);
        $stmt = $connection->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        $name = 'Student';
        $email = 'student@test.com';
        $stmt->bind_param('sss', $name, $email, $password);
        $stmt->execute();
    }

    return $connection;
}

function is_logged_in()
{
    return isset($_SESSION['user_id']);
}

function require_login()
{
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

function make_quiz($topic)
{
    $topic = trim($topic);

    if ($topic == '') {
        $topic = 'General Knowledge';
    }

    return [
        'topic' => $topic,
        'questions' => [
            [
                'question' => 'What is ' . $topic . ' mainly about?',
                'options' => ['Learning about ' . $topic, 'Only playing games', 'Only watching movies', 'Only sleeping'],
                'answer' => 0
            ],
            [
                'question' => 'Which one is useful when studying ' . $topic . '?',
                'options' => ['Practice', 'Guessing always', 'Ignoring notes', 'Closing the book'],
                'answer' => 0
            ],
            [
                'question' => 'What should a beginner do first in ' . $topic . '?',
                'options' => ['Start with basics', 'Skip the basics', 'Avoid examples', 'Never revise'],
                'answer' => 0
            ],
            [
                'question' => 'Why is ' . $topic . ' important?',
                'options' => ['It improves knowledge', 'It wastes all time', 'It has no use', 'It stops learning'],
                'answer' => 0
            ],
            [
                'question' => 'This quiz was generated for which topic?',
                'options' => [$topic, 'Cricket', 'Cooking', 'Travel'],
                'answer' => 0
            ]
        ]
    ];
}

function generate_ai_quiz($topic, &$error_message = '')
{
    $topic = trim($topic);

    if ($topic == '') {
        $topic = 'General Knowledge';
    }

    if (GEMINI_API_KEY == '') {
        $error_message = 'API key not added yet. Showing basic quiz.';
        return make_quiz($topic);
    }

    $prompt = 'Create a beginner-friendly multiple choice quiz about "' . $topic . '". '
        . 'Return only valid JSON in this format: '
        . '{"topic":"topic name","questions":[{"question":"...","options":["a","b","c","d"],"answer":0}]} '
        . 'Rules: exactly 5 questions, exactly 4 options for each question, answer must be 0 to 3, simple English, no markdown, no code block.';

    $payload = [
        'contents' => [
            [
                'parts' => [
                    ['text' => $prompt]
                ]
            ]
        ],
        'generationConfig' => [
            'temperature' => 0.7,
            'responseMimeType' => 'application/json'
        ]
    ];

    $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . GEMINI_MODEL . ':generateContent?key=' . urlencode(GEMINI_API_KEY);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

    $response = curl_exec($ch);

    if ($response === false) {
        $error_message = 'Gemini request failed. Showing basic quiz.';
        curl_close($ch);
        return make_quiz($topic);
    }

    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code < 200 || $http_code >= 300) {
        $error_message = 'Gemini response error. Showing basic quiz.';
        return make_quiz($topic);
    }

    $data = json_decode($response, true);
    $text = gemini_response_text($data);

    if ($text == '') {
        $error_message = 'Gemini text not found. Showing basic quiz.';
        return make_quiz($topic);
    }

    $quiz_data = json_decode($text, true);

    if (!is_array($quiz_data) || !isset($quiz_data['questions'])) {
        $error_message = 'Gemini JSON format was invalid. Showing basic quiz.';
        return make_quiz($topic);
    }

    $quiz = normalize_ai_quiz($quiz_data, $topic);

    if ($quiz === null) {
        $error_message = 'Gemini quiz format was incomplete. Showing basic quiz.';
        return make_quiz($topic);
    }

    return $quiz;
}

function gemini_response_text($data)
{
    if (!is_array($data)) {
        return '';
    }

    if (
        !isset($data['candidates'][0]['content']['parts']) ||
        !is_array($data['candidates'][0]['content']['parts'])
    ) {
        return '';
    }

    foreach ($data['candidates'][0]['content']['parts'] as $part) {
        if (isset($part['text']) && is_string($part['text'])) {
            return trim($part['text']);
        }
    }

    return '';
}

function normalize_ai_quiz($quiz_data, $default_topic)
{
    if (!isset($quiz_data['questions']) || !is_array($quiz_data['questions'])) {
        return null;
    }

    $questions = [];

    foreach ($quiz_data['questions'] as $question) {
        if (
            !isset($question['question']) ||
            !isset($question['options']) ||
            !isset($question['answer']) ||
            !is_array($question['options']) ||
            count($question['options']) != 4
        ) {
            continue;
        }

        $answer = (int) $question['answer'];

        if ($answer < 0 || $answer > 3) {
            continue;
        }

        $questions[] = [
            'question' => trim((string) $question['question']),
            'options' => array_values(array_map('strval', $question['options'])),
            'answer' => $answer
        ];
    }

    if (count($questions) != 5) {
        return null;
    }

    $topic = isset($quiz_data['topic']) ? trim((string) $quiz_data['topic']) : $default_topic;

    if ($topic == '') {
        $topic = $default_topic;
    }

    return [
        'topic' => $topic,
        'questions' => $questions
    ];
}
