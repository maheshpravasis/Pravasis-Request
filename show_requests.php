<?php
session_start();
require_once 'config.php';

// Check if user is logged in as admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: index.html');
    exit();
}

// Function to get requests by status
function getRequests($pdo, $status = null) {
    $sql = "SELECT r.*, u.name, u.designation, u.email 
            FROM requests r 
            JOIN users u ON r.user_id = u.id";
    
    if ($status === 'pending') {
        $sql .= " WHERE r.status = 'unread'";
    } elseif ($status === 'approved') {
        $sql .= " WHERE r.status = 'approved'";
    } elseif ($status === 'rejected') {
        $sql .= " WHERE r.status = 'rejected'";
    }
    
    $sql .= " ORDER BY r.created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Handle request deletion
if (isset($_POST['delete_request'])) {
    $requestId = $_POST['request_id'];
    $stmt = $pdo->prepare("DELETE FROM requests WHERE id = ?");
    $stmt->execute([$requestId]);
}

// Handle status updates
if (isset($_POST['update_status'])) {
    $requestId = $_POST['request_id'];
    $newStatus = $_POST['new_status'];
    $stmt = $pdo->prepare("UPDATE requests SET status = ? WHERE id = ?");
    $stmt->execute([$newStatus, $requestId]);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Management - Pravasis Request System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .section {
            background: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            display: none;
        }
        .section.active {
            display: block;
        }
        .btn-container {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .tab-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            background-color: #2196F3;
            color: white;
            font-size: 16px;
        }
        .tab-btn:hover {
            background-color: #1976D2;
        }
        .tab-btn.active {
            background-color: #1565C0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #f5f5f5;
        }
        .btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            color: white;
            margin: 2px;
            min-width: 150px;
            min-height: 20px;
            padding: 10px 20px;
            background-color:rgb(236, 106, 0);
        }
        .btn:hover {
            background-color:rgb(131, 72, 4);   
            
        }
        .btn-approve { background-color: #4CAF50; }
        .btn-reject { background-color: #f44336; }
        .btn-delete { background-color: #ff9800; }
        .btn-approve:hover { background-color:rgb(2, 221, 13); }
        .btn-reject:hover { background-color:rgb(236, 38, 12); }
        .btn-delete:hover { background-color:rgb(213, 247, 62); }
        .back-button {
            background:rgb(243, 33, 33);
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 20px;
            position: absolute;
            top: 20px;
            right: 20px;
        }
        .back-button:hover {
            background-color:rgb(69, 153, 0);
        }
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            color: white;
            font-size: 0.9em;
        }
        .status-pending { background-color: #ff9800; }
        .status-approved { background-color: #4CAF50; }
        .status-rejected { background-color: #f44336; }
    </style>
</head>
<body>
    <div class="container">
        <a href="admin_dashboard.php" class="back-button">← Back to Dashboard</a>

        <div class="btn-container">
            <button id="pending_btn" class="tab-btn" onclick="showSection('pending')">Pending Requests</button>
            <button id="rejected_btn" class="tab-btn" onclick="showSection('rejected')">Rejected Requests</button>
            <button id="approved_btn" class="tab-btn" onclick="showSection('approved')">Approved Requests</button>
            <button id="all_btn" class="tab-btn" onclick="showSection('all')">All Requests</button> 
        </div>    
        <div class="filter-search-container">
            <input type="text" id="searchInput" placeholder="Search requests..." onkeyup="searchRequests()">
            <button class="btn" onclick="exportToCSV()">Export to CSV</button>
        </div>

        <script>
        function searchRequests() {
            const input = document.getElementById('searchInput').value.toLowerCase();
            const rows = document.querySelectorAll('.section.active table tbody tr');
            rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            let match = false;
            cells.forEach(cell => {
                if (cell.textContent.toLowerCase().includes(input)) {
                match = true;
                }
            });
            row.style.display = match ? '' : 'none';
            });
            
        }

        function exportToCSV() {
            const activeSection = document.querySelector('.section.active');
            const rows = activeSection.querySelectorAll('table tr');
            let csvContent = '';
            rows.forEach(row => {
                const cells = row.querySelectorAll('th, td');
                const rowContent = Array.from(cells).map(cell => cell.textContent).join(',');
                csvContent += rowContent + '\n';
            });

            const blob = new Blob([csvContent], { type: 'text/csv' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'requests.csv';
            a.click();
            URL.revokeObjectURL(url);
        }
        </script>
        <div id="pendingSection" class="section">
            <h2>Pending Requests</h2>
            <div id="pendingContent"></div>
        </div>

        <div id="approvedSection" class="section">
            <h2>Approved Requests</h2>
            <div id="approvedContent"></div>
        </div>

        <div id="rejectedSection" class="section">
            <h2>Rejected Requests</h2>
            <div id="rejectedContent"></div>
        </div>

        <div id="allSection" class="section">
            <h2>All Requests</h2>
            <div id="allContent"></div>
        </div>
    </div>
    

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.getElementById('pending_btn').click();
        });
    function showSection(sectionType) {
        // Hide all sections and remove active class from buttons
        document.querySelectorAll('.section').forEach(section => {
            section.classList.remove('active');
        });
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('active');
        });

        // Show selected section and activate button
        document.getElementById(sectionType + 'Section').classList.add('active');
        event.target.classList.add('active');


        // Fetch and load content
        fetch(`get_requests.php?type=${sectionType}`)
            .then(response => response.text())
            .then(data => {
                document.getElementById(sectionType + 'Content').innerHTML = data;
            });
    }

    function handleAction(requestId, action) {
        const formData = new FormData();
        formData.append('request_id', requestId);
        
        if (action === 'delete') {
            if (!confirm('Are you sure you want to delete this request?')) {
                return;
            }
            formData.append('delete_request', '1');
        } else {
            formData.append('update_status', '1');
            formData.append('new_status', action);
        }

        fetch('show_requests.php', {
            method: 'POST',
            body: formData
        })
        .then(() => {
            document.getElementById('pending_btn').click();
            // Refresh current section
            const activeSection = document.querySelector('.section.active');
            if (activeSection) {
                // showSection(activeSection.id.replace('Section', ''));
            }
        });
    }

    // Show pending requests by default
    document.addEventListener('DOMContentLoaded', () => {
        showSection('pending');
    });
    </script>
</body>
</html>