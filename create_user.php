<?php
session_start();
require_once 'config.php';

// Check if user is logged in as admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: index.html');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate input
        $required_fields = ['username', 'password', 'name', 'designation', 'contact', 'email'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("All fields are required");
            }
        }

        // Validate email
        if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format");
        }

        // Check if username already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
        $stmt->execute([$_POST['username']]);
        if ($stmt->fetchColumn() > 0) {
            throw new Exception("Username already exists");
        }

        // Hash password
        $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);

        // Insert new user
        $sql = "INSERT INTO users (username, password, name, designation, contact, email, user_type) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $_POST['username'],
            $hashedPassword,
            $_POST['name'],
            $_POST['designation'],
            $_POST['contact'],
            $_POST['email'],
            $_POST['user_type'] ?? 'employee'
        ]);

        $success_message = "User created successfully!";
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create User - Pravasis Request System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
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
            font-weight: bold;
        }
        input, select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            background: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background: #45a049;
        }
        .error {
            color: #f44336;
            margin-bottom: 10px;
        }
        .success {
            color: #4CAF50;
            margin-bottom: 10px;
        }
        .back-button {
            background: #2196F3;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 20px;
        }
        .password-requirements {
            font-size: 0.8em;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="admin_dashboard.php" class="back-button">← Back to Dashboard</a>
        
        <h2>Create New User</h2>
        
        <?php if (isset($error_message)): ?>
            <div class="error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        
        <?php if (isset($success_message)): ?>
            <div class="success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>

        <form method="POST" action="" id="createUserForm">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>

            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
                <div class="password-requirements">
                    Password should be at least 8 characters long and include numbers and special characters
                </div>
            </div>

            <div class="form-group">
                <label for="name">Full Name:</label>
                <input type="text" id="name" name="name" required>
            </div>

            <div class="form-group">
                <label for="designation">Designation:</label>
                <input type="text" id="designation" name="designation" required>
            </div>

            <div class="form-group">
                <label for="contact">Contact Number:</label>
                <input type="tel" id="contact" name="contact" required>
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="user_type">User Type:</label>
                <select id="user_type" name="user_type">
                    <option value="employee">Employee</option>
                    <option value="admin">Admin</option>
                </select>
            </div>

            <button type="submit">Create User</button>
        </form>
    </div>

    <script>
        document.getElementById('createUserForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            
            // Basic password validation
            if (password.length < 8) {
                e.preventDefault();
                alert('Password must be at least 8 characters long');
                return;
            }
            
            if (!/\d/.test(password)) {
                e.preventDefault();
                alert('Password must contain at least one number');
                return;
            }
            
            if (!/[!@#$%^&*]/.test(password)) {
                e.preventDefault();
                alert('Password must contain at least one special character (!@#$%^&*)');
                return;
            }
        });
    </script>
</body>
</html>