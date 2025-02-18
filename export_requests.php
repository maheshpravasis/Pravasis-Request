<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    exit('Unauthorized');
}

// Get all parameters from the URL (same as get_requests.php)
$type = $_GET['type'] ?? 'all';
$dateFrom = $_GET['dateFrom'] ?? '';
$dateTo = $_GET['dateTo'] ?? '';
$requestType = $_GET['requestType'] ?? '';
$leaveType = $_GET['leaveType'] ?? '';
$search = $_GET['search'] ?? '';

// Build the SQL query (same as get_requests.php)
$sql = "SELECT r.*, u.name, u.designation, u.email 
        FROM requests r 
        JOIN users u ON r.user_id = u.id
        WHERE 1=1";

$params = [];

// Add all the same WHERE clauses as in get_requests.php
// ... (same filtering logic)

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Set headers for Excel download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="requests_export_' . date('Y-m-d') . '.csv"');

// Create output stream
$output = fopen('php://output', 'w');

// Add headers
fputcsv($output, [
    'Employee Name',
    'Designation',
    'Email',
    'Request Type',
    'Leave Type',
    'From Date',
    'To Date',
    'Status',
    'Reason',
    'Submitted On'
]);

// Add data rows
foreach ($requests as $request) {
    fputcsv($output, [
        $request['name'],
        $request['designation'],
        $request['email'],
        $request['request_type'],
        $request['leave_type'],
        $request['from_date'],
        $request['to_date'],
        $request['status'] === 'unread' ? 'Pending' : ucfirst($request['status']),
        $request['reason'],
        $request['created_at']
    ]);
}

fclose($output);
exit();
?>