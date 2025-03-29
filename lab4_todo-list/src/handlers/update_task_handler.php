<?php
/**
 * Обработчик переключения статуса задачи ("Выполнено"/"Не выполнено") с использованием AJAX.
 *
 * Возвращает JSON-ответ с информацией об успехе или ошибке.
 *
 * @return void
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../helpers.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $taskId = $_POST['id'] ?? null;
    if (!$taskId) {
        echo json_encode(['success' => false, 'error' => 'Отсутствует ID задачи']);
        exit;
    }

    $storageFile = __DIR__ . '/../../storage/tasks.txt';
    $tasks = readTasksFromStorage($storageFile);
    $found = false;
    foreach ($tasks as &$task) {
        if (isset($task['id']) && $task['id'] === $taskId) {
            $task['completed'] = !$task['completed'];
            $found = true;
            break;
        }
    }
    unset($task);

    if (!$found) {
        echo json_encode(['success' => false, 'error' => 'Задача не найдена']);
        exit;
    }

    rewriteTasksInStorage($storageFile, $tasks);
    echo json_encode(['success' => true, 'taskId' => $taskId]);
    exit;
} else {
    echo json_encode(['success' => false, 'error' => 'Неверный метод запроса']);
    exit;
}