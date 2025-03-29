<?php
/**
 * Обработчик удаления задачи.
 */

require_once __DIR__ . '/../helpers.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $taskId = $_POST['id'] ?? null;
    if (!$taskId) {
        header('Location: ../../public/task/index.php');
        exit;
    }

    $storageFile = __DIR__ . '/../../storage/tasks.txt';
    $tasks = readTasksFromStorage($storageFile);

    // Убираем из массива задачу с данным ID
    $updatedTasks = array_filter($tasks, fn($task) => $task['id'] !== $taskId);

    rewriteTasksInStorage($storageFile, array_values($updatedTasks));

    header('Location: ../../public/task/index.php');
    exit;
} else {
    header('Location: ../../public/task/index.php');
    exit;
}