<?php
/**
 * Обработчик формы создания новой задачи.
 */

require_once __DIR__ . '/../helpers.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Массив правил для валидации/фильтрации
    $filters = [
        'title' => [
            'filter' => FILTER_SANITIZE_STRING,
        ],
        'due_date' => [
            'filter' => FILTER_SANITIZE_STRING,
        ],
        'priority' => [
            'filter' => FILTER_SANITIZE_STRING,
        ],
        'description' => [
            'filter' => FILTER_SANITIZE_STRING,
        ]
        // Массивы (tags, steps) будем обрабатывать отдельно
    ];

    // Фильтруем основные поля
    $filteredInput = filter_input_array(INPUT_POST, $filters);

    // Обрабатываем многострочные поля (tags[], steps[])
    // Для тегов — тоже может потребоваться фильтрация на каждый элемент
    $tags = isset($_POST['tags']) ? (array)$_POST['tags'] : [];
    $filteredTags = array_map('strip_tags', $tags);

    // Шаги: массив строк
    $steps = isset($_POST['steps']) ? (array)$_POST['steps'] : [];
    $filteredSteps = array_map('strip_tags', $steps);

    // Валидация
    $errors = [];

    // Проверка: название задачи (обязательно)
    if (empty($filteredInput['title'])) {
        $errors['title'] = 'Пожалуйста, введите название задачи.';
    }

    // Проверка: дедлайн (опционально проверим формат даты, упростим проверку)
    if (!empty($filteredInput['due_date']) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $filteredInput['due_date'])) {
        $errors['due_date'] = 'Неверный формат даты. Используйте ГГГГ-ММ-ДД.';
    }

    // Проверка: приоритет (обязательно, выберите из списка)
    $validPriorities = ['Низкий', 'Средний', 'Высокий'];
    if (!in_array($filteredInput['priority'], $validPriorities)) {
        $errors['priority'] = 'Выберите корректный приоритет.';
    }

    // Подготовим "старые" значения (для возврата в форму при ошибках)
    $oldValues = [
        'title' => $filteredInput['title'],
        'due_date' => $filteredInput['due_date'],
        'priority' => $filteredInput['priority'],
        'description' => $filteredInput['description'],
        'tags' => $filteredTags,
        'steps' => $filteredSteps
    ];

    // Если есть ошибки, перенаправим обратно
    if (!empty($errors)) {
        // Кодируем $errors и $oldValues в JSON и передаём через GET-параметры
        $errorsEncoded = urlencode(json_encode($errors));
        $oldEncoded = urlencode(json_encode($oldValues));
        header("Location: /public/task/create.php?errors={$errorsEncoded}&old={$oldEncoded}");
        exit;
    }

    // Если ошибок нет, формируем задачу
    $taskData = [
        'id' => uniqid(), // Добавляем уникальный ID
        'title' => $filteredInput['title'],
        'due_date' => $filteredInput['due_date'],
        'priority' => $filteredInput['priority'],
        'description' => $filteredInput['description'],
        'tags' => $filteredTags,
        'steps' => $filteredSteps,
        'completed' => false // Явно устанавливаем начальное значение "не выполнено"
    ];

    // Сохраняем задачу в файл
    $storageFile = __DIR__ . '/../../storage/tasks.txt';
    saveTaskToStorage($storageFile, $taskData);

    // Перенаправляем на главную
    header('Location: /public/index.php');
    exit;
} else {
    // Если пришли не через POST, то просто отправим пользователя на форму
    header('Location: /public/task/create.php');
    exit;
}