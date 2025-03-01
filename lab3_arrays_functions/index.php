<?php
declare(strict_types=1);

/**
 * Лабораторная работа №3: Массивы и Функции
 *
 * Пример реализации "Системы управления банковскими транзакциями"
 * с возможностью добавления, удаления, сортировки и поиска
 * в виде интерактивного веб-интерфейса (минималистичный «Apple-стиль»).
 *
 * Все требования задания:
 *  1) Добавление, удаление транзакций.
 *  2) Сортировка по дате и по сумме.
 *  3) Поиск транзакций по описанию.
 *  4) Функции findTransactionById (через foreach и через array_filter).
 *  5) Подсчет количества дней с даты транзакции.
 *  6) Вывод общей суммы транзакций.
 */

// ----------------------------------------------------
// ИСХОДНЫЕ ДАННЫЕ (каждый раз загружаются заново)
// ----------------------------------------------------
$transactions = [
    [
        "id"          => 1,
        "date"        => "2019-01-01",
        "amount"      => 100.00,
        "description" => "Payment for groceries",
        "merchant"    => "SuperMart",
    ],
    [
        "id"          => 2,
        "date"        => "2020-02-15",
        "amount"      => 75.50,
        "description" => "Dinner with friends",
        "merchant"    => "Local Restaurant",
    ],
    [
        "id"          => 3,
        "date"        => "2021-06-10",
        "amount"      => 1200.00,
        "description" => "Monthly rent",
        "merchant"    => "City Apartments",
    ],
    [
        "id"          => 4,
        "date"        => "2022-11-25",
        "amount"      => 299.99,
        "description" => "Online shopping (electronics)",
        "merchant"    => "TechStore",
    ],
];

/**
 * Добавляет новую транзакцию в массив $transactions (переданный по ссылке).
 *
 * @param array  $transactions Ссылочный массив с транзакциями
 * @param int    $id           Уникальный идентификатор транзакции
 * @param string $date         Дата в формате YYYY-MM-DD
 * @param float  $amount       Сумма транзакции
 * @param string $description  Описание платежа
 * @param string $merchant     Получатель (организация)
 *
 * @return void
 */
function addTransaction(array &$transactions, int $id, string $date, float $amount, string $description, string $merchant): void
{
    $transactions[] = [
        "id"          => $id,
        "date"        => $date,
        "amount"      => $amount,
        "description" => $description,
        "merchant"    => $merchant,
    ];
}

/**
 * Удаляет транзакцию по её ID из массива $transactions (переданного по ссылке).
 *
 * @param array $transactions Ссылочный массив с транзакциями
 * @param int   $id           Уникальный идентификатор транзакции
 *
 * @return void
 */
function removeTransaction(array &$transactions, int $id): void
{
    foreach ($transactions as $index => $t) {
        if ($t['id'] === $id) {
            unset($transactions[$index]);
            // Сбрасываем индексы массива, чтобы не было «дыр»
            $transactions = array_values($transactions);
            break;
        }
    }
}

/**
 * Сортирует транзакции по дате (возрастание) с использованием usort().
 *
 * @param array $transactions Ссылочный массив с транзакциями
 *
 * @return void
 */
function sortTransactionsByDate(array &$transactions): void
{
    usort($transactions, function ($a, $b) {
        $dateA = new DateTime($a['date']);
        $dateB = new DateTime($b['date']);
        return $dateA <=> $dateB;
    });
}

/**
 * Сортирует транзакции по сумме (убывание) с использованием usort().
 *
 * @param array $transactions Ссылочный массив с транзакциями
 *
 * @return void
 */
function sortTransactionsByAmountDesc(array &$transactions): void
{
    usort($transactions, function ($a, $b) {
        return $b['amount'] <=> $a['amount'];
    });
}

/**
 * Ищет транзакции по части описания (регистр не учитывается).
 * Аналог findTransactionByDescription(string $descriptionPart).
 *
 * @param array  $transactions Массив транзакций
 * @param string $descriptionPart Часть описания для поиска
 *
 * @return array Массив подходящих транзакций
 */
function findTransactionByDescription(array $transactions, string $descriptionPart): array
{
    $descriptionPart = mb_strtolower($descriptionPart);
    $result = [];
    foreach ($transactions as $t) {
        if (mb_strpos(mb_strtolower($t['description']), $descriptionPart) !== false) {
            $result[] = $t;
        }
    }
    return $result;
}

/**
 * Ищет транзакцию по идентификатору (через foreach).
 *
 * @param array $transactions Массив транзакций
 * @param int   $id           Уникальный идентификатор
 *
 * @return array|null Вернёт транзакцию или null, если не найдена
 */
function findTransactionByIdForeach(array $transactions, int $id): ?array
{
    foreach ($transactions as $t) {
        if ($t['id'] === $id) {
            return $t;
        }
    }
    return null;
}

/**
 * Ищет транзакцию по идентификатору (через array_filter).
 *
 * @param array $transactions Массив транзакций
 * @param int   $id           Уникальный идентификатор
 *
 * @return array|null Вернёт транзакцию или null, если не найдена
 */
function findTransactionByIdFilter(array $transactions, int $id): ?array
{
    $filtered = array_filter($transactions, fn($t) => $t['id'] === $id);
    // array_filter вернёт массив, возьмём первый элемент или null
    return $filtered ? array_shift($filtered) : null;
}

/**
 * Возвращает количество дней, прошедших с даты транзакции.
 *
 * @param string $date Дата в формате YYYY-MM-DD
 *
 * @return int Количество дней между $date и сегодняшним днём
 */
function daysSinceTransaction(string $date): int
{
    $transactionDate = new DateTime($date);
    $now = new DateTime('now');
    $interval = $transactionDate->diff($now);
    return $interval->days;
}

/**
 * Вычисляет общую сумму всех транзакций.
 *
 * @param array $transactions Массив транзакций
 *
 * @return float Сумма по полю 'amount'
 */
function calculateTotalAmount(array $transactions): float
{
    $total = 0.0;
    foreach ($transactions as $t) {
        $total += $t['amount'];
    }
    return $total;
}

// ----------------------------------------------------
// ОБРАБОТКА ФОРМ (POST / GET)
// ----------------------------------------------------

/**
 * Добавление новой транзакции
 */
if (isset($_POST['action']) && $_POST['action'] === 'add') {
    // Получаем данные из формы
    $id          = (int)$_POST['id'];
    $date        = $_POST['date'];
    $amount      = (float)$_POST['amount'];
    $description = $_POST['description'];
    $merchant    = $_POST['merchant'];
    // Добавляем
    addTransaction($transactions, $id, $date, $amount, $description, $merchant);
}

/**
 * Удаление транзакции
 */
if (isset($_POST['action']) && $_POST['action'] === 'remove') {
    $removeId = (int)$_POST['remove_id'];
    removeTransaction($transactions, $removeId);
}

/**
 * Сортировка (дата или сумма)
 */
if (isset($_POST['action']) && $_POST['action'] === 'sort') {
    $sortType = $_POST['sort_type'] ?? '';
    if ($sortType === 'date') {
        sortTransactionsByDate($transactions);
    } elseif ($sortType === 'amount') {
        sortTransactionsByAmountDesc($transactions);
    }
}

/**
 * Поиск по описанию
 */
$searchResults = [];
$searchQuery = '';
if (isset($_POST['action']) && $_POST['action'] === 'search') {
    $searchQuery = trim($_POST['search_query'] ?? '');
    if ($searchQuery !== '') {
        // используем функцию findTransactionByDescription
        $searchResults = findTransactionByDescription($transactions, $searchQuery);
    }
}

// ----------------------------------------------------
// ВЫВОД HTML (ИНТЕРАКТИВНАЯ СТРАНИЦА)
// ----------------------------------------------------
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Лабораторная №3: Банковские транзакции</title>
    <style>
        body {
            background-color: #f5f5f7;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, Cantarell, "Open Sans", "Helvetica Neue", sans-serif;
            color: #1c1c1e;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 95%;
            max-width: 1200px;
            margin: 30px auto;
        }
        h1, h2 {
            text-align: center;
            font-weight: 600;
        }
        h2 {
            margin-top: 40px;
        }
        form {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        form .row {
            margin-bottom: 10px;
        }
        form label {
            display: inline-block;
            width: 150px;
            font-weight: 500;
        }
        form input[type="text"],
        form input[type="number"],
        form input[type="date"],
        form select {
            font-size: 16px;
            padding: 6px 8px;
            border: 1px solid #ccc;
            border-radius: 8px;
            width: 240px;
        }
        form button {
            font-size: 16px;
            padding: 8px 16px;
            border: none;
            border-radius: 8px;
            background-color: #007aff;
            color: #fff;
            cursor: pointer;
            margin-top: 10px;
        }
        form button:hover {
            background-color: #005bb5;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            background-color: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        th, td {
            text-align: left;
            padding: 12px;
            border-bottom: 1px solid #eee;
        }
        th {
            background-color: #f9f9f9;
            color: #666;
        }
        tr:hover td {
            background-color: #fafafa;
        }
        .total {
            text-align: center;
            font-weight: 600;
            margin-top: 10px;
        }
        .search-results {
            background-color: #fff;
            margin-top: 20px;
            padding: 15px;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .search-results h3 {
            margin-top: 0;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Система управления банковскими транзакциями</h1>

    <!-- ФОРМА: Добавить транзакцию -->
    <form method="post">
        <h2>Добавить транзакцию</h2>
        <input type="hidden" name="action" value="add">
        <div class="row">
            <label for="id">ID:</label>
            <input type="number" name="id" id="id" required>
        </div>
        <div class="row">
            <label for="date">Дата:</label>
            <input type="date" name="date" id="date" required>
        </div>
        <div class="row">
            <label for="amount">Сумма:</label>
            <input type="number" step="0.01" name="amount" id="amount" required>
        </div>
        <div class="row">
            <label for="description">Описание:</label>
            <input type="text" name="description" id="description" required>
        </div>
        <div class="row">
            <label for="merchant">Получатель:</label>
            <input type="text" name="merchant" id="merchant" required>
        </div>
        <button type="submit">Добавить</button>
    </form>

    <!-- ФОРМА: Удалить транзакцию -->
    <form method="post">
        <h2>Удалить транзакцию</h2>
        <input type="hidden" name="action" value="remove">
        <div class="row">
            <label for="remove_id">ID транзакции:</label>
            <input type="number" name="remove_id" id="remove_id" required>
        </div>
        <button type="submit">Удалить</button>
    </form>

    <!-- ФОРМА: Сортировать транзакции -->
    <form method="post">
        <h2>Сортировка транзакций</h2>
        <input type="hidden" name="action" value="sort">
        <div class="row">
            <label for="sort_type">Сортировать по:</label>
            <select name="sort_type" id="sort_type">
                <option value="">Без сортировки</option>
                <option value="date">Дате (возрастание)</option>
                <option value="amount">Сумме (убывание)</option>
            </select>
        </div>
        <button type="submit">Сортировать</button>
    </form>

    <!-- ФОРМА: Поиск транзакций по описанию -->
    <form method="post">
        <h2>Поиск по описанию</h2>
        <input type="hidden" name="action" value="search">
        <div class="row">
            <label for="search_query">Поиск:</label>
            <input type="text" name="search_query" id="search_query" required>
        </div>
        <button type="submit">Искать</button>
    </form>

    <!-- ОТОБРАЖЕНИЕ ТРАНЗАКЦИЙ -->
    <h2>Список транзакций</h2>
    <table>
        <thead>
        <tr>
            <th>ID</th>
            <th>Дата</th>
            <th>Сумма</th>
            <th>Описание</th>
            <th>Получатель</th>
            <th>Дней с транзакции</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($transactions as $t): ?>
            <tr>
                <td><?php echo $t['id']; ?></td>
                <td><?php echo $t['date']; ?></td>
                <td><?php echo $t['amount']; ?></td>
                <td><?php echo $t['description']; ?></td>
                <td><?php echo $t['merchant']; ?></td>
                <td><?php echo daysSinceTransaction($t['date']); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <div class="total">
        <?php $total = calculateTotalAmount($transactions); ?>
        Общая сумма: <strong><?php echo $total; ?></strong>
    </div>

    <!-- РЕЗУЛЬТАТЫ ПОИСКА (если был выполнен поиск) -->
    <?php if (!empty($searchQuery)): ?>
        <div class="search-results">
            <h3>Результаты поиска по «<?php echo htmlspecialchars($searchQuery); ?>»:</h3>
            <?php if (count($searchResults) > 0): ?>
                <table>
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Дата</th>
                        <th>Сумма</th>
                        <th>Описание</th>
                        <th>Получатель</th>
                        <th>Дней с транзакции</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($searchResults as $res): ?>
                        <tr>
                            <td><?php echo $res['id']; ?></td>
                            <td><?php echo $res['date']; ?></td>
                            <td><?php echo $res['amount']; ?></td>
                            <td><?php echo $res['description']; ?></td>
                            <td><?php echo $res['merchant']; ?></td>
                            <td><?php echo daysSinceTransaction($res['date']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Ничего не найдено.</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

</div>
</body>
</html>