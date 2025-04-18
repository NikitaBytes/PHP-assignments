<?php
/**
 * Файл инициализации приложения.
 *
 * Выполняет следующие действия:
 * 1. Подключает автозагрузчик Composer.
 * 2. Загружает переменные окружения из файла .env (если он существует).
 * 3. Устанавливает внутреннюю кодировку в UTF-8.
 * 4. Подключает необходимые классы.
 * 5. Регистрирует маршруты приложения.
 *
 * @author ToDoList Team
 * @version 1.0
 */
declare(strict_types=1);

// 1) Composer‑автозагрузчик
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers.php';   // ✅ чтобы функции были везде

use Dotenv\Dotenv;
use Core\Router;
use Core\Database;
use Core\View;

// 2) Загрузка .env (если есть)
$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

// 3) Внутренняя кодировка
mb_internal_encoding('UTF-8');

// 4) Подключаем необходимые классы
use Handlers\Task\Index;
use Handlers\Task\ListTasks;
use Handlers\Task\Show;
use Handlers\Task\CreateForm;
use Handlers\Task\Create;
use Handlers\Task\Toggle;
use Handlers\Task\Delete;
use Handlers\Task;

// 5) Регистрация маршрутов
// Главная — последние 2 задачи
/**
 * Регистрирует маршрут для главной страницы.
 *
 * @uses Handlers\Task\Index::handle()
 */
Router::get('/',            fn() => (new Index())->handle());

// Все задачи с фильтрами и пагинацией
/**
 * Регистрирует маршрут для страницы со списком задач.
 *
 * @uses Handlers\Task\ListTasks::handle()
 */
Router::get('/task/index',  fn() => (new ListTasks())->handle());

// Показ одной задачи
/**
 * Регистрирует маршрут для отображения одной задачи.
 *
 * @uses Handlers\Task\Show::handle()
 */
Router::get('/task/show',   fn() => (new Show())->handle());

// Форма создания задачи
/**
 * Регистрирует маршрут для отображения формы создания задачи.
 *
 * Загружает список категорий из базы данных и передает их в шаблон.
 */
Router::get('/task/create', function() {
    $pdo = Database::connection();
    $categories = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();
    View::render('task/create', [
        'errors'     => [],
        'oldValues'  => [
            'title' => '',
            'description' => '',
            'due_date' => '',
            'priority' => 'medium',
            'category_id' => null,
            'tags' => [],
            'steps' => ['']
        ],
        'categories' => $categories,
    ]);
});

// Обработка отправки формы создания
/**
 * Регистрирует маршрут для обработки отправки формы создания задачи.
 *
 * @uses Handlers\Task\Create::handle()
 */
Router::post('/task/create',fn() => (new Create())->handle());

// AJAX: переключение статуса «выполнено/не выполнено»
/**
 * Регистрирует маршрут для AJAX-запроса на переключение статуса задачи.
 *
 * @uses Handlers\Task\Toggle::handle()
 */
Router::post('/task/toggle',fn() => (new Toggle())->handle());

// POST‑форма: удаление задачи
/**
 * Регистрирует маршрут для удаления задачи.
 *
 * @uses Handlers\Task\Delete::handle()
 */
Router::post('/task/delete',fn() => (new Delete())->handle());

// NEW: редактирование
/**
 * Регистрирует маршрут для отображения формы редактирования задачи.
 *
 * @uses Handlers\Task\EditForm::handle()
 */
Router::get ('/task/edit',  fn () => (new Task\EditForm())->handle()); // GET‑форма
/**
 * Регистрирует маршрут для обработки отправки формы редактирования задачи.
 *
 * @uses Handlers\Task\Edit::handle()
 */
Router::post('/task/edit',  fn () => (new Task\Edit())->handle());     // POST‑update
