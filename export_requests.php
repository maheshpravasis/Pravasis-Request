<?php
session_start();
require_once 'config.php';

// Check if user is logged in as admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('HTTP/1.1 403 Forbidden');
    exit('Access denied');
}

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="requests_export_' . date('Y-m-d') . '.csv"');

// Create output stream
$output = fopen('php://output', 'w');

// Add UTF-8 BOM for proper Excel encoding
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Write CSV header
fputcsv($output, [
    'Request ID',
    'User Name',
    'Designation',
    'Email',
    'Request Type',
    'Description',
    'Status',
    'Created Date',
    'Last Updated'
]);

try {
    // Prepare and execute the query
    $sql = "SELECT 
                r.id,
                u.name,
                u.designation,
                u.email,
                r.request_type,
                r.description,
                r.status,
                r.created_at,
                r.updated_at
            FROM requests r
            JOIN users u ON r.user_id = u.id
            ORDER BY r.created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    // Write each row to CSV
    while ($row = $stmt->fetch()) {
        // Format dates
        $row['created_at'] = date('Y-m-d H:i:s', strtotime($row['created_at']));
        $row['updated_at'] = $row['updated_at'] ? date('Y-m-d H:i:s', strtotime($row['updated_at'])) : 'N/A';
        
        // Capitalize status
        $row['status'] = ucfirst($row['status']);
        
        fputcsv($output, [
            $row['id'],
            $row['name'],
            $row['designation'],
            $row['email'],
            $row['request_type'],
            $row['description'],
            $row['status'],
            $row['created_at'],
            $row['updated_at']
        ]);
    }

} catch (PDOException $e) {
    // Log error (in a production environment, use proper error logging)
    error_log("CSV Export Error: " . $e->getMessage());
    header('HTTP/1.1 500 Internal Server Error');
    exit('Error generating CSV file');
}

// Close the output stream
fclose($output);
?>