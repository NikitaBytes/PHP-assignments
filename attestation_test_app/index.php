<?php
declare(strict_types=1);
/**
 * Главная страница.
 *
 * Предоставляет пользователю возможность пройти тест или войти в административную панель.
 *
 * @package TestApp
 */
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Application</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>Welcome to the Test Application</h1>
    <a class="btn" href="test.php">Пройти тест</a>
    <a class="btn" href="admin_login.php">Admin Panel</a>
</div>
</body>
</html>