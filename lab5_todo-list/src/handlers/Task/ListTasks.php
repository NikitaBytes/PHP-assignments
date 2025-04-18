<?php
/**
 * Обработчик GET-запроса на /task/index.
 * Выводит список задач с возможностью фильтрации и пагинации.
 *
 * @package Handlers\Task
 * @version 1.0
 */

namespace Handlers\Task;

use Core\Database;
use Core\View;
use PDO;

/**
 * Класс ListTasks - обработчик для отображения списка задач.
 *
 * Получает параметры фильтрации и пагинации из GET-запроса,
 * формирует SQL-запрос для выборки задач из базы данных
 * и рендерит шаблон 'task/index' с полученными данными.
 */
final class ListTasks
{
    /**
     * Обрабатывает GET-запрос для отображения списка задач.
     *
     * Принимает параметры 'filter' (all, completed, incomplete) и 'page'.
     * Выполняет запрос к базе данных с учетом фильтра и пагинации.
     * Передает полученные задачи и информацию о пагинации в шаблон.
     *
     * @return void
     * @throws \PDOException при ошибке взаимодействия с базой данных
     */
    public function handle(): void
    {
        // --- Получение входных параметров из GET-запроса ---
        /** @var string $filter Тип фильтрации ('all', 'completed', 'incomplete') */
        $filter   = $_GET['filter'] ?? 'all';
        /** @var int $page Номер текущей страницы (минимум 1) */
        $page     = max(1, (int)($_GET['page'] ?? 1));
        /** @var int $perPage Количество задач на странице */
        $perPage  = 5;
        /** @var int $offset Смещение для SQL-запроса (LIMIT) */
        $offset   = ($page - 1) * $perPage;

        // --- Формирование условия WHERE для SQL-запроса ---
        $where  = '';
        // $params пока не используется, но может пригодиться для параметризации запроса COUNT
        $params = [];

        if ($filter === 'completed') {
            $where = 'WHERE completed = 1';
        } elseif ($filter === 'incomplete') {
            $where = 'WHERE completed = 0';
        }

        // --- Подключение к базе данных ---
        $pdo = Database::connection();

        // --- Определение общего количества задач для пагинации ---
        /** @var int $total Общее количество задач, удовлетворяющих фильтру */
        $total = (int)$pdo->query("SELECT COUNT(*) FROM tasks $where")->fetchColumn();
        /** @var int $totalPages Общее количество страниц */
        $totalPages = max(1, (int)ceil($total / $perPage));
        // Корректировка номера страницы, если он превышает общее количество страниц
        $page = min($page, $totalPages);
        $offset = ($page - 1) * $perPage; // Пересчет смещения

        // --- Получение задач для текущей страницы ---
        $stmt = $pdo->prepare("
            SELECT *
              FROM tasks
              $where
          ORDER BY created_at DESC
             LIMIT :limit
            OFFSET :offset
        ");
        $stmt->bindValue(':limit',  $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset,  PDO::PARAM_INT);
        $stmt->execute();

        /** @var array $tasks Массив задач для текущей страницы */
        $tasks = $stmt->fetchAll();

        // --- Рендеринг шаблона с передачей данных ---
        View::render('task/index', [
            'tasks'      => $tasks,
            'filter'     => $filter,
            'page'       => $page,
            'totalPages' => $totalPages,
        ]);
    }
}