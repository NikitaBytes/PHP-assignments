<?php
namespace Handlers\Task;

use Core\Database;
use Core\View;

/**
 * Класс Index - обработчик главной страницы.
 *
 * Обрабатывает GET-запрос на '/'.
 * Загружает две последние созданные задачи из базы данных
 * и рендерит шаблон 'index'.
 *
 * @package Handlers\Task
 * @author ToDoList Team
 * @version 1.0
 */
final class Index
{
    /**
     * Обрабатывает GET-запрос для отображения главной страницы.
     *
     * Выбирает две последние задачи из базы данных и передает их
     * в шаблон 'index' для рендеринга.
     *
     * @return void
     * @throws \PDOException при ошибке взаимодействия с базой данных
     */
    public function handle(): void
    {
        // Получаем соединение с базой данных
        $pdo = Database::connection();
        // Выполняем запрос на получение двух последних задач
        $tasks = $pdo->query('SELECT * FROM tasks ORDER BY created_at DESC LIMIT 2')
                     ->fetchAll();

        // Рендерим шаблон 'index', передавая полученные задачи
        View::render('index', ['tasks'=>$tasks]);
    }
}