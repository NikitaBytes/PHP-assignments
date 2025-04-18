<?php
namespace Handlers\Task;

use Core\Database;
use Core\View;
use PDO; // Добавлено для PDO::FETCH_ASSOC

/**
 * Класс CreateForm - обработчик отображения формы создания новой задачи.
 *
 * Обрабатывает GET-запрос на /task/create.
 * Загружает список категорий из базы данных и рендерит шаблон 'task/create'
 * с пустыми значениями полей и списком категорий.
 *
 * @package Handlers\Task
 * @version 1.0
 */
final class CreateForm
{
    /**
     * Обрабатывает GET-запрос для отображения формы создания задачи.
     *
     * Загружает список категорий из базы данных и передает его вместе
     * с пустым массивом ошибок и значениями по умолчанию в шаблон 'task/create'.
     *
     * @return void
     * @throws \PDOException при ошибке взаимодействия с базой данных
     */
    public function handle(): void
    {
        $pdo = Database::connection();
        // Загружаем список категорий для выпадающего списка
        $categories = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);

        // Рендерим шаблон 'task/create'
        View::render('task/create', [
            'categories' => $categories, // Список категорий
            'errors'     => [],           // Пустой массив ошибок
            'oldValues'  => [            // Значения по умолчанию для полей формы
                'title'       => '',
                'description' => '',
                'due_date'    => '',
                'priority'    => 'medium', // Приоритет по умолчанию
                'category_id' => null,     // Категория не выбрана
                'tags'        => [],       // Пустой массив тегов
                'steps'       => ['']      // Один пустой шаг по умолчанию
            ]
        ]);
    }
}