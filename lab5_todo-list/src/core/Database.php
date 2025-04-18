<?php
namespace Core;

use PDO;
use PDOException;

/**
 * Класс Database - простой PDO синглтон.
 *
 * Этот класс обеспечивает единственное соединение с базой данных
 * через паттерн Singleton.
 *
 * @package Core
 * @author ToDoList Team
 * @version 1.0
 */
final class Database
{
    /** @var PDO|null $pdo Статическое свойство для хранения экземпляра PDO */
    private static ?PDO $pdo = null;

    /**
     * Возвращает экземпляр PDO.
     *
     * Если экземпляр не существует, создает новое соединение с базой данных,
     * используя параметры из конфигурационного файла.
     *
     * @return PDO Объект PDO для работы с базой данных
     * @throws PDOException При ошибке подключения к базе данных
     */
    public static function connection(): PDO
    {
        if (self::$pdo) {
            return self::$pdo;
        }
        $cfg = require dirname(__DIR__,2).'/config/db.php';

        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $cfg['host'], $cfg['port'], $cfg['name'], $cfg['charset']
        );

        self::$pdo = new PDO($dsn, $cfg['user'], $cfg['pass'], [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);

        return self::$pdo;
    }

    /**
     * Запрещает создание экземпляров класса.
     *
     * @codeCoverageIgnore
     */
    private function __construct() {}
}