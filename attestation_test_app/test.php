<?php
declare(strict_types=1);

/**
 * Страница прохождения теста.
 *
 * При GET-запросе отображается форма с вопросами.
 * При POST-запросе производится валидация, подсчет результатов,
 * сохранение результата и перенаправление на страницу с индивидуальным результатом.
 *
 * @package TestApp
 */

session_start();

require_once 'includes/DataManager.php';
require_once 'includes/TestEvaluator.php';

$dataManager = new DataManager();
$questionsData = $dataManager->loadQuestions();

if ($questionsData === null || !isset($questionsData['questions'])) {
    die("Ошибка загрузки вопросов теста.");
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получение и очистка имени пользователя
    $username = trim(filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING) ?? '');
    if (empty($username)) {
        $error = "Введите ваше имя!";
    }

    // Дополнительная серверная валидация ответов
    if (empty($error)) {
        foreach ($questionsData['questions'] as $index => $question) {
            $qKey = 'q' . $index;
            // Собираем список допустимых ответов для данного вопроса
            $allowedAnswers = array_map(
                fn($opt) => $opt['text'],
                $question['options']
            );
            if ($question['type'] === 'single') {
                // Ожидается единственный ответ
                $userAnswer = $_POST[$qKey] ?? null;
                if ($userAnswer === null || !in_array($userAnswer, $allowedAnswers, true)) {
                    $error = "Некорректный ответ на вопрос " . ($index + 1);
                    break;
                }
            } elseif ($question['type'] === 'multiple') {
                // Ожидается массив ответов
                if (!isset($_POST[$qKey]) || !is_array($_POST[$qKey])) {
                    $error = "Некорректный формат ответа на вопрос " . ($index + 1);
                    break;
                }
                foreach ($_POST[$qKey] as $ans) {
                    if (!in_array($ans, $allowedAnswers, true)) {
                        $error = "Некорректный ответ на вопрос " . ($index + 1);
                        break 2; // Прерываем оба цикла
                    }
                }
            }
        }
    }

    if (empty($error)) {
        $evaluator = new TestEvaluator();
        $evaluation = $evaluator->evaluate($questionsData['questions'], $_POST);

        $result = [
            'username'        => htmlspecialchars($username, ENT_QUOTES, 'UTF-8'),
            'score'           => $evaluation['scorePercent'],
            'correct_answers' => $evaluation['correctCount'],
            'total'           => $evaluation['totalQuestions'],
            'date'            => date('Y-m-d H:i:s'),
        ];

        // Сохранение результата
        $dataManager->saveResult($result);

        // Сохранение результата в сессии для вывода индивидуального результата
        $_SESSION['result'] = $result;
        header('Location: result.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Прохождение теста</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>Пройдите тест</h1>
    <?php if (!empty($error)): ?>
        <div class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
    <form method="POST" action="test.php">
        <div class="form-group">
            <label for="username">Введите ваше имя:</label>
            <input type="text" id="username" name="username" required autofocus>
        </div>
        <?php foreach ($questionsData['questions'] as $index => $question):
            $qKey = 'q' . $index;
        ?>
            <div class="question">
                <p>
                    <strong>
                        <?= sprintf("%d. %s", $index + 1, htmlspecialchars($question['text'], ENT_QUOTES, 'UTF-8')) ?>
                    </strong>
                </p>
                <?php if ($question['type'] === 'single'): ?>
                    <?php foreach ($question['options'] as $option): ?>
                        <label>
                            <input type="radio" name="<?= $qKey ?>" 
                                   value="<?= htmlspecialchars($option['text'], ENT_QUOTES, 'UTF-8') ?>" required>
                            <?= htmlspecialchars($option['text'], ENT_QUOTES, 'UTF-8') ?>
                        </label><br>
                    <?php endforeach; ?>
                <?php elseif ($question['type'] === 'multiple'): ?>
                    <?php foreach ($question['options'] as $option): ?>
                        <label>
                            <input type="checkbox" name="<?= $qKey ?>[]" 
                                   value="<?= htmlspecialchars($option['text'], ENT_QUOTES, 'UTF-8') ?>">
                            <?= htmlspecialchars($option['text'], ENT_QUOTES, 'UTF-8') ?>
                        </label><br>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        <button class="btn" type="submit">Отправить</button>
    </form>
</div>
</body>
</html>