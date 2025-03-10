<?php
declare(strict_types=1);

/**
 * Административная панель.
 *
 * Отображает агрегированные результаты тестов в виде таблицы с возможностью экспорта в PDF.
 * Доступ к странице разрешён только авторизованным пользователям.
 *
 * @package TestApp\Admin
 */

session_start();

if (empty($_SESSION['admin'])) {
    header('Location: admin_login.php');
    exit;
}

require_once 'includes/DataManager.php';

$dataManager = new DataManager();
$results = $dataManager->loadResults();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Админ панель - Результаты тестов</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>Результаты тестов</h1>
    <a class="btn" href="export_pdf.php" target="_blank">Экспорт в PDF</a>
    <a class="btn" href="logout.php">Выход</a>
    <table>
        <thead>
            <tr>
                <th>Имя пользователя</th>
                <th>Правильных ответов</th>
                <th>Процент</th>
                <th>Дата</th>
            </tr>
        </thead>
        <tbody>
        <?php if (!empty($results)): ?>
            <?php foreach ($results as $result): ?>
                <tr>
                    <td><?= htmlspecialchars($result['username'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= sprintf('%d / %d', $result['correct_answers'], $result['total']) ?></td>
                    <td><?= htmlspecialchars((string)$result['score'], ENT_QUOTES, 'UTF-8') ?>%</td>
                    <td><?= htmlspecialchars($result['date'], ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="4">Нет данных</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>