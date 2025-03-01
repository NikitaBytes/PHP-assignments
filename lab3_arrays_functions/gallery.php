<?php
/**
 * Этот скрипт выводит изображения из папки "image" в виде галереи.
 */
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Галерея изображений</title>
    <style>
        body {
            font-family: sans-serif;
            margin: 0;
            padding: 0;
        }
        header, nav, footer {
            background-color: #f5f5f5;
            padding: 10px 20px;
        }
        nav {
            text-align: center;
        }
        nav a {
            margin-right: 15px;
            text-decoration: none;
            color: #333;
        }
        h1, h2, h3 {
            margin: 0;
        }
        .gallery {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            padding: 10px;
        }
        .gallery img {
            max-width: 180px;
            margin: 5px;
            border: 2px solid #ccc;
        }
        .gallery img:hover {
            border-color: #999;
        }
        footer {
            text-align: center;
            color: #777;
        }
    </style>
</head>
<body>

<header>
    <h1>My Image Gallery</h1>
</header>

<nav>
    <a href="#">About Cats</a> |
    <a href="#">News</a> |
    <a href="#">Contacts</a>
</nav>
<h1>#cats</h1>
<main>
    <div class="gallery">
        <?php
        // Задаем путь к папке с изображениями
        $dir = 'image/';

        // Сканируем содержимое директории
        $files = scandir($dir);
        if ($files === false) {
            echo "<p>Не удалось открыть папку с изображениями.</p>";
        } else {
            foreach ($files as $file) {
                // Пропускаем служебные каталоги . и ..
                if ($file !== '.' && $file !== '..') {
                    // Формируем полный путь к файлу
                    $path = $dir . $file;
                    // Проверяем, что файл имеет расширение .jpg (или .jpeg)
                    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                    if ($ext === 'jpg' || $ext === 'jpeg') {
                        echo "<img src=\"$path\" alt=\"Image\">";
                    }
                }
            }
        }
        ?>
    </div>
</main>

<footer>
    <p>© 2025 My Awesome Gallery</p>
</footer>

</body>
</html>