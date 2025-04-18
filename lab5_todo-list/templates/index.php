<?php
/**
 * Шаблон: главная страница — последние две задачи.
 *
 * Этот шаблон отображает приветствие и список из двух последних задач.
 * Если задач нет, выводится соответствующее сообщение.
 * Для каждой задачи отображается название, приоритет, дедлайн, описание, теги и шаги.
 *
 * @var array $tasks Массив последних задач (максимум 2).
 *                    Каждая задача представлена в виде ассоциативного массива
 *                    с ключами: 'id', 'title', 'priority', 'due_date',
 *                    'description', 'tags', 'steps'.
 */
?>
<h1>Добро пожаловать в ToDoList</h1>

<p style="text-align:center; margin-bottom:20px;">
    <a class="btn" href="/task/create">Создать задачу</a>
    <a class="btn" href="/task/index">Все задачи</a>
</p>

<h2>Последние задачи</h2>

<?php if (empty($tasks)): ?>
    <p style="text-align:center">Пока нет ни одной задачи.</p>
<?php else: ?>
    <ul class="task-list">
        <?php foreach ($tasks as $t):
            // класс приоритета
            $prioClass = match ($t['priority']) {
                'high'   => 'priority-high',
                'medium' => 'priority-medium',
                default  => 'priority-low'
            };

            // дедлайн
            $due = $t['due_date'] ?: null;
            if (!$due) {
                $dlMsg = 'Дата не указана';
                $dlClass = 'deadline-none';
            } else {
                $now = new DateTime();
                $d   = new DateTime($due);
                $diff = (int)$now->diff($d)->format('%r%a');
                if ($diff < 0) {
                    $dlMsg   = "Просрочено на " . abs($diff) . " дн.";
                    $dlClass = 'deadline-overdue';
                } elseif ($diff === 0) {
                    $dlMsg   = 'Дедлайн сегодня!';
                    $dlClass = 'deadline-critical';
                } elseif ($diff <= 5) {
                    $dlMsg   = "Осталось {$diff} дн.";
                    $dlClass = 'deadline-critical';
                } else {
                    $dlMsg   = "Осталось {$diff} дн.";
                    $dlClass = 'deadline-normal';
                }
            }
        ?>
        <li class="task-item">
            <div class="task-title">
                <?= htmlspecialchars($t['title'], ENT_QUOTES) ?>
            </div>

            <div class="task-meta">
                <span class="priority-label <?= $prioClass ?>">
                    <?= htmlspecialchars($t['priority'], ENT_QUOTES) ?>
                </span>
                <span>
                    Дедлайн: <?= htmlspecialchars($t['due_date'] ?: '—', ENT_QUOTES) ?>
                    <span class="deadline-label <?= $dlClass ?>">
                        <?= $dlMsg ?>
                    </span>
                </span>
            </div>

            <?php if (!empty($t['description'])): ?>
                <p class="task-description">
                    <?= nl2br(htmlspecialchars($t['description'], ENT_QUOTES)) ?>
                </p>
            <?php endif; ?>

            <?php if (!empty($t['tags'])): ?>
                <p class="tags">
                    <em>Тэги: <?= implode(', ', json_decode($t['tags'], true) ?: []) ?></em>
                </p>
            <?php endif; ?>

            <?php if (!empty($t['steps'])): ?>
                <div class="task-steps">
                    <p><strong>Шаги:</strong></p>
                    <ul>
                        <?php foreach (json_decode($t['steps'], true) ?: [] as $step): ?>
                            <li><?= htmlspecialchars($step, ENT_QUOTES) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>