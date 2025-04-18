<?php
/**
 * Шаблон: базовый макет страницы.
 *
 * Этот шаблон определяет структуру HTML-страницы, включая doctype,
 * мета-теги, заголовок, подключение стилей и основной контейнер для контента.
 * Внутри контейнера подключается файл с контентом, специфичным для каждой страницы.
 *
 * @var string $contentFile Путь к файлу с контентом страницы (подключается через require).
 */
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="utf-8">
<title>To‑Do List</title>
<link rel="stylesheet" href="/css/style.css">
</head><body>
<main class="container"><?php require $contentFile; ?></main>
</body></html>