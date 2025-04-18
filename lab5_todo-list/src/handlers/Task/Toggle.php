<?php
namespace Handlers\Task;

use Core\Database;

/**
 * Класс Toggle - обработчик AJAX-запроса на переключение статуса задачи.
 *
 * Обрабатывает POST-запрос на /task/toggle.
 * Инвертирует статус 'completed' для задачи с указанным ID.
 * Возвращает JSON-ответ об успехе операции.
 *
 * @package Handlers\Task
 * @author ToDoList Team
 * @version 1.0
 */
final class Toggle
{
    /**
     * Обрабатывает AJAX POST-запрос для переключения статуса задачи.
     *
     * Получает ID задачи из POST-параметров, обновляет поле 'completed' в базе данных
     * и возвращает JSON-ответ.
     *
     * @return void
     * @throws \PDOException при ошибке взаимодействия с базой данных
     */
    public function handle(): void
    {
        // Устанавливаем заголовок ответа как JSON
        header('Content-Type: application/json; charset=utf-8');

        // Получаем и преобразуем ID задачи из POST-запроса
        $id = (int)($_POST['id'] ?? 0);
        $pdo= Database::connection();

        // Подготавливаем и выполняем SQL-запрос для инвертирования статуса 'completed'
        $pdo->prepare('UPDATE tasks SET completed = NOT completed WHERE id=:id')
            ->execute([':id'=>$id]);

        // Возвращаем JSON-ответ об успехе
        echo json_encode(['success'=>true]); exit;
    }
}