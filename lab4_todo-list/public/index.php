<?php
/**
 * public/index.php
 * Главная страница приложения ToDoList: показывает две последние задачи и ссылки на другие страницы.
 */

require_once __DIR__ . '/../src/helpers.php';

// Считываем все задачи из хранилища
$storageFile = __DIR__ . '/../storage/tasks.txt';
$tasks = readTasksFromStorage($storageFile);

// Берём последние две задачи (если их меньше двух — берём столько, сколько есть)
$latestTasks = array_slice($tasks, -2);

?><!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>ToDoList - Главная</title>
    <!-- Предполагается, что style.css лежит в public/css/style.css -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container">
    <h1>Добро пожаловать в ToDoList</h1>
    <p>
        <!-- Ссылки на другие страницы (лежащие в public/task/) -->
        <a class="btn" href="task/create.php">Создать задачу</a>
        <a class="btn" href="task/index.php">Список всех задач</a>
    </p>

    <h2>Последние задачи</h2>
    <?php if (empty($latestTasks)): ?>
        <p>Пока нет ни одной задачи.</p>
    <?php else: ?>
        <ul class="task-list">
            <?php foreach ($latestTasks as $task):
                // Определяем CSS-класс для приоритета
                $priorityClass = match ($task['priority'] ?? '') {
                    'Низкий'  => 'priority-low',
                    'Средний' => 'priority-medium',
                    'Высокий' => 'priority-high',
                    default   => 'priority-low'
                };

                // Получаем информацию по дедлайну (см. функцию getDeadlineInfo в helpers.php)
                $deadlineInfo = getDeadlineInfo($task['due_date'] ?? '');
            ?>
            <li class="task-item">
                <!-- Капсула приоритета -->
                <div class="priority-label <?= $priorityClass ?>">
                    <?= htmlspecialchars($task['priority'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                </div>

                <!-- Заголовок задачи -->
                <div class="task-title">
                    <?= htmlspecialchars($task['title'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                </div>

                <!-- Дедлайн и его статус -->
                <div>
                    Дедлайн: <?= htmlspecialchars($task['due_date'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                    <span class="deadline-label <?= $deadlineInfo['class'] ?>">
                        <?= $deadlineInfo['message'] ?>
                    </span>
                </div>

                <!-- Описание задачи -->
                <?php if (!empty($task['description'])): ?>
                    <p class="task-description">
                        <?= nl2br(htmlspecialchars($task['description'], ENT_QUOTES, 'UTF-8')) ?>
                    </p>
                <?php endif; ?>

                <!-- Тэги -->
                <?php if (!empty($task['tags'])): ?>
                    <p class="tags">
                        <em>Тэги: <?= implode(', ', array_map('htmlspecialchars', $task['tags'])) ?></em>
                    </p>
                <?php endif; ?>

                <!-- Шаги задачи -->
                <?php if (!empty($task['steps'])): ?>
                    <div class="task-steps">
                        <p><strong>Шаги:</strong></p>
                        <ul>
                            <?php foreach ($task['steps'] as $step): ?>
                                <li><?= htmlspecialchars($step, ENT_QUOTES, 'UTF-8') ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>
</body>
</html>