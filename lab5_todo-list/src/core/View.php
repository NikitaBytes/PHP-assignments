<?php
namespace Core;

/**
 * Класс View - простой рендерер шаблонов.
 *
 * Этот класс предоставляет статический метод для рендеринга шаблонов,
 * используя layout и передавая данные в шаблон.
 *
 * @package Core
 * @author ToDoList Team
 * @version 1.0
 */
final class View
{
    /**
     * Рендерит шаблон внутри базового layout.
     *
     * Извлекает данные в область видимости шаблона, подключает файл шаблона
     * и базовый layout.
     *
     * @param string $tpl Путь к шаблону относительно директории templates/ (без .php)
     * @param array<string,mixed> $data Ассоциативный массив данных, передаваемых в шаблон
     * @return void
     * @throws \RuntimeException Если шаблон не найден
     */
    public static function render(string $tpl, array $data = []): void
    {
        extract($data, EXTR_OVERWRITE);
        $contentFile = dirname(__DIR__,2)."/templates/$tpl.php";
        $layoutFile  = dirname(__DIR__,2).'/templates/layout.php';
        if (!file_exists($contentFile)) {
            throw new \RuntimeException("Template $tpl not found");
        }
        require $layoutFile;
    }
}