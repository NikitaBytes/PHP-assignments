<?php
declare(strict_types=1);

/**
 * Страница авторизации администратора.
 *
 * Доступ в административную панель осуществляется по статическим учетным данным.
 * При успешной авторизации устанавливается сессионная переменная 'admin'.
 *
 * @package TestApp\Admin
 */

session_start();

// Определяем статические учетные данные для входа в админ-панель
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', '123456');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем и фильтруем входные данные
    $username = trim(filter_input(INPUT_POST, 'username', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
    $password = trim(filter_input(INPUT_POST, 'password', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');

    // Проверяем корректность введенных данных
    if ($username === ADMIN_USERNAME && $password === ADMIN_PASSWORD) {
        $_SESSION['admin'] = true;
        header('Location: dashboard.php');
        exit;
    } else {
        $error = "Неверные логин или пароль!";
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Вход в админ-панель</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>Вход в админ-панель</h1>
    <?php if (!empty($error)): ?>
        <div class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
    <form method="POST" action="admin_login.php">
        <div class="form-group">
            <label for="username">Логин:</label>
            <input type="text" id="username" name="username" required autofocus>
        </div>
        <div class="form-group">
            <label for="password">Пароль:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button class="btn" type="submit">Войти</button>
    </form>
    <a class="btn" href="index.php">На главную</a>
</div>
</body>
</html>