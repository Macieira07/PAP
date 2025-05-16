<?php
if (session_status() === PHP_SESSION_NONE) {
    $secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
    $domain = $_SERVER['HTTP_HOST'];

    if (version_compare(PHP_VERSION, '7.3.0', '>=')) {
        session_set_cookie_params([
            'lifetime' => 86400,
            'path' => '/',
            'domain' => $domain,
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
    } else {
        session_set_cookie_params(
            86400,
            "/; samesite=Strict",
            $domain,
            $secure,
            true
        );
    }
    session_start();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Debug para ver o token
header('Content-Type: application/json');
echo json_encode([
    'csrf_token_session' => $_SESSION['csrf_token'],
    'request_method' => $_SERVER['REQUEST_METHOD'],
    'cookies' => $_COOKIE,
]);
exit;
