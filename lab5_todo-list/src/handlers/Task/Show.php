<?php
namespace Handlers\Task;

use Core\Database;
use Core\View;

/**
 * Класс Show - обработчик отображения детальной информации о задаче.
 *
 * Обрабатывает GET-запрос на /task/show?id={id}.
 * Загружает данные задачи по ID, включая имя категории,
 * декодирует JSON-поля tags и steps и рендерит шаблон 'task/show'.
 *
 * @package Handlers\Task
 * @version 1.0
 */
final class Show
{
    /**
     * Обрабатывает GET-запрос для отображения задачи.
     *
     * Получает ID задачи из GET-параметров, загружает данные из базы данных,
     * обрабатывает их и передает в шаблон для рендеринга.
     * В случае отсутствия задачи возвращает 404 ошибку.
     *
     * @return void
     * @throws \PDOException при ошибке взаимодействия с базой данных
     */
    public function handle(): void
    {
        // Получаем и преобразуем ID задачи из GET-запроса
        $id = (int)($_GET['id'] ?? 0);
        $pdo = Database::connection();

        // Подготавливаем SQL-запрос для получения данных задачи и имени категории
        $stmt = $pdo->prepare(<<<SQL
            SELECT t.*, c.name AS category_name
            FROM tasks t
            LEFT JOIN categories c ON t.category_id = c.id
            WHERE t.id = :id
        SQL);
        // Выполняем запрос
        $stmt->execute([':id' => $id]);
        // Получаем данные задачи
        $task = $stmt->fetch();

        // Если задача не найдена, возвращаем 404 ошибку
        if (!$task) {
            http_response_code(404);
            echo 'Task not found';
            return;
        }

        // Декодируем JSON-поля tags и steps в массивы
        $task['tags']   = $task['tags']   ? json_decode($task['tags'],   true) : [];
        $task['steps']  = $task['steps']  ? json_decode($task['steps'],  true) : [];

        // Рендерим шаблон 'task/show', передавая данные задачи
        View::render('task/show', ['task' => $task]);
    }
}