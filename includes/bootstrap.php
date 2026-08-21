<?php
session_start();

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

    $connection->query("CREATE TABLE IF NOT EXISTS quiz_questions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        attempt_id INT NOT NULL,
        question_order INT NOT NULL,
        question_text TEXT NOT NULL,
        options_json TEXT NOT NULL,
        correct_answer INT NOT NULL,
        explanation TEXT NOT NULL,
        INDEX (attempt_id)
    )");

    $connection->query("CREATE TABLE IF NOT EXISTS quiz_answers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        attempt_id INT NOT NULL,
        question_id INT NOT NULL,
        selected_answer INT NOT NULL,
        is_correct TINYINT(1) NOT NULL,
        INDEX (attempt_id),
        INDEX (question_id)
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

function generate_ai_quiz($topic, $difficulty = 'beginner', $question_count = 5, &$error_message = '')
{
    $topic = trim($topic);

    $allowed_difficulties = ['beginner', 'intermediate', 'advanced'];
    $allowed_question_counts = [5, 10, 15];

    if (!in_array($difficulty, $allowed_difficulties, true)) {
        $difficulty = 'beginner';
    }

    $question_count = (int) $question_count;

    if (!in_array($question_count, $allowed_question_counts, true)) {
        $question_count = 5;
    }

    if ($topic == '') {
        $topic = 'General Knowledge';
    }

    if (!defined('GEMINI_API_KEY') || GEMINI_API_KEY == '') {
        $error_message = 'API key not added yet.';
        return null;
    }

    $prompt = 'Create a ' . $difficulty . '-level multiple choice quiz about "' . $topic . '". '
        . 'Return only valid JSON in this format: '
        . '{"topic":"topic name","questions":[{"question":"...","options":["a","b","c","d"],"answer":0,"explanation":"Why the correct option is right."}]} '
        . 'Rules: exactly ' . $question_count . ' unique questions, exactly 4 unique options for each question, '
        . 'answer must be an integer from 0 to 3, every answer must match the correct option, '
        . 'questions must be specifically about the requested topic and appropriate for the difficulty, '
        . 'avoid ambiguous questions and repeated wording, no markdown, no code block.';

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

    $model = defined('GEMINI_MODEL') ? GEMINI_MODEL : 'gemini-2.5-flash';
    $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . $model . ':generateContent?key=' . urlencode(GEMINI_API_KEY);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

    $response = curl_exec($ch);

    if ($response === false) {
        $error_message = 'Gemini request failed.';
        curl_close($ch);
        return null;
    }

    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code < 200 || $http_code >= 300) {
        $error_message = 'Gemini response error.';
        return null;
    }

    $data = json_decode($response, true);
    $text = gemini_response_text($data);

    if ($text == '') {
        $error_message = 'Gemini text not found.';
        return null;
    }

    $quiz_data = json_decode($text, true);

    if (!is_array($quiz_data) || !isset($quiz_data['questions'])) {
        $error_message = 'Gemini JSON format was invalid.';
        return null;
    }

    $quiz = normalize_ai_quiz($quiz_data, $topic, $question_count);

    if ($quiz === null) {
        $error_message = 'Gemini quiz format was incomplete.';
        return null;
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

function normalize_ai_quiz($quiz_data, $default_topic, $question_count = 5)
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

        $question_text = trim((string) $question['question']);
        $options = array_values(array_map('trim', array_map('strval', $question['options'])));
        $explanation = isset($question['explanation']) ? trim((string) $question['explanation']) : 'This is the correct answer for the question.';

        if ($question_text == '' || $explanation == '' || count(array_unique(array_map('strtolower', $options))) != 4 || in_array('', $options, true)) {
            continue;
        }

        $answer = (int) $question['answer'];

        if ($answer < 0 || $answer > 3) {
            continue;
        }

        $questions[] = [
            'question' => $question_text,
            'options' => $options,
            'answer' => $answer,
            'explanation' => $explanation
        ];
    }

    $question_keys = array_map(function ($item) {
        return strtolower($item['question']);
    }, $questions);

    if (count($questions) != $question_count || count(array_unique($question_keys)) != $question_count) {
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
