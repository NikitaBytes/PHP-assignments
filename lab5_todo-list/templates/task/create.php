<?php
/**
 * Шаблон: Форма создания новой задачи.
 *
 * Отображает форму для ввода данных новой задачи.
 * Включает поля для названия, категории, приоритета, тегов, описания, дедлайна и шагов.
 * При наличии ошибок валидации, отображает их рядом с соответствующими полями
 * и сохраняет введенные пользователем значения (старые значения).
 * Содержит JavaScript для динамического добавления полей для шагов.
 *
 * @var array<string,string>        $errors     Ассоциативный массив ошибок валидации (ключ - имя поля, значение - сообщение).
 * @var array<string,mixed>         $oldValues  Ассоциативный массив со значениями, введенными пользователем ранее (для сохранения при ошибках).
 * @var array<int,array{id:int,name:string}> $categories Список всех доступных категорий (массив ассоциативных массивов ['id', 'name']).
 */
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Создание новой задачи</title>
    <link rel="stylesheet" href="/css/style.css">
    <script>
        /**
         * Добавляет новое поле ввода для шага в контейнер #steps-container.
         * @returns {void}
         */
        function addStep() {
            const container = document.getElementById('steps-container');
            const input = document.createElement('input');
            input.type = 'text';
            input.name = 'steps[]';
            input.placeholder = 'Описание нового шага'; // Улучшенный плейсхолдер
            input.className = 'step-input';
            container.appendChild(input);
            input.focus(); // Фокусируемся на новом поле
        }
    </script>
</head>
<body>
<div class="container">
    <h1>Создание новой задачи</h1>

    <?php if (!empty($errors['database'])): // Отображение общих ошибок БД ?>
        <div class="error global-error"><?= htmlspecialchars($errors['database'], ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <form action="/task/create" method="post">
        <!-- TITLE -->
        <label for="title">Название</label>
        <input
            type="text"
            id="title"
            name="title"
            value="<?= htmlspecialchars($oldValues['title'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
            required> <?php // Добавлено required для базовой валидации браузером ?>
        <?php if (!empty($errors['title'])): ?>
            <div class="error"><?= htmlspecialchars($errors['title'], ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <!-- CATEGORY -->
        <label for="category_id">Категория</label>
        <select name="category_id" id="category_id" required> <?php // Добавлено required ?>
            <option value="">— выберите категорию —</option>
            <?php foreach ($categories as $cat): ?>
                <option
                    value="<?= $cat['id'] ?>"
                    <?= isset($oldValues['category_id']) && (string)$oldValues['category_id'] === (string)$cat['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if (!empty($errors['category_id'])): ?>
            <div class="error"><?= htmlspecialchars($errors['category_id'], ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <!-- PRIORITY -->
        <label for="priority">Приоритет</label>
        <select name="priority" id="priority">
            <?php
            $priorities = ['low'=>'Низкий','medium'=>'Средний','high'=>'Высокий']; // Используем русские названия
            foreach ($priorities as $val=>$label): ?>
                <option
                    value="<?= $val ?>"
                    <?= ($oldValues['priority'] ?? 'medium') === $val ? 'selected' : '' // 'medium' по умолчанию ?>>
                    <?= $label ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if (!empty($errors['priority'])): ?>
            <div class="error"><?= htmlspecialchars($errors['priority'], ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <!-- TAGS -->
        <label for="tags">Теги (через запятую)</label>
        <input
            type="text"
            id="tags"
            name="tags" <?php // Используем name="tags" для строки ?>
            placeholder="например, работа, дом, важно"
            value="<?= htmlspecialchars(implode(', ', (array)($oldValues['tags'] ?? [])), ENT_QUOTES, 'UTF-8') ?>">
        <?php if (!empty($errors['tags'])): ?>
            <div class="error"><?= htmlspecialchars($errors['tags'], ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <!-- DESCRIPTION -->
        <label for="description">Описание</label>
        <textarea
            id="description"
            name="description"
            rows="4"
            placeholder="Подробное описание задачи..."><?= htmlspecialchars($oldValues['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
        <?php if (!empty($errors['description'])): ?>
            <div class="error"><?= htmlspecialchars($errors['description'], ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <!-- DUE DATE -->
        <label for="due_date">Дата выполнения</label>
        <input
            type="date"
            id="due_date"
            name="due_date"
            value="<?= htmlspecialchars($oldValues['due_date'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        <?php if (!empty($errors['due_date'])): ?>
            <div class="error"><?= htmlspecialchars($errors['due_date'], ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <!-- STEPS -->
        <label>Шаги выполнения</label>
        <div id="steps-container">
            <?php
            // Обеспечиваем наличие хотя бы одного пустого поля для шагов
            $steps = (array)($oldValues['steps'] ?? []);
            if (empty($steps)) {
                $steps[] = '';
            }
            foreach ($steps as $step): ?>
                <input
                    type="text"
                    name="steps[]"
                    class="step-input"
                    placeholder="Описание шага"
                    value="<?= htmlspecialchars($step ?? '', ENT_QUOTES, 'UTF-8') ?>">
            <?php endforeach; ?>
        </div>
        <?php if (!empty($errors['steps'])): ?>
            <div class="error"><?= htmlspecialchars($errors['steps'], ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
        <button type="button" class="btn btn-secondary" onclick="addStep()">+ Добавить шаг</button>

        <!-- SUBMIT -->
        <div style="text-align:center; margin-top:20px">
            <button type="submit" class="btn btn-primary">Создать задачу</button>
            <a href="/" class="btn btn-secondary">Отмена</a>
        </div>
    </form>
</div>
</body>
</html>