<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (isset($_GET['lang']) && in_array($_GET['lang'], ['ar', 'en'])) {
    $_SESSION['lang'] = $_GET['lang'];
}


$current_lang = $_SESSION['lang'] ?? 'en';


require_once __DIR__ . '/../assets/lang/' . $current_lang . '.php';
?>