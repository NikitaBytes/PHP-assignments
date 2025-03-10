<?php
declare(strict_types=1);

/**
 * Скрипт выхода из административной панели.
 *
 * Завершает сессию пользователя и перенаправляет его на страницу авторизации.
 *
 * @package TestApp\Auth
 */

session_start();

// Удаляем все переменные сессии
$_SESSION = [];

// Если используются cookie для сессии, удаляем их
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Завершаем сессию
session_destroy();

// Перенаправляем пользователя на страницу авторизации
header('Location: admin_login.php');
exit;