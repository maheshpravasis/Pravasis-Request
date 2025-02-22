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
    <title>Admin Dashboard</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 0;
            margin: 0;
            background-color: #f8f9fa;
            color: #333;
        }
        
        .container {
            max-width: 800px;
            margin: 40px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .dashboard-header {
            text-align: center;
            margin-bottom: 40px;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 20px;
        }
        
        .dashboard-header h2 {
            font-size: 28px;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        .dashboard-cards {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .card {
            background: #fff;
            border-radius: 10px;
            padding: 25px;
            width: 250px;
            text-align: center;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            transition: transform 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .card img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 15px;
            border: 4px solid #f0f0f0;
        }
        
        .button {
            display: inline-block;
            width: 80%;
            background: #4CAF50;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            text-align: center;
            margin-top: 15px;
        }
        
        .button:hover {
            opacity: 0.9;
            transform: scale(1.03);
        }
        
        .button.red { background: #e74c3c; }
        .button.blue { background: #3498db; }
        
        #requestsTable {
            margin-top: 30px;
            width: 100%;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
        }
        
        th {
            background-color: #34495e;
            color: white;
            font-weight: 500;
        }
        
        tr:nth-child(even) {
            background-color: #f5f5f5;
        }
        
        tr:hover {
            background-color: #f0f0f0;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .container {
                padding: 20px;
                margin: 20px;
            }
            
            .card {
                width: 100%;
                margin-bottom: 20px;
            }
        }
            .back-button {
                left: 20px;
                top: 20px;
                display: flex;
                align-items: center;
                gap: 5px;
                padding: 8px 15px;
                background: #e9ecef;
                border: 1px solid #dee2e6;
                border-radius: 5px;
                color: #333;
                text-decoration: none;
                transition: all 0.2s ease;
                width: 65px; 33b249

        }

.back-button:hover {
    background: #5dbea3;
    transform: translateX(-3px);
}

.container {
    position: relative;  /* Add this line to your existing container class */
}
        
    </style>
</head>
<body>
    <div class="container"> 
    <a href="index.html" class="back-button">
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="m12 19-7-7 7-7"/>
        <path d="M19 12H5"/>
    </svg>
    Back
</a>     
        <div class="dashboard-header">
            <h2>Admin Dashboard</h2>
            <p>Welcome to the administrative control panel</p>
        </div>
        <div class="dashboard-cards">
            <div class="card">
                <img src="LOGO/user.png" alt="User management">
                <h3>User Management</h3>
                <a href="create_user.php" class="button">Create User</a>
            </div>
            
            <div class="card">
                <img src="LOGO/request.jpg" alt="Request management">
                <h3>Request Management</h3>
                <a href="show_requests.php" class="button blue" onclick="loadRequests()">Show Requests</a>
            </div>
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