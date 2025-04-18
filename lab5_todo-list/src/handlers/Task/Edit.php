<?php
declare(strict_types=1);

namespace Handlers\Task;

use Core\Database;
use Core\View; // Добавлено для рендеринга формы при ошибках
use PDO;

/**
 * Класс Edit - обработчик обновления данных задачи.
 *
 * Обрабатывает POST-запрос на /task/edit.
 * Получает данные из формы, выполняет базовую валидацию.
 * При ошибках валидации повторно отображает форму редактирования с сообщениями об ошибках.
 * При успехе обновляет запись в базе данных и перенаправляет на страницу просмотра задачи.
 *
 * @package Handlers\Task
 * @version 1.0
 */
final class Edit
{
    /**
     * Обрабатывает POST-запрос для обновления задачи.
     *
     * Получает данные из $_POST, валидирует их.
     * Если есть ошибки, вызывает EditForm::handle() для повторного отображения формы.
     * Если ошибок нет, обновляет данные задачи в базе данных и перенаправляет
     * пользователя на страницу просмотра обновленной задачи (/task/show?id={id}).
     *
     * @return void
     * @throws \PDOException при ошибке взаимодействия с базой данных
     */
    public function handle(): void
    {
        // Получение и базовая санитация данных из POST-запроса
        $id          = (int)($_POST['id'] ?? 0);
        $title       = trim($_POST['title'] ?? '');
        $categoryId  = filter_var($_POST['category_id'] ?? 0, FILTER_VALIDATE_INT) ?: null; // Используем filter_var
        $priority    = $_POST['priority'] ?? 'medium';
        $dueDate     = !empty($_POST['due_date']) ? trim($_POST['due_date']) : null; // Проверка на пустоту
        $description = trim($_POST['description'] ?? '');
        // Обработка тегов: предполагаем, что это строка через запятую или массив
        $tagsInput   = $_POST['tags'] ?? '';
        if (is_string($tagsInput)) {
            $tags = array_map('trim', explode(',', $tagsInput));
            $tags = array_filter($tags, fn($tag) => $tag !== ''); // Удаляем пустые теги
        } elseif (is_array($tagsInput)) {
            $tags = array_map('trim', $tagsInput);
            $tags = array_filter($tags, fn($tag) => $tag !== '');
        } else {
            $tags = [];
        }
        // Обработка шагов
        $steps       = is_array($_POST['steps'] ?? null) ? array_map('trim', $_POST['steps']) : [];
        $steps       = array_filter($steps, fn($step) => $step !== ''); // Удаляем пустые шаги

        // --- Расширенная валидация (аналогично Create) ---
        $errors = [];
        if ($id <= 0) {
            $errors['general'] = 'Некорректный ID задачи.'; // Общая ошибка, если ID невалиден
        }
        if ($title === '') {
            $errors['title'] = 'Название задачи обязательно к заполнению';
        } elseif (mb_strlen($title) > 255) {
            $errors['title'] = 'Название задачи не должно превышать 255 символов';
        }

        $pdo = Database::connection(); // Соединение нужно для проверки категории

        // Валидация категории (должна существовать в БД)
        if ($categoryId !== null) {
            $stmtCat = $pdo->prepare('SELECT COUNT(*) FROM categories WHERE id = ?');
            $stmtCat->execute([$categoryId]);
            if ($stmtCat->fetchColumn() == 0) {
                $errors['category_id'] = 'Выбранная категория не существует';
            }
        } else {
             $errors['category_id'] = 'Необходимо выбрать категорию'; // Если категория обязательна
        }


        if (!in_array($priority, ['low', 'medium', 'high'], true)) {
            $errors['priority'] = 'Некорректный приоритет задачи';
            $priority = 'medium'; // Возвращаем к дефолту при ошибке
        }

        // Валидация даты (формат YYYY-MM-DD, не в прошлом)
        if ($dueDate !== null) {
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dueDate)) {
                $errors['due_date'] = 'Дата должна быть в формате YYYY-MM-DD';
            } else {
                $date = \DateTime::createFromFormat('Y-m-d', $dueDate);
                if (!$date || $date->format('Y-m-d') !== $dueDate) {
                    $errors['due_date'] = 'Указана некорректная дата';
                }
                // Опционально: проверка на прошедшую дату
                // $today = new \DateTime();
                // $today->setTime(0, 0, 0);
                // if ($date < $today) {
                //     $errors['due_date'] = 'Дата не может быть в прошлом';
                // }
            }
        }

        // Валидация описания
        if (mb_strlen($description) > 1000) {
            $errors['description'] = 'Описание не должно превышать 1000 символов';
        }

        // Валидация тегов
        if (count($tags) > 10) {
            $errors['tags'] = 'Максимальное количество тегов: 10';
        } else {
            foreach ($tags as $tag) {
                if (mb_strlen($tag) > 50) {
                    $errors['tags'] = 'Длина тега не должна превышать 50 символов';
                    break;
                }
            }
        }

        // Валидация шагов
        if (count($steps) > 20) {
            $errors['steps'] = 'Максимальное количество шагов: 20';
        } else {
            foreach ($steps as $step) {
                if (mb_strlen($step) > 500) {
                    $errors['steps'] = 'Длина шага не должна превышать 500 символов';
                    break;
                }
            }
        }
        // --- Конец валидации ---


        // Если есть ошибки валидации
        if (!empty($errors)) {
            // Загружаем данные для повторного отображения формы
            $cats = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);
            // Собираем текущие (невалидные) значения для передачи в шаблон
            $currentValues = compact('id', 'title', 'categoryId', 'priority', 'dueDate', 'description', 'tags', 'steps');

            // Рендерим шаблон редактирования с ошибками и старыми значениями
            View::render('task/edit', [
                'task'       => $currentValues, // Передаем текущие значения как 'task'
                'categories' => $cats,
                'errors'     => $errors,
            ]);
            return;
        }

        // --- Обновление данных в базе данных ---
        $stmt = $pdo->prepare(<<<SQL
            UPDATE tasks SET
                title       = :title,
                category_id = :cat,
                priority    = :priority,
                due_date    = :due,
                description = :descr,
                tags        = :tags,
                steps       = :steps
            WHERE id = :id
        SQL);
        $stmt->execute([
            ':title'    => $title,
            ':cat'      => $categoryId, // categoryId уже проверен и может быть null, если разрешено
            ':priority' => $priority,
            ':due'      => $dueDate,
            ':descr'    => $description,
            ':tags'     => json_encode($tags,  JSON_UNESCAPED_UNICODE), // Кодируем массив тегов в JSON
            ':steps'    => json_encode(array_values($steps), JSON_UNESCAPED_UNICODE), // Кодируем массив шагов в JSON
            ':id'       => $id,
        ]);

        // Перенаправление на страницу просмотра задачи после успешного обновления
        header('Location: /task/show?id=' . $id);
        exit;
    }
}