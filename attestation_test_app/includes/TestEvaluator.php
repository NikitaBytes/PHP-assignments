<?php

/**
 * Class TestEvaluator
 *
 * Осуществляет валидацию ответов пользователя и подсчет результатов теста.
 * Подсчет осуществляется для вопросов с одним или несколькими правильными ответами.
 *
 * @package TestApp\Test
 */
class TestEvaluator
{
    /**
     * Оценивает ответы пользователя.
     *
     * @param array $questions Массив вопросов теста.
     * @param array $answers Массив ответов пользователя (например, $_POST).
     *
     * @return array Ассоциативный массив с результатами:
     *               - 'correctCount' (int): Количество правильно отвеченных вопросов.
     *               - 'totalQuestions' (int): Общее число вопросов.
     *               - 'scorePercent' (int): Процент набранных баллов.
     */
    public function evaluate(array $questions, array $answers): array
    {
        $correctCount = 0;
        $totalQuestions = count($questions);

        foreach ($questions as $index => $question) {
            $qKey = 'q' . $index;

            if ($question['type'] === 'single') {
                // Для вопросов с одним правильным ответом ожидается строка
                $userAnswer = $answers[$qKey] ?? null;
                if ($userAnswer !== null) {
                    foreach ($question['options'] as $option) {
                        if ($option['correct'] && $option['text'] === $userAnswer) {
                            $correctCount++;
                            break;
                        }
                    }
                }
            } elseif ($question['type'] === 'multiple') {
                // Для вопросов с несколькими правильными ответами ожидается массив
                $userAnswers = $answers[$qKey] ?? [];
                if (is_array($userAnswers)) {
                    $allCorrect = true;
                    foreach ($question['options'] as $option) {
                        if ($option['correct']) {
                            // Правильный ответ должен присутствовать в массиве ответов
                            if (!in_array($option['text'], $userAnswers, true)) {
                                $allCorrect = false;
                                break;
                            }
                        } else {
                            // Неправильный ответ не должен присутствовать
                            if (in_array($option['text'], $userAnswers, true)) {
                                $allCorrect = false;
                                break;
                            }
                        }
                    }
                    if ($allCorrect) {
                        $correctCount++;
                    }
                }
            }
        }

        // Вычисляем процент набранных баллов
        $scorePercent = $totalQuestions > 0 ? (int) round(($correctCount / $totalQuestions) * 100) : 0;

        return [
            'correctCount'   => $correctCount,
            'totalQuestions' => $totalQuestions,
            'scorePercent'   => $scorePercent,
        ];
    }
}