<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.html');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get form data
        $userId = $_SESSION['user_id'];
        $requestType = $_POST['request_type'];
        $leaveType = ($requestType === 'Leave Request') ? $_POST['leave_type'] : null;
        $reason = $_POST['reason'];
        $fromDate = $_POST['from_date'];
        $toDate = $_POST['to_date'];

        // Validate dates
        $fromDateTime = new DateTime($fromDate);
        $toDateTime = new DateTime($toDate);

        if ($toDateTime < $fromDateTime) {
            throw new Exception("End date cannot be earlier than start date");
        }

        // Prepare SQL statement
        $sql = "INSERT INTO requests (user_id, request_type, leave_type, reason, from_date, to_date, status) 
                VALUES (?, ?, ?, ?, ?, ?, 'unread')";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $userId,
            $requestType,
            $leaveType,
            $reason,
            $fromDate,
            $toDate
        ]);

        // Get admin email for notification
        $adminStmt = $pdo->prepare("SELECT email FROM users WHERE user_type = 'admin' LIMIT 1");
        $adminStmt->execute();
        $adminEmail = $adminStmt->fetchColumn();

        // Get user details for the email
        $userStmt = $pdo->prepare("SELECT name, email FROM users WHERE id = ?");
        $userStmt->execute([$userId]);
        $user = $userStmt->fetch();

        // Send email notification to admin
        if ($adminEmail) {
            $subject = "New Request Submission";
            $message = "A new " . $requestType . " request has been submitted by " . $user['name'] . ".\n\n";
            $message .= "Details:\n";
            $message .= "Type: " . $requestType . "\n";
            if ($leaveType) {
                $message .= "Leave Type: " . $leaveType . "\n";
            }
            $message .= "From: " . $fromDate . "\n";
            $message .= "To: " . $toDate . "\n";
            $message .= "Reason: " . $reason . "\n";

            $headers = "From: " . $user['email'] . "\r\n";
            $headers .= "Reply-To: " . $user['email'] . "\r\n";
            $headers .= "X-Mailer: PHP/" . phpversion();

            mail($adminEmail, $subject, $message, $headers);
        }

        // Return success response
        $response = [
            'success' => true,
            'message' => 'Request submitted successfully',
            'redirect' => 'request_form.php?success=1'
        ];
        echo json_encode($response);
        exit();

    } catch (Exception $e) {
        // Return error response
        $response = [
            'success' => false,
            'message' => $e->getMessage(),
            'redirect' => 'request_form.php?error=1'
        ];
        echo json_encode($response);
        exit();
    }
}

// If not POST request, redirect to form
header('Location: request_form.php');
exit();
?> 