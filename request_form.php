<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.html');
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Form</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input, select, textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            background: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .readonly {
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Request Form</h2>
        <form id="requestForm" action="submit_request.php" method="POST">
            <div class="form-group">
                <label>Name:</label>
                <input type="text" value="<?php echo htmlspecialchars($user['name']); ?>" readonly class="readonly">
            </div>
            <div class="form-group">
                <label>Designation:</label>
                <input type="text" value="<?php echo htmlspecialchars($user['designation']); ?>" readonly class="readonly">
            </div>
            <div class="form-group">
                <label>Contact:</label>
                <input type="text" value="<?php echo htmlspecialchars($user['contact']); ?>" readonly class="readonly">
            </div>
            <div class="form-group">
                <label>Email:</label>
                <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly class="readonly">
            </div>
            <div class="form-group">
                <label>Request Type:</label>
                <select name="request_type" id="requestType" required>
                    <option value="">Select Request Type</option>
                    <option value="Leave Request">Leave Request</option>
                    <option value="Work from Home">Work from Home</option>
                </select>
            </div>
            <div class="form-group" id="leaveTypeGroup" style="display: none;">
                <label>Leave Type:</label>
                <select name="leave_type">
                    <option value="">Select Leave Type</option>
                    <option value="CL">CL</option>
                    <option value="CO">CO</option>
                    <option value="LOP">LOP</option>
                </select>
            </div>
            <div class="form-group">
                <label>Reason:</label>
                <textarea name="reason" required rows="4"></textarea>
            </div>
            <div class="form-group">
                <label>From Date:</label>
                <input type="date" name="from_date" required>
            </div>
            <div class="form-group">
                <label>To Date:</label>
                <input type="date" name="to_date" required>
            </div>
            <button type="submit">Submit Request</button>
        </form>
        <div></div>
        <div id="responseMessage"></div>    
    </div>

    <script>
        document.getElementById('requestType').addEventListener('change', function() {
            const leaveTypeGroup = document.getElementById('leaveTypeGroup');
            if (this.value === 'Leave Request') {
                leaveTypeGroup.style.display = 'block';
            } else {
                leaveTypeGroup.style.display = 'none';
            }
        });

        
        document.getElementById('requestForm').addEventListener('submit', function(event) {
            event.preventDefault();
            const formData = new FormData(this);

            fetch('submit_request.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const responseMessage = document.getElementById('responseMessage');
                if (data.success) {
                    responseMessage.textContent = data.message;
                    responseMessage.style.color = 'green';
                } else {
                    responseMessage.textContent = data.message;
                    responseMessage.style.color = 'red';
                }
            })
            .catch(error => {
                const responseMessage = document.getElementById('responseMessage');
                responseMessage.textContent = 'An error occurred while submitting the request.';
                responseMessage.style.color = 'red';
            });
        });
    </script>
</body>
</html>