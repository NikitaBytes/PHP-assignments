<?php
/**
 * Шаблон: Список всех задач с фильтрацией и пагинацией.
 *
 * Отображает список задач, позволяя фильтровать их по статусу (все, выполненные, невыполненные).
 * Реализует пагинацию для больших списков задач.
 * Показывает основную информацию по каждой задаче (название, приоритет, дедлайн, описание, теги, шаги).
 * Предоставляет кнопки для отметки выполнения, удаления и просмотра деталей задачи.
 * Содержит JavaScript для AJAX-переключения статуса выполнения задачи.
 *
 * @var array<int, array> $tasks Массив задач для отображения на текущей странице.
 *                               Каждая задача - ассоциативный массив с полями из БД.
 * @var int    $page         Номер текущей страницы пагинации.
 * @var int    $totalPages   Общее количество страниц пагинации.
 * @var string $filter       Текущий активный фильтр ('all', 'completed', 'incomplete').
 */
?>
<h1>Список всех задач</h1>

<!-- фильтры ---------------------------------------------------------------->
<nav style="margin-bottom:20px;text-align:center">
    <?php
    $filters = [
        'all'        => 'Все',
        'completed'  => 'Выполненные',
        'incomplete' => 'Невыполненные',
    ];
    foreach ($filters as $k => $label): ?>
        <a class="btn<?= $k === 'all' ? '3' : ($k === 'completed' ? '4' : '5') ?>"
           href="/task/index?filter=<?= $k ?>"
           style="<?= $filter === $k ? 'font-weight:700' : '' ?>">
            <?= $label ?>
        </a>
    <?php endforeach; ?>
</nav>

<p>
    <a class="btn" href="/">На главную</a>
    <a class="btn" href="/task/create">Создать задачу</a>
</p>

<?php if (empty($tasks)): ?>
    <p>Список пуст.</p>
<?php else: ?>
    <ul class="task-list">
        <?php foreach ($tasks as $t):

            // — приоритет ------------------------------------------------------
            $prioClass = match ($t['priority']) {
                'high'   => 'priority-high',
                'medium' => 'priority-medium',
                default  => 'priority-low'
            };

            // — дедлайн --------------------------------------------------------
            [$msg, $deadlineClass] = (function (?string $date): array {
                if (!$date)   return ['Дата не указана',       'deadline-none'];
                $d = new DateTime($date);
                $now = new DateTime();
                $diff = (int)$now->diff($d)->format('%r%a');
                return match (true) {
                    $diff < 0  => ['Просрочено на ' . abs($diff) . ' дн.', 'deadline-overdue'],
                    $diff === 0 => ['Дедлайн сегодня!',              'deadline-critical'],
                    $diff <= 5  => ['Осталось ' . $diff . ' дн.',     'deadline-critical'],
                    default     => ['Осталось ' . $diff . ' дн.',     'deadline-normal'],
                };
            })($t['due_date'] ?? null);

            $id   = (int)$t['id'];
            $done = (bool)$t['completed'];
        ?>
            <li class="task-item" style="<?= $done ? 'opacity:.65' : '' ?>">
                <div class="task-title">
                    <a href="/task/show?id=<?= $id ?>">
                        <?= htmlspecialchars($t['title'], ENT_QUOTES) ?>
                    </a>
                    <!-- Info button -->
                    <a class="btn btn-info" href="/task/show?id=<?= $id ?>" style="margin-left:10px">ℹ Info</a>
                    <?php if ($done): ?>
                        <span style="color:green;margin-left:8px">✔</span>
                    <?php endif; ?>
                </div>

                <div class="task-meta">
                    <span class="priority-label <?= $prioClass ?>">
                        <?= htmlspecialchars($t['priority']) ?>
                    </span>
                    <span>
                        Дедлайн: <?= htmlspecialchars($t['due_date'] ?: '—') ?>
                        <span class="deadline-label <?= $deadlineClass ?>">
                            <?= $msg ?>
                        </span>
                    </span>
                </div>

                <?php if ($t['description']): ?>
                    <p class="task-description"><?= nl2br(htmlspecialchars($t['description'])) ?></p>
                <?php endif; ?>

                <?php if ($t['tags']): ?>
                    <p class="tags"><em>Тэги: <?= implode(', ', json_decode($t['tags'], true) ?: []) ?></em></p>
                <?php endif; ?>

                <?php if ($t['steps']): ?>
                    <div class="task-steps">
                        <p><strong>Шаги:</strong></p>
                        <ul>
                            <?php foreach (json_decode($t['steps'], true) ?: [] as $step): ?>
                                <li><?= htmlspecialchars($step) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <!-- кнопки ----------------------------------------------------->
                <div style="margin-top:10px">
                    <button data-toggle data-id="<?= $id ?>" class="btn">
                        <?= $done ? '↩ Отменить' : '✔ Выполнить' ?>
                    </button>

                    <form action="/task/delete" method="post" style="display:inline">
                        <input type="hidden" name="id" value="<?= $id ?>">
                        <button class="btn" style="background:#ff3b30"
                                onclick="return confirm('Удалить задачу?')">🗑</button>
                    </form>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>

    <!-- пагинация ------------------------------------------------------------>
    <?php if ($totalPages > 1): ?>
        <div style="text-align:center;margin-top:20px">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="/task/index?page=<?= $i ?>&filter=<?= $filter ?>"
                   style="<?= $i === $page ? 'font-weight:700' : '' ?>">
                   <?= $i ?>
                </a>
                <?= $i < $totalPages ? ' | ' : '' ?>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>

<!-- AJAX‑переключатель "выполнено/не выполнено" ---------------------------->
<script>
document.addEventListener('click', e => {
    if (!e.target.matches('[data-toggle]')) return;
    e.preventDefault();
    const btn = e.target, id = btn.dataset.id;
    fetch('/task/toggle', {
        method: 'POST',
        body: new URLSearchParams({id})
    })
    .then(r => r.json())
    .then(json => location.reload())
    .catch(() => alert('Ошибка запроса'));
});
</script>