<?php
/**
 * Вспомогательные функции для проекта ToDoList.
 */

/**
 * Читает все задачи из файла-хранилища в виде массива.
 *
 * @param string $filePath
 * @return array
 */
function readTasksFromStorage(string $filePath): array {
    if (!file_exists($filePath)) {
        return [];
    }
    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $tasks = [];
    foreach ($lines as $line) {
        $decoded = json_decode($line, true);
        if (is_array($decoded)) {
            $tasks[] = $decoded;
        }
    }
    return $tasks;
}

/**
 * Сохраняет одну задачу в конец файла (JSON + перевод строки).
 *
 * @param string $filePath
 * @param array $taskData
 */
function saveTaskToStorage(string $filePath, array $taskData): void {
    $json = json_encode($taskData, JSON_UNESCAPED_UNICODE);
    file_put_contents($filePath, $json . PHP_EOL, FILE_APPEND);
}

/**
 * Полностью перезаписывает файл, полезно при удалении/обновлении.
 *
 * @param string $filePath
 * @param array $tasks
 */
function rewriteTasksInStorage(string $filePath, array $tasks): void {
    file_put_contents($filePath, ''); // очищаем
    foreach ($tasks as $task) {
        $json = json_encode($task, JSON_UNESCAPED_UNICODE);
        file_put_contents($filePath, $json . PHP_EOL, FILE_APPEND);
    }
}

/**
 * Возвращает информацию по дедлайну (сколько осталось дней или просрочено).
 *
 * @param string $dueDate
 * @return array ['message' => string, 'class' => string]
 */
function getDeadlineInfo(string $dueDate): array {
    if (empty($dueDate)) {
        return ['message' => 'Дата не указана', 'class' => 'deadline-none'];
    }
    $today = new DateTime();
    $deadline = DateTime::createFromFormat('Y-m-d', $dueDate);
    if (!$deadline) {
        return ['message' => 'Дата недействительна', 'class' => 'deadline-none'];
    }
    $diff = $today->diff($deadline);
    $days = (int)$diff->format('%r%a');

    if ($days < 0) {
        $daysOverdue = abs($days);
        return ['message' => "Просрочено на {$daysOverdue} дн.", 'class' => 'deadline-overdue'];
    } elseif ($days === 0) {
        return ['message' => 'Дедлайн сегодня!', 'class' => 'deadline-critical'];
    } else {
        if ($days <= 5) {
            return ['message' => "Осталось {$days} дн.", 'class' => 'deadline-critical'];
        } else {
            return ['message' => "Осталось {$days} дн.", 'class' => 'deadline-normal'];
        }
    }
}