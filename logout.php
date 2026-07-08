<?php
require_once __DIR__ . '/includes/bootstrap.php';
quizai_logout_user();
header('Location: index.php');
exit;
