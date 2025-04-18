<?php
namespace Handlers\Task;

use Core\Database;

/**
 * Класс Delete - обработчик удаления задачи.
 *
 * Обрабатывает POST-запрос на /task/delete.
 * Удаляет задачу с указанным ID из базы данных.
 * Перенаправляет пользователя на страницу списка задач.
 *
 * @package Handlers\Task
 * @author ToDoList Team
 * @version 1.0
 */
final class Delete
{
    /**
     * Обрабатывает POST-запрос для удаления задачи.
     *
     * Получает ID задачи из POST-параметров, выполняет SQL-запрос DELETE
     * и перенаправляет пользователя на /task/index.
     *
     * @return void
     * @throws \PDOException при ошибке взаимодействия с базой данных
     */
    public function handle(): void
    {
        // Получаем и преобразуем ID задачи из POST-запроса
        $id=(int)($_POST['id']??0);
        // Получаем соединение с базой данных
        $pdo = Database::connection();
        // Подготавливаем и выполняем SQL-запрос для удаления задачи
        $pdo->prepare('DELETE FROM tasks WHERE id=:id')->execute([':id'=>$id]);

        // Перенаправляем пользователя на страницу списка задач
        header('Location: /task/index'); exit;
    }
}
