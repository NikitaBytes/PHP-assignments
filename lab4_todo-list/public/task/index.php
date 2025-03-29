<?php
/**
 * Страница для отображения всех задач (с пагинацией и фильтрами выполнения) с AJAX-обновлением статуса.
 */

require_once __DIR__ . '/../../src/helpers.php';

$storageFile = __DIR__ . '/../../storage/tasks.txt';
$tasks = readTasksFromStorage($storageFile);

// Получаем фильтр из GET: all, completed, incomplete
$filter = $_GET['filter'] ?? 'all';

// Фильтруем задачи по состоянию выполнения
if ($filter === 'completed') {
    $tasks = array_filter($tasks, fn($task) => !empty($task['completed']));
} elseif ($filter === 'incomplete') {
    $tasks = array_filter($tasks, fn($task) => empty($task['completed']));
}
$tasks = array_values($tasks); // переиндексация массива

// Параметры пагинации
$tasksPerPage = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) {
    $page = 1;
}

$totalTasks = count($tasks);
$totalPages = ceil($totalTasks / $tasksPerPage);
$offset = ($page - 1) * $tasksPerPage;
$currentTasks = array_slice($tasks, $offset, $tasksPerPage);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Список всех задач</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="container">
    <h1>Список всех задач</h1>
    
    <!-- Фильтры задач -->
    <div style="margin-bottom: 20px; text-align: center;">
        <a class="btn3" href="?filter=all">Все</a>
        <a class="btn4" href="?filter=completed">Выполненные</a>
        <a class="btn5" href="?filter=incomplete">Невыполненные</a>
    </div>

    <p>
        <a class="btn" href="../index.php">На главную</a> 
        <a class="btn" href="create.php">Создать задачу</a>
    </p>

    <?php if (empty($currentTasks)): ?>
        <p>Нет задач для отображения.</p>
    <?php else: ?>
        <ul class="task-list">
            <?php foreach ($currentTasks as $task): 
                // Определяем CSS-класс для приоритета
                $priorityClass = match ($task['priority'] ?? '') {
                    'Низкий'  => 'priority-low',
                    'Средний' => 'priority-medium',
                    'Высокий' => 'priority-high',
                    default   => 'priority-low'
                };
                // Получаем информацию по дедлайну
                $deadlineInfo = getDeadlineInfo($task['due_date'] ?? '');
                // Получаем ID с проверкой на существование
                $taskId = isset($task['id']) ? htmlspecialchars($task['id'], ENT_QUOTES, 'UTF-8') : '';
                $isCompleted = !empty($task['completed']);
            ?>
                <li class="task-item" style="<?= $isCompleted ? 'opacity:0.8;' : '' ?>">
                    <div class="task-title">
                        <?= htmlspecialchars($task['title'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                        <?php if ($isCompleted): ?>
                            <span style="color:green; margin-left:10px;">✔ Выполнено</span>
                        <?php endif; ?>
                    </div>

                    <div class="task-meta">
                        <div class="priority-label <?= $priorityClass ?>">
                            <?= htmlspecialchars($task['priority'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                        </div>
                        <div>
                            Дедлайн: <?= htmlspecialchars($task['due_date'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                            <span class="deadline-label <?= $deadlineInfo['class'] ?>">
                                <?= $deadlineInfo['message'] ?>
                            </span>
                        </div>
                    </div>

                    <?php if (!empty($task['description'])): ?>
                        <p class="task-description">
                            <?= nl2br(htmlspecialchars($task['description'] ?? '', ENT_QUOTES, 'UTF-8')) ?>
                        </p>
                    <?php endif; ?>

                    <?php if (!empty($task['tags'])): ?>
                        <p class="tags"><em>Тэги: <?= implode(', ', array_map('htmlspecialchars', $task['tags'])) ?></em></p>
                    <?php endif; ?>

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

                    <!-- Кнопки управления задачей с AJAX -->
                    <div style="margin-top:10px;">
                        <?php if ($taskId !== ''): ?>
                        <form class="update-form" data-taskid="<?= $taskId ?>" action="../../src/handlers/update_task_handler.php" method="POST" style="display:inline;">
                            <input type="hidden" name="id" value="<?= $taskId ?>">
                            <?php if ($isCompleted): ?>
                                <button class="btn" type="submit">Отменить выполнение</button>
                            <?php else: ?>
                                <button class="btn" type="submit">Выполнить</button>
                            <?php endif; ?>
                        </form>
                        <form action="../../src/handlers/delete_task_handler.php" method="POST" style="display:inline;">
                            <input type="hidden" name="id" value="<?= $taskId ?>">
                            <button class="btn" type="submit" style="background-color:#ff3b30;">Удалить</button>
                        </form>
                        <?php else: ?>
                            <p style="color:red;">Ошибка: задача не содержит ID.</p>
                        <?php endif; ?>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>

        <!-- Пагинация -->
        <?php if ($totalPages > 1): ?>
            <div style="text-align:center; margin-top:20px;">
                <?php for ($i = 1; $i <= $totalPages; $i++): 
                    $link = '?filter=' . $filter . '&page=' . $i;
                ?>
                    <?php if ($i == $page): ?>
                        <strong><?= $i ?></strong>
                    <?php else: ?>
                        <a class="btn" href="<?= $link ?>"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const updateForms = document.querySelectorAll('.update-form');
    updateForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault(); // Отменяем стандартную отправку формы
            const formData = new FormData(form);
            const actionUrl = form.getAttribute('action');
            
            fetch(actionUrl, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const button = form.querySelector('button');
                    // Переключаем текст кнопки в зависимости от статуса
                    if (button.textContent.trim() === "Выполнить") {
                        button.textContent = "Отменить выполнение";
                    } else {
                        button.textContent = "Выполнить";
                    }
                } else {
                    alert("Ошибка: " + data.error);
                }
            })
            .catch(error => {
                console.error("Ошибка запроса:", error);
                alert("Ошибка выполнения запроса");
            });
        });
    });
});
</script>
</body>
</html>