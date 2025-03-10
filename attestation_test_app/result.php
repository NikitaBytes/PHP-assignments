<?php
declare(strict_types=1);

/**
 * Страница отображения индивидуального результата теста.
 *
 * Выводит результат теста, сохранённый в сессии. Если результат отсутствует,
 * перенаправляет пользователя на главную страницу.
 *
 * @package TestApp\Result
 */

session_start();

if (!isset($_SESSION['result'])) {
    header('Location: index.php');
    exit;
}

$result = $_SESSION['result'];
unset($_SESSION['result']);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Результаты теста</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>Ваш результат</h1>
    <p>Имя: <?= htmlspecialchars($result['username'], ENT_QUOTES, 'UTF-8') ?></p>
    <p>Правильных ответов: <?= (int)$result['correct_answers'] ?> из <?= (int)$result['total'] ?></p>
    <p>Процент: <?= htmlspecialchars((string)$result['score'], ENT_QUOTES, 'UTF-8') ?>%</p>
    <a class="btn" href="index.php">На главную</a>
</div>
</body>
</html>