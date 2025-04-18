<?php
/**
 * Файл вспомогательных функций.
 *
 * Содержит функции, используемые в различных частях приложения.
 *
 * @author ToDoList Team
 * @version 1.0
 */
declare(strict_types=1);

/**
 * Возвращает информацию о дедлайне: класс CSS и сообщение.
 *
 * Функция определяет класс CSS и сообщение для отображения информации о дедлайне задачи.
 * Использует старый алгоритм из файловой версии приложения.
 *
 * @param string|null $dueDate Дата дедлайна в формате YYYY-MM-DD или null, если дата не указана
 * @return array{class:string,message:string} Ассоциативный массив, содержащий класс CSS и сообщение о дедлайне
 */
function getDeadlineInfo(?string $dueDate): array
{
    if (!$dueDate) {
        return ['class' => 'deadline-none', 'message' => 'Дата не указана'];
    }

    $today    = new DateTimeImmutable('today');
    $deadline = DateTimeImmutable::createFromFormat('Y-m-d', $dueDate);

    if (!$deadline) {
        return ['class' => 'deadline-none', 'message' => 'Дата недействительна'];
    }

    $diff = (int) $today->diff($deadline)->format('%r%a');

    return match (true) {
        $diff < 0               => ['class' => 'deadline-overdue',  'message' => "Просрочено на " . abs($diff) . " дн."],
        $diff === 0             => ['class' => 'deadline-critical', 'message' => "Дедлайн сегодня!"],
        $diff <= 5              => ['class' => 'deadline-critical', 'message' => "Осталось $diff дн."],
        default                 => ['class' => 'deadline-normal',   'message' => "Осталось $diff дн."],
    };
}