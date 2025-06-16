<?php
session_start();
header('Content-Type: application/json');

if (isset($_POST['lang'])) {
    $lang = $_POST['lang'];
    // Validar se o idioma é suportado
    $supported_languages = ['pt', 'en', 'fr', 'es'];
    if (in_array($lang, $supported_languages)) {
        $_SESSION['lang'] = $lang;
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Unsupported language']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'No language specified']);
} 