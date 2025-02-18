<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: index.html');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title padding=center>Admin Dashboard</title>
    <style>
        .container div {
            text-align: center;
        }
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .button {
            background: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 5px;
        }
        .button.red { background: #f44336; }
        .button.blue { background: #2196F3; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #f5f5f5;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Admin Dashboard</h2>
        <div>
            <a href="create_user.php" class="button">Create User</a>
            <a href="show_requests.php" class="button blue" onclick="loadRequests()">Show Requests</a>
        </div>
        
        <div id="requestsTable"></div>
    </div>

    <script>
        function loadRequests() {
            fetch('get_requests.php')
                .then(response => response.text())
                .then(data => {
                    document.getElementById('requestsTable').innerHTML = data;
                });
        }

        function updateStatus(requestId, status) {
            fetch('update_request.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `request_id=${requestId}&status=${status}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Status updated successfully');
                    loadRequests();
                }
            });
        }

        function deleteRequest(requestId) {
            if (confirm('Are you sure you want to delete this request?')) {
                fetch('delete_request.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `request_id=${requestId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Request deleted successfully');
                        loadRequests();
                    }
                });
            }
        }
    </script>
</body>
</html>