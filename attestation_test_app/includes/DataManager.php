<?php

/**
 * Class DataManager
 *
 * Предоставляет методы для загрузки вопросов теста и чтения/записи результатов.
 * Сохраняет данные в JSON-файлы.
 *
 * @package TestApp\Data
 */
class DataManager
{
    /**
     * Путь к файлу с вопросами.
     *
     * @var string
     */
    private string $questionsFile;

    /**
     * Путь к файлу с результатами.
     *
     * @var string
     */
    private string $resultsFile;

    /**
     * Конструктор.
     *
     * @param string $questionsFile Путь к JSON-файлу с вопросами (по умолчанию "questions.json").
     * @param string $resultsFile   Путь к JSON-файлу с результатами (по умолчанию "results.json").
     */
    public function __construct(string $questionsFile = 'questions.json', string $resultsFile = 'results.json')
    {
        $this->questionsFile = $questionsFile;
        $this->resultsFile   = $resultsFile;
    }

    /**
     * Загружает вопросы теста из JSON-файла.
     *
     * @return array|null Вернёт ассоциативный массив с вопросами или null при ошибке/отсутствии файла.
     */
    public function loadQuestions(): ?array
    {
        if (!file_exists($this->questionsFile)) {
            return null;
        }

        $jsonContent = file_get_contents($this->questionsFile);
        if ($jsonContent === false) {
            // Не удалось прочитать файл
            return null;
        }

        $data = json_decode($jsonContent, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            // Файл поврежден или содержит некорректный JSON
            return null;
        }

        return $data;
    }

    /**
     * Загружает результаты тестов из JSON-файла.
     *
     * Если файл отсутствует или пуст, создаёт его с пустым массивом results.
     *
     * @return array Массив результатов (каждый элемент — это массив с данными о прохождении теста).
     */
    public function loadResults(): array
    {
        if (!file_exists($this->resultsFile)) {
            // Если файла нет, создаём пустую структуру
            $this->initializeResultsFile();
        }

        $jsonContent = file_get_contents($this->resultsFile);
        if ($jsonContent === false) {
            // В случае ошибки чтения вернём пустой массив
            return [];
        }

        $data = json_decode($jsonContent, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            // Если JSON некорректен, переинициализируем файл
            $this->initializeResultsFile();
            return [];
        }

        return $data['results'] ?? [];
    }

    /**
     * Сохраняет новый результат теста, добавляя его в конец массива results.
     *
     * @param array $result Ассоциативный массив с данными результата (username, score, correct_answers и т.д.).
     *
     * @return bool Возвращает true при успешной записи в файл, иначе false.
     */
    public function saveResult(array $result): bool
    {
        $results = $this->loadResults();
        $results[] = $result;

        $data = ['results' => $results];
        $jsonData = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        if ($jsonData === false) {
            // Ошибка при сериализации JSON
            return false;
        }

        // Запись с блокировкой (LOCK_EX) для избежания конфликтов при одновременной записи
        return file_put_contents($this->resultsFile, $jsonData, LOCK_EX) !== false;
    }

    /**
     * Инициализирует файл результатов пустым массивом results.
     *
     * @return void
     */
    private function initializeResultsFile(): void
    {
        $initialData = ['results' => []];
        $jsonData = json_encode($initialData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        // Игнорируем возможную ошибку записи — если файл не создался,
        // при следующей попытке loadResults() произойдёт повторная инициализация.
        @file_put_contents($this->resultsFile, $jsonData, LOCK_EX);
    }
}