<?php
/**
 * Шаблон: Форма редактирования задачи.
 *
 * Отображает форму для изменения существующей задачи.
 * Поля формы предзаполняются текущими данными задачи.
 * Включает поля для названия, категории, приоритета, дедлайна, описания, тегов и шагов.
 * Отображает ошибки валидации, если они были переданы из обработчика.
 * Содержит JavaScript для динамического добавления полей для шагов.
 *
 * @var array $task       Ассоциативный массив с текущими данными задачи для предзаполнения формы.
 *                        Ожидаемые ключи: 'id', 'title', 'category_id', 'priority', 'due_date',
 *                        'description', 'tags' (JSON string), 'steps' (JSON string).
 * @var array $categories Список всех доступных категорий (массив ассоциативных массивов ['id', 'name']).
 * @var array $errors     Ассоциативный массив ошибок валидации (ключ - имя поля, значение - сообщение).
 */
?>
<h1>Редактирование задачи</h1>

<?php if (!empty($errors)): ?>
    <div class="errors">
        <p>Пожалуйста, исправьте следующие ошибки:</p>
        <ul>
            <?php foreach ($errors as $field => $message): ?>
                <li><?= htmlspecialchars(ucfirst($field), ENT_QUOTES, 'UTF-8') ?>: <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form action="/task/edit" method="post">
    <input type="hidden" name="id" value="<?= (int)($task['id'] ?? 0) // Приведение к int и значение по умолчанию ?>">

    <label for="title">Название</label>
    <input type="text" id="title" name="title" value="<?= htmlspecialchars($task['title'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
    <?php if (isset($errors['title'])): ?><div class="error"><?= htmlspecialchars($errors['title'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>

    <label for="category_id">Категория</label>
    <select name="category_id" id="category_id">
        <option value="">— без категории —</option>
        <?php foreach ($categories as $c): ?>
            <option value="<?= $c['id'] ?>"
                <?= isset($task['category_id']) && $c['id'] == $task['category_id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($c['name'], ENT_QUOTES, 'UTF-8') ?>
            </option>
        <?php endforeach; ?>
    </select>
    <?php if (isset($errors['category_id'])): ?><div class="error"><?= htmlspecialchars($errors['category_id'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>

    <label for="priority">Приоритет</label>
    <select name="priority" id="priority">
        <?php foreach (['low'=>'LOW','medium'=>'MEDIUM','high'=>'HIGH'] as $val=>$txt): ?>
            <option value="<?= $val ?>"
                <?= isset($task['priority']) && $val === $task['priority'] ? 'selected' : '' ?>><?= $txt ?></option>
        <?php endforeach; ?>
    </select>
    <?php if (isset($errors['priority'])): ?><div class="error"><?= htmlspecialchars($errors['priority'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>

    <label for="due_date">Дедлайн</label>
    <input type="date" id="due_date" name="due_date" value="<?= htmlspecialchars($task['due_date'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
    <?php if (isset($errors['due_date'])): ?><div class="error"><?= htmlspecialchars($errors['due_date'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>

    <label for="description">Описание</label>
    <textarea name="description" id="description" rows="4"><?= htmlspecialchars($task['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
    <?php if (isset($errors['description'])): ?><div class="error"><?= htmlspecialchars($errors['description'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>

    <!-- теги -->
    <label for="tags">Теги (через запятую)</label>
    <?php
        $tagsValue = '';
        if (!empty($task['tags'])) {
            $tagsArray = json_decode($task['tags'], true);
            if (is_array($tagsArray)) {
                $tagsValue = implode(', ', array_map(fn($tag) => htmlspecialchars($tag, ENT_QUOTES, 'UTF-8'), $tagsArray));
            }
        }
    ?>
    <input type="text" id="tags" name="tags" value="<?= $tagsValue // Используем name="tags", а не name="tags[]" для строки ?>">
    <?php if (isset($errors['tags'])): ?><div class="error"><?= htmlspecialchars($errors['tags'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>


    <!-- шаги -->
    <label>Шаги</label>
    <div id="steps">
        <?php
            $stepsArray = [];
            if (!empty($task['steps'])) {
                $decodedSteps = json_decode($task['steps'], true);
                if (is_array($decodedSteps)) {
                    $stepsArray = $decodedSteps;
                }
            }
            // Добавляем хотя бы одно поле, если шагов нет
            if (empty($stepsArray)) {
                 $stepsArray[] = '';
            }
        ?>
        <?php foreach ($stepsArray as $step): ?>
            <input class="step-input" type="text" name="steps[]" value="<?= htmlspecialchars($step, ENT_QUOTES, 'UTF-8') ?>" placeholder="Описание шага">
        <?php endforeach; ?>
        <!-- Поле для добавления нового шага убрано, кнопка добавляет новое поле -->
    </div>
    <?php if (isset($errors['steps'])): ?><div class="error"><?= htmlspecialchars($errors['steps'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
    <button type="button" class="btn btn-secondary" onclick="addStep()">+ Добавить шаг</button>

    <div style="margin-top:20px;text-align:center">
        <button class="btn btn-primary" type="submit">Сохранить изменения</button>
        <a href="/task/show?id=<?= (int)($task['id'] ?? 0) ?>" class="btn btn-secondary">Отмена</a>
    </div>
</form>

<script>
/**
 * Добавляет новое поле ввода для шага в контейнер #steps.
 * @returns {void}
 */
function addStep(){
  const stepsContainer = document.getElementById('steps');
  const newInput = document.createElement('input');
  newInput.className = 'step-input';
  newInput.type = 'text';
  newInput.name = 'steps[]';
  newInput.placeholder = 'Описание нового шага'; // Плейсхолдер для нового поля
  stepsContainer.appendChild(newInput);
  newInput.focus(); // Фокусируемся на новом поле
}
</script>