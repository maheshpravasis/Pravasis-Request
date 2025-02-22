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
        :root {
            --primary-color: #2563eb;
            --primary-hover: #1d4ed8;
            --bg-color: #f1f5f9;
            --card-bg: #ffffff;
            --text-color: #1f2937;
            --border-color: #e5e7eb;
            --danger-color: #dc2626;
            --danger-hover: #b91c1c;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            padding: 2rem;
            background-color: var(--bg-color);
            color: var(--text-color);
            line-height: 1.5;
            margin: 0;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: var(--card-bg);
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        h2 {
            color: var(--text-color);
            font-size: 1.875rem;
            font-weight: 600;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--border-color);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: var(--text-color);
        }

        input, select, textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.2s, box-shadow 0.2s;
            box-sizing: border-box;
        }

        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .readonly {
            background-color: var(--bg-color);
            cursor: not-allowed;
        }

        .button-group {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        button {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s;
            flex: 1;
        }

        .btn-submit {
            background: var(--primary-color);
            color: white;
        }

        .btn-submit:hover {
            background: var(--primary-hover);
        }

        .btn-cancel {
            background: var(--danger-color);
            color: white;
        }

        .btn-cancel:hover {
            background: var(--danger-hover);
        }

        #responseMessage {
            margin-top: 1rem;
            padding: 1rem;
            border-radius: 8px;
            font-weight: 500;
        }

        .success {
            background-color: #dcfce7;
            color: #166534;
        }

        .error {
            background-color: #fee2e2;
            color: #991b1b;
        }

        @media (min-width: 640px) {
            .button-group {
                justify-content: flex-end;
            }
            
            button {
                flex: 0 0 auto;
                min-width: 120px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Request Form</h2>
        <form id="requestForm" action="submit_request.php" method="POST">
            <div class="form-group">
                <label>Name</label>
                <input type="text" value="<?php echo htmlspecialchars($user['name']); ?>" readonly class="readonly">
            </div>
            <div class="form-group">
                <label>Designation</label>
                <input type="text" value="<?php echo htmlspecialchars($user['designation']); ?>" readonly class="readonly">
            </div>
            <div class="form-group">
                <label>Contact</label>
                <input type="text" value="<?php echo htmlspecialchars($user['contact']); ?>" readonly class="readonly">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly class="readonly">
            </div>
            <div class="form-group">
                <label>Division</label>
                <input type="division" value="<?php echo htmlspecialchars($user['division']); ?>" readonly class="readonly">
            </div>
            <div class="form-group">
                <label>Request Type</label>
                <select name="request_type" id="requestType" required>
                    <option value="">Select Request Type</option>
                    <option value="Leave Request">Leave Request</option>
                    <option value="Work from Home">Work from Home</option>
                </select>
            </div>
            <div class="form-group" id="leaveTypeGroup" style="display: none;">
                <label>Leave Type</label>
                <select name="leave_type">
                    <option value="">Select Leave Type</option>
                    <option value="CL">CL</option>
                    <option value="CO">CO</option>
                    <option value="LOP">LOP</option>
                    <option value="HDL">HDL</option>
                </select>
            </div>
            <div class="form-group">
                <label>Reason</label>
                <textarea name="reason" required rows="4"></textarea>
            </div>
            <div class="form-group">
                <label>From Date</label>
                <input type="date" name="from_date" required>
            </div>
            <div class="form-group">
                <label>To Date</label>
                <input type="date" name="to_date" required>
            </div>
            <div class="button-group">
                <button type="submit" class="btn-submit">Submit Request</button>
                <button type="button" class="btn-cancel" onclick="window.location.href='index.html'">Cancel Request</button>
            </div>
        </form>
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
        if (data.success) {
            alert('Request submitted successfully!');
            window.location.reload();
        } else {
            const responseMessage = document.getElementById('responseMessage');
            responseMessage.textContent = data.message;
            responseMessage.className = 'error';
        }
    })
    .catch(error => {
        const responseMessage = document.getElementById('responseMessage');
        responseMessage.textContent = 'An error occurred while submitting the request.';
        responseMessage.className = 'error';
    });
});
    </script>
</body>
</html>