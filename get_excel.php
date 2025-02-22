<?php
session_start();
require_once 'config.php';
require 'vendor/autoload.php'; // You'll need to install PhpSpreadsheet using Composer

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Check if user is logged in as admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: index.html');
    exit();
}

// Get request type from URL
$type = $_GET['type'] ?? 'all';

try {
    // Prepare the SQL query based on type
    $sql = "SELECT 
                u.name as 'Employee Name',
                r.request_type as 'Request Type',
                DATE_FORMAT(r.from_date, '%d-%m-%Y') as 'From Date',
                DATE_FORMAT(r.to_date, '%d-%m-%Y') as 'To Date',
                CASE 
                    WHEN r.status = 'unread' THEN 'Pending'
                    WHEN r.status = 'approved' THEN 'Approved'
                    ELSE 'Rejected'
                END as 'Status',
                r.reason as 'Reason',
                CASE 
                    WHEN r.status = 'unread' THEN 'Approve/Reject'
                    ELSE 'Delete'
                END as 'Action'
            FROM requests r 
            JOIN users u ON r.user_id = u.id";

    if ($type === 'pending') {
        $sql .= " WHERE r.status = 'unread'";
    } elseif ($type === 'approved') {
        $sql .= " WHERE r.status = 'approved'";
    } elseif ($type === 'rejected') {
        $sql .= " WHERE r.status = 'rejected'";
    }

    $sql .= " ORDER BY r.created_at DESC";

    // Execute query
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Check if we have any results
    if (empty($results)) {
        die("No data found to export.");
    }

    // Create new Spreadsheet object
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Requests');

    // Set headers
    $columns = array_keys($results[0]);
    $col = 'A';
    foreach ($columns as $column) {
        $sheet->setCellValue($col . '1', $column);
        // Set auto width for all columns except Action
        if ($column !== 'Action') {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        } else {
            // Set static width for Action column (width in characters)
            $sheet->getColumnDimension($col)->setWidth(15);
        }
        $col++;
    }

    // Add data
    $row = 2;
    foreach ($results as $result) {
        $col = 'A';
        foreach ($result as $value) {
            $sheet->setCellValue($col . $row, $value);
            $col++;
        }
        $row++;
    }

    // Style the header row
    $lastCol = chr(ord('A') + count($columns) - 1);
    $headerStyle = [
        'font' => [
            'bold' => true,
            'color' => ['rgb' => '000000'],
        ],
        'fill' => [
            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            'startColor' => [
                'rgb' => 'E0E0E0',
            ],
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
            ],
        ],
        'alignment' => [
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
        ],
    ];
    $sheet->getStyle("A1:{$lastCol}1")->applyFromArray($headerStyle);

    // Style data cells
    $dataStyle = [
        'borders' => [
            'allBorders' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
            ],
        ],
        'alignment' => [
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
        ],
    ];
    $sheet->getStyle("A2:{$lastCol}" . ($row-1))->applyFromArray($dataStyle);

    // Set column widths
    foreach(range('A', $lastCol) as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    // Set headers for download
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $type . '_requests_' . date('Y-m-d') . '.xlsx"');
    header('Cache-Control: max-age=0');

    // Create Excel file
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;

} catch (Exception $e) {
    die("Error occurred while exporting data: " . $e->getMessage());
} 