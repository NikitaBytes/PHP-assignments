<?php
namespace Core;

/**
 * Класс Router - минималистичный маршрутизатор HTTP-запросов.
 *
 * Этот класс предоставляет статические методы для регистрации маршрутов
 * и диспетчеризации запросов к соответствующим обработчикам.
 *
 * @package Core
 * @author ToDoList Team
 * @version 1.0
 */
final class Router
{
    /** @var array<string, callable> $map Ассоциативный массив маршрутов, где ключ - метод + путь, значение - обработчик */
    private static array $map = [];

    /**
     * Регистрирует GET-маршрут.
     *
     * @param string $p Путь маршрута
     * @param callable $h Обработчик маршрута
     * @return void
     */
    public static function get (string $p, callable $h): void { self::$map['GET'.self::n($p)] = $h; }

    /**
     * Регистрирует POST-маршрут.
     *
     * @param string $p Путь маршрута
     * @param callable $h Обработчик маршрута
     * @return void
     */
    public static function post(string $p, callable $h): void { self::$map['POST'.self::n($p)] = $h; }

    /**
     * Выполняет диспетчеризацию запроса.
     *
     * Определяет обработчик для заданного метода и URI и вызывает его.
     * Если маршрут не найден, возвращает код 404.
     *
     * @param string $m HTTP-метод запроса (GET, POST и т.д.)
     * @param string $u URI запроса
     * @return void
     */
    public static function dispatch(string $m, string $u): void
    {
        $k = $m.self::n(parse_url($u, PHP_URL_PATH) ?? '/');
        if (!isset(self::$map[$k])) {
            http_response_code(404);
            echo '404 Not Found'; return;
        }
        (self::$map[$k])();
    }

    /**
     * Нормализует путь маршрута.
     *
     * Убирает лишние пробелы и слеши.
     *
     * @param string $p Путь для нормализации
     * @return string Нормализованный путь
     */
    private static function n(string $p): string { return ' '.($p==='/'?'/':rtrim($p,'/')); }
}