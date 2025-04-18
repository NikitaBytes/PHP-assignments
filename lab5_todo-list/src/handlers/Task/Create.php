<?php
namespace Handlers\Task;

use Core\Database;
use Core\View;
use PDO;

/**
 * Класс Create - обработчик создания новой задачи
 *
 * Обрабатывает POST-запрос на /task/create для создания новой задачи.
 * Выполняет валидацию данных формы, сохраняет задачу в базу данных
 * и перенаправляет пользователя на главную страницу в случае успеха.
 * При ошибках валидации повторно отображает форму с сообщениями об ошибках.
 *
 * @package Handlers\Task
 * @version 1.1 Добавлена повторная отрисовка формы при ошибках валидации.
 */
final class Create
{
    /**
     * Обрабатывает POST-запрос для создания новой задачи
     *
     * Выполняет следующие действия:
     * 1. Получает и санитизирует данные из формы ($_POST).
     * 2. Выполняет расширенную валидацию полученных данных.
     * 3. При ошибках валидации:
     *    - Загружает список категорий.
     *    - Повторно рендерит шаблон 'task/create', передавая ошибки и введенные пользователем значения.
     * 4. При успешной валидации:
     *    - Вставляет новую задачу в базу данных с использованием подготовленных выражений.
     *    - Перенаправляет пользователя на главную страницу ('/').
     *
     * @return void
     * @throws \PDOException при ошибке взаимодействия с базой данных (кроме проверок).
     */
    public function handle(): void
    {
        // Инициализация соединения с базой данных
        $pdo = Database::connection();

        // 1) Сбор и базовая санитация данных из формы
        $post = $_POST;

        /** @var string $title Заголовок задачи */
        $title       = trim($post['title'] ?? '');
        /** @var string|null $dueDate Дата выполнения задачи (YYYY-MM-DD) или null */
        $dueDate     = !empty($post['due_date']) ? trim($post['due_date']) : null;
        /** @var string $priority Приоритет задачи ('low', 'medium', 'high') */
        $priority    = $post['priority'] ?? 'medium';
        /** @var int|null $categoryId ID категории или null */
        $categoryId  = filter_var($post['category_id'] ?? 0, FILTER_VALIDATE_INT) ?: null;
        /** @var string $description Описание задачи */
        $description = trim($post['description'] ?? '');
        /** @var array<string> $tags Массив тегов */
        $tags        = is_array($post['tags'] ?? null) ? array_map('trim', $post['tags']) : [];
        $tags        = array_filter($tags, fn($tag) => $tag !== ''); // Удаляем пустые теги
        /** @var array<string> $steps Массив шагов */
        $steps       = is_array($post['steps'] ?? null) ? array_map('trim', $post['steps']) : [];
        $steps       = array_filter($steps, fn($step) => $step !== ''); // Удаляем пустые шаги

        // 2) Расширенная валидация
        /** @var array<string, string> $errors Массив ошибок валидации */
        $errors = [];

        // Валидация заголовка
        if ($title === '') {
            $errors['title'] = 'Название задачи обязательно к заполнению';
        } elseif (mb_strlen($title) > 255) {
            $errors['title'] = 'Название задачи не должно превышать 255 символов';
        }

        // Валидация категории
        if ($categoryId === null) {
            $errors['category_id'] = 'Необходимо выбрать категорию';
        } else {
            // Проверка существования категории в БД
            try {
                $stmt = $pdo->prepare('SELECT COUNT(*) FROM categories WHERE id = ?');
                $stmt->execute([$categoryId]);
                if ($stmt->fetchColumn() == 0) {
                    $errors['category_id'] = 'Выбранная категория не существует';
                }
            } catch (\PDOException $e) {
                // Логирование ошибки или обработка по-другому
                 $errors['database'] = 'Ошибка проверки категории.';
            }
        }

        // Валидация приоритета
        if (!in_array($priority, ['low', 'medium', 'high'], true)) {
            $errors['priority'] = 'Некорректный приоритет задачи';
            $priority = 'medium'; // Устанавливаем дефолтное значение при ошибке
        }

        // Валидация даты
        if ($dueDate !== null) {
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dueDate)) {
                $errors['due_date'] = 'Дата должна быть в формате YYYY-MM-DD';
            } else {
                try {
                    $date = new \DateTime($dueDate); // Используем конструктор для проверки
                    if ($date->format('Y-m-d') !== $dueDate) {
                         // Эта проверка избыточна, если preg_match прошел
                         $errors['due_date'] = 'Указана некорректная дата (ошибка формата)';
                    }

                    // Проверка на прошедшую дату (опционально)
                    $today = new \DateTime();
                    $today->setTime(0, 0, 0); // Устанавливаем время на начало дня
                    if ($date < $today) {
                        $errors['due_date'] = 'Дата не может быть в прошлом';
                    }
                } catch (\Exception $e) {
                    $errors['due_date'] = 'Указана некорректная дата (исключение)';
                }
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

        // 3) При ошибках валидации возвращаем форму с сообщениями
        if (!empty($errors)) {
            // Загружаем категории для формы (если еще не загружены или для уверенности)
            try {
                $categories = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);
            } catch (\PDOException $e) {
                // Обработка ошибки загрузки категорий
                $categories = []; // Пустой массив, чтобы шаблон не упал
                $errors['database'] = 'Ошибка загрузки категорий.';
            }

            // Рендерим шаблон 'task/create' снова, передавая ошибки и введенные значения
            View::render('task/create', [
                'errors'     => $errors,
                'oldValues'  => compact('title', 'dueDate', 'priority', 'categoryId', 'description', 'tags', 'steps'),
                'categories' => $categories
            ]);
            return; // Прерываем выполнение
        }

        // 4) Вставка в базу данных (с защитой от SQL-инъекций через подготовленные выражения)
        $stmt = $pdo->prepare(<<<SQL
            INSERT INTO tasks
                (title, due_date, priority, category_id, description, tags, steps)
            VALUES
                (:title, :due_date, :priority, :category_id, :description, :tags, :steps)
        SQL);

        // Выполнение запроса с безопасной передачей параметров
        $stmt->execute([
            ':title'       => $title,
            ':due_date'    => $dueDate,
            ':priority'    => $priority,
            ':category_id' => $categoryId,
            ':description' => $description,
            ':tags'        => json_encode($tags, JSON_UNESCAPED_UNICODE), // Кодируем массив тегов
            ':steps'       => json_encode(array_values($steps), JSON_UNESCAPED_UNICODE), // Кодируем массив шагов (сбрасываем ключи)
        ]);

        // 5) Перенаправление на главную страницу после успешного создания
        header('Location: /');
        exit;
    }
}