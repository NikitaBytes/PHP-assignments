<?php
/**
 * Шаблон: Отображение детальной информации о задаче.
 *
 * Этот шаблон выводит все детали конкретной задачи, включая заголовок,
 * метаданные (категория, приоритет, дедлайн), описание, теги и шаги.
 * Также предоставляет кнопки для редактирования и удаления задачи.
 *
 * @var array $task Ассоциативный массив с данными задачи. Ожидаемые ключи:
 *                  'id' (int), 'title' (string), 'category_name' (string|null),
 *                  'priority' (string), 'due_date' (string|null),
 *                  'description' (string|null), 'tags' (array), 'steps' (array).
 * @uses getDeadlineInfo() Вспомогательная функция для получения информации о дедлайне.
 */
?>
<div class="container">
    <h1><?= htmlspecialchars($task['title'], ENT_QUOTES, 'UTF-8') ?></h1>

    <div class="task-meta">
        <!-- Категория -->
        <span class="category-label">
            <?= htmlspecialchars($task['category_name'] ?? 'Без категории', ENT_QUOTES, 'UTF-8') ?>
        </span>
        <!-- Приоритет -->
        <span class="priority-label priority-<?= htmlspecialchars($task['priority'], ENT_QUOTES, 'UTF-8') ?>">
            <?= strtoupper(htmlspecialchars($task['priority'], ENT_QUOTES, 'UTF-8')) ?>
        </span>
        <!-- Дедлайн -->
        <?php $dl = getDeadlineInfo($task['due_date'] ?? ''); ?>
        <span>
            Дедлайн: <?= htmlspecialchars($task['due_date'] ?? '-', ENT_QUOTES, 'UTF-8') ?>
            <span class="deadline-label <?= $dl['class'] ?>"><?= $dl['message'] ?></span>
        </span>
    </div>

    <?php if (!empty($task['description'])): ?>
        <p class="task-description">
            <?= nl2br(htmlspecialchars($task['description'], ENT_QUOTES, 'UTF-8')) ?>
        </p>
    <?php endif; ?>

    <?php if ($task['tags']): ?>
        <p class="tags"><strong>Теги:</strong>
            <?= implode(', ', array_map('htmlspecialchars', $task['tags'])) ?>
        </p>
    <?php endif; ?>

    <?php if ($task['steps']): ?>
        <div class="task-steps">
            <p><strong>Шаги:</strong></p>
            <ol>
                <?php foreach ($task['steps'] as $step): ?>
                    <li><?= htmlspecialchars($step, ENT_QUOTES, 'UTF-8') ?></li>
                <?php endforeach; ?>
            </ol>
        </div>
    <?php endif; ?>

    <div class="actions" style="margin-top:20px">
        <a class="btn btn-info" href="/task/edit?id=<?= $task['id'] ?>">✏ Edit</a>
        <form action="/task/delete" method="post" style="display:inline">
            <input type="hidden" name="id" value="<?= $task['id'] ?>">
            <button class="btn btn-danger" onclick="return confirm('Удалить?')">🗑 Delete</button>
        </form>
    </div>
</div>