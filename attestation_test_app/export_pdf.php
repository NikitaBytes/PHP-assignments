<?php
declare(strict_types=1);

/**
 * Экспорт результатов теста в PDF.
 *
 * Доступен только авторизованным пользователям.
 * Используется библиотека FPDF http://www.fpdf.org/ 
 *
 * @package TestApp\Export
 */

session_start();

if (empty($_SESSION['admin'])) {
    header('Location: admin_login.php');
    exit;
}

require_once 'includes/DataManager.php';
require_once 'fpdf/fpdf.php';

$dataManager = new DataManager();
$results = $dataManager->loadResults();

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'Test Results', 0, 1, 'C');

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(50, 10, 'Username', 1);
$pdf->Cell(40, 10, 'Correct', 1);
$pdf->Cell(30, 10, 'Percent', 1); 
$pdf->Cell(50, 10, 'Date', 1);
$pdf->Ln();

$pdf->SetFont('Arial', '', 12);
if (!empty($results)) {
    foreach ($results as $result) {
        $username = $result['username'] ?? '';
        $correct  = (int) ($result['correct_answers'] ?? 0);
        $total    = (int) ($result['total'] ?? 0);
        $score    = $result['score'] ?? 0;
        $date     = $result['date'] ?? '';

        $pdf->Cell(50, 10, $username, 1);
        $pdf->Cell(40, 10, sprintf('%d / %d', $correct, $total), 1);
        $pdf->Cell(30, 10, $score . '%', 1);
        $pdf->Cell(50, 10, $date, 1);
        $pdf->Ln();
    }
}

$pdf->Output('D', 'results.pdf');