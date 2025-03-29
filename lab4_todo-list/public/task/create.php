<?php
/**
 * Форма создания новой задачи.
 */

$errors = [];
$oldValues = [];

// Считываем ошибки и старые значения из GET-параметров
if (!empty($_GET['errors'])) {
    $errors = json_decode($_GET['errors'], true);
}
if (!empty($_GET['old'])) {
    $oldValues = json_decode($_GET['old'], true);
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Создать задачу</title>
    <link rel="stylesheet" href="../css/style.css">
    <script>
        /**
         * Добавляет новое поле ввода шага в контейнер шагов в форме создания задачи.
         * 
         * Эта функция создает новый элемент ввода типа 'text', устанавливает его свойства
         * (имя, плейсхолдер, класс) и добавляет его в div-контейнер шагов.
         * Поле ввода используется для ввода отдельных шагов задачи.
         * 
         * @return void
         */

        function addStep() {
            const stepsContainer = document.getElementById('steps-container');
            const newInput = document.createElement('input');
            newInput.type = 'text';
            newInput.name = 'steps[]';
            newInput.placeholder = 'Описание шага';
            newInput.className = 'step-input';
            stepsContainer.appendChild(newInput);
        }
    </script>
</head>
<body>
<div class="container">
    <h1>Создание новой задачи</h1>
    <form action="../../src/handlers/create_task_handler.php" method="POST">
        <!-- Название задачи -->
        <label for="title">Название задачи</label>
        <input 
            type="text" 
            id="title" 
            name="title"
            value="<?= isset($oldValues['title']) ? htmlspecialchars($oldValues['title'], ENT_QUOTES, 'UTF-8') : '' ?>">
        <?php if (!empty($errors['title'])): ?>
            <div class="error"><?= htmlspecialchars($errors['title'], ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <!-- Дедлайн (date) -->
        <label for="due_date">Дедлайн (дата)</label>
        <input 
            type="date"
            id="due_date"
            name="due_date"
            value="<?= isset($oldValues['due_date']) ? htmlspecialchars($oldValues['due_date'], ENT_QUOTES, 'UTF-8') : '' ?>">
        <?php if (!empty($errors['due_date'])): ?>
            <div class="error"><?= htmlspecialchars($errors['due_date'], ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <!-- Приоритет -->
        <label for="priority">Приоритет</label>
        <select name="priority" id="priority">
            <?php
            $priorityOptions = ['Низкий', 'Средний', 'Высокий'];
            $selectedPriority = isset($oldValues['priority']) ? $oldValues['priority'] : '';
            foreach ($priorityOptions as $option):
                $isSelected = ($option === $selectedPriority) ? 'selected' : '';
            ?>
                <option value="<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>" <?= $isSelected ?>>
                    <?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if (!empty($errors['priority'])): ?>
            <div class="error"><?= htmlspecialchars($errors['priority'], ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <!-- Описание задачи -->
        <label for="description">Описание</label>
        <textarea 
            name="description" 
            id="description" 
            rows="4"
        ><?= isset($oldValues['description']) ? htmlspecialchars($oldValues['description'], ENT_QUOTES, 'UTF-8') : '' ?></textarea>
        <?php if (!empty($errors['description'])): ?>
            <div class="error"><?= htmlspecialchars($errors['description'], ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <!-- Тэги (мультивыбор) -->
        <label for="tags">Тэги</label>
        <select name="tags[]" id="tags" multiple>
            <?php
            $availableTags = ['Работа', 'Дом', 'Учёба', 'Отдых', 'Важное'];
            $oldTags = isset($oldValues['tags']) ? (array)$oldValues['tags'] : [];
            foreach ($availableTags as $tag):
                $isSelected = in_array($tag, $oldTags) ? 'selected' : '';
            ?>
                <option value="<?= htmlspecialchars($tag, ENT_QUOTES, 'UTF-8') ?>" <?= $isSelected ?>>
                    <?= htmlspecialchars($tag, ENT_QUOTES, 'UTF-8') ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if (!empty($errors['tags'])): ?>
            <div class="error"><?= htmlspecialchars($errors['tags'], ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <!-- Динамические шаги -->
        <label>Шаги</label>
        <div id="steps-container">
            <?php
            if (!empty($oldValues['steps']) && is_array($oldValues['steps'])) {
                foreach ($oldValues['steps'] as $stepVal) {
                    echo '<input type="text" name="steps[]" class="step-input" 
                          value="' . htmlspecialchars($stepVal, ENT_QUOTES, 'UTF-8') . '" 
                          placeholder="Описание шага">';
                }
            } else {
                echo '<input type="text" name="steps[]" class="step-input" placeholder="Описание шага">';
            }
            ?>
        </div>
        <button type="button" class="btn" onclick="addStep()">Добавить шаг</button>

        <!-- Кнопка отправки формы -->
        <div style="text-align:center; margin-top: 20px;">
            <button type="submit" class="btn">Отправить</button>
        </div>
    </form>
</div>
</body>
</html>