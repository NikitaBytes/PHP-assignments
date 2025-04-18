<?php
declare(strict_types=1);

namespace Handlers\Task;

use Core\Database;
use Core\View;
use PDO;

/**
 * Класс EditForm - обработчик отображения формы редактирования задачи.
 *
 * Обрабатывает GET-запрос на /task/edit?id={id}.
 * Загружает данные задачи по ID и список категорий,
 * затем рендерит шаблон 'task/edit' с предзаполненными данными.
 *
 * @package Handlers\Task
 * @version 1.0
 */
final class EditForm
{
    /**
     * Обрабатывает GET-запрос для отображения формы редактирования.
     *
     * Получает ID задачи из GET-параметров. Если ID некорректен, возвращает 400 ошибку.
     * Загружает данные задачи и список категорий из базы данных.
     * Если задача не найдена, возвращает 404 ошибку.
     * Передает данные в шаблон 'task/edit' для рендеринга.
     *
     * @return void
     * @throws \PDOException при ошибке взаимодействия с базой данных
     */
    public function handle(): void
    {
        // Получаем и валидируем ID задачи из GET-запроса
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo 'Bad request: Invalid task ID'; // Уточнено сообщение об ошибке
            return;
        }

        $pdo = Database::connection();

        // Загрузка данных задачи
        $stmt = $pdo->prepare('SELECT * FROM tasks WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $task = $stmt->fetch(PDO::FETCH_ASSOC);

        // Если задача не найдена, возвращаем 404 ошибку
        if (!$task) {
            http_response_code(404);
            echo 'Task not found';
            return;
        }

        // Загрузка списка категорий для выпадающего списка
        $cats = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);

        // Рендеринг шаблона формы редактирования с передачей данных
        View::render('task/edit', [
            'task'       => $task,       // Данные редактируемой задачи
            'categories' => $cats,       // Список всех категорий
            'errors'     => [],           // Массив ошибок (пустой при GET-запросе)
        ]);
    }
}