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
            justify-content: space-between; /* Align items to the start and end */
            align-items: center; /* Center items vertically */
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
            background-color:#fd7e14;
        }
        .btn:hover {
            background-color:#f06200;  
        }
        .btn-approve { background-color: #4CAF50; }
        .btn-reject { background-color: #f44336; }
        .btn-delete { background-color: #ff9800; }
        .btn-approve:hover { background-color:rgb(2, 221, 13); }
        .btn-reject:hover { background-color:rgb(236, 38, 12); }
        .btn-delete:hover { background-color:rgb(213, 247, 62); }
        .back-button {
            background: #dc3545;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            font-size: 16px;
        }
        .back-button:hover {
            background-color:#c82333;
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
        .action-column {
            width: 50px;
        }
    </style>
</head>
<body>
    <div class="container">        
        <div class="btn-container">
            <div>
                <button id="pending_btn" class="tab-btn" onclick="showSection('pending')">Pending Requests</button>
                <button id="rejected_btn" class="tab-btn" onclick="showSection('rejected')">Rejected Requests</button>
                <button id="approved_btn" class="tab-btn" onclick="showSection('approved')">Approved Requests</button>
                <button id="all_btn" class="tab-btn" onclick="showSection('all')">All Requests</button>
            </div>
            <a href="admin_dashboard.php"><button id="back" class="back-button" onclick="showSection('back')">← Back to Dashboard</button></a>
        </div>   
        <div class="filter-search-container">
            <input type="text" id="searchInput" placeholder="Search requests..." onkeyup="searchRequests()">
            <button class="btn" onclick="ExportToExcel()">Export to Excel</button>
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
        function ExportToExcel() {
            const activeSection = document.querySelector('.section.active');
            if (!activeSection) return;

            const sectionType = activeSection.id.replace('Section', '');
            
            // Fetch data from server for Excel export
            fetch(`get_excel.php?type=${sectionType}`)
                .then(response => response.blob())
                .then(blob => {
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    const date = new Date().toISOString().slice(0, 10);
                    a.href = url;
                    a.download = `${activeSection.querySelector('h2').textContent.trim()}_${date}.xlsx`;
                    document.body.appendChild(a);
                    a.click();
                    a.remove();
                    window.URL.revokeObjectURL(url);
                })
                .catch(error => console.error('Error exporting data:', error));
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