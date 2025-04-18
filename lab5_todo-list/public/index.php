<?php
/**
 * Главный файл, точка входа в приложение.
 *
 * Этот файл выполняет следующие действия:
 * 1. Объявляет строгий режим типов.
 * 2. Подключает автозагрузчик Composer.
 * 3. Подключает файл bootstrap.php для инициализации приложения.
 * 4. Вызывает диспетчер маршрутов для обработки текущего запроса.
 *
 * @author ToDoList Team
 * @version 1.0
 */
declare(strict_types=1);

// Подключаем автозагрузчик Composer
require_once dirname(__DIR__).'/vendor/autoload.php';

// Подключаем файл инициализации приложения (bootstrap)
require_once dirname(__DIR__).'/src/bootstrap.php';

use Core\Router;

/**
 * Вызываем диспетчер маршрутов для обработки текущего HTTP-запроса.
 *
 * Получаем метод запроса (GET, POST и т.д.) и URI из суперглобального массива $_SERVER
 * и передаем их в статический метод dispatch класса Router.
 */
Router::dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);