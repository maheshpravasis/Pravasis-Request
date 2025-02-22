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
        $required_fields = ['username', 'password', 'name', 'designation', 'contact', 'email', 'division'];
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
        $sql = "INSERT INTO users (username, password, name, designation, contact, email, division, user_type) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $_POST['username'],
            $hashedPassword,
            $_POST['name'],
            $_POST['designation'],
            $_POST['contact'],
            $_POST['email'],
            $_POST['division'],
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --success-color: #4ade80;
            --error-color: #f87171;
            --text-color: #374151;
            --light-bg: #f9fafb;
            --border-color: #e5e7eb;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f3f4f6;
            margin: 0;
            padding: 0;
            color: var(--text-color);
            line-height: 1.6;
        }
        
        .container {
            max-width: 600px;
            margin: 40px auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: var(--shadow);
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 15px;
        }
        
        .back-button {
            display: flex;
            align-items: center;
            gap: 8px;
            background: var(--primary-color);
            color: white;
            padding: 10px 16px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .back-button:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.15);
        }
        
        h2 {
            color: var(--primary-color);
            margin: 0;
            font-size: 1.75rem;
        }
        
        .notification {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-weight: 500;
        }
        
        .error {
            background-color: rgba(254, 226, 226, 1);
            color: var(--error-color);
            border-left: 4px solid var(--error-color);
        }
        
        .success {
            background-color: rgba(236, 253, 245, 1);
            color: #065f46;
            border-left: 4px solid var(--success-color);
        }
        
        .form-group {
            margin-bottom: 20px;
            width: 100%;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #4b5563;
        }
        
        input, select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            box-sizing: border-box;
            font-size: 1rem;
            transition: border-color 0.3s, box-shadow 0.3s;
            background-color: var(--light-bg);
        }
        
        input:focus, select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
        }
        
        .password-requirements {
            font-size: 0.8rem;
            color: #6b7280;
            margin-top: 6px;
            padding-left: 10px;
            border-left: 2px solid #d1d5db;
        }
        
        .submit-button {
            background: var(--primary-color);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            width: 100%;
            margin-top: 20px;
        }
        
        .submit-button:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.15);
        }
        
        .input-icon-wrap {
            position: relative;
        }
        
        .input-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
        }
        
        .input-with-icon {
            padding-left: 40px;
        }
        
        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            cursor: pointer;
            z-index: 10;
        }

        .password-toggle:hover {
            color: var(--primary-color);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h2>Create New User</h2>
            <a href="admin_dashboard.php" class="back-button">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
        
        <?php if (isset($error_message)): ?>
            <div class="notification error">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($success_message)): ?>
            <div class="notification success">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" id="createUserForm">
            <div class="form-group">
                <label for="username">Username</label>
                <div class="input-icon-wrap">
                    <i class="fas fa-user input-icon"></i>
                    <input type="text" id="username" name="username" class="input-with-icon" required>
                </div>
            </div>

            <div class="form-group">               
                <label for="password">Password</label>
                <div class="input-icon-wrap">
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password" id="password" name="password" class="input-with-icon" required>
                    <i class="fas fa-eye password-toggle" id="togglePassword"></i>
                </div>
                <div class="password-requirements">
                    <i class="fas fa-info-circle"></i> Password should be at least 8 characters long and include numbers and special characters
                </div>
            </div>

            <div class="form-group">
                <label for="name">Full Name</label>
                <div class="input-icon-wrap">
                    <i class="fas fa-id-card input-icon"></i>
                    <input type="text" id="name" name="name" class="input-with-icon" required>
                </div>
            </div>

            <div class="form-group">
                <label for="designation">Designation</label>
                <div class="input-icon-wrap">
                    <i class="fas fa-briefcase input-icon"></i>
                    <input type="text" id="designation" name="designation" class="input-with-icon" required>
                </div>
            </div>

            <div class="form-group">
                <label for="contact">Contact Number</label>
                <div class="input-icon-wrap">
                    <i class="fas fa-phone input-icon"></i>
                    <input type="tel" id="contact" name="contact" class="input-with-icon" required>
                </div>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <div class="input-icon-wrap">
                    <i class="fas fa-envelope input-icon"></i>
                    <input type="email" id="email" name="email" class="input-with-icon" required>
                </div>
            </div>
            <div class="form-group">
                <label for="division">Division</label>
                <div class="input-icon-wrap">
                    <i class="fas fa-users-cog input-icon"></i>
                    <select id="division" name="division" class="input-with-icon">
                        <option value="">Select Division</option>
                        <option value="finance">Finance Division</option>
                        <option value="it">IT Division</option>
                        <option value="psk">PSK Division</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="user_type">User Type</label>
                <div class="input-icon-wrap">
                    <i class="fas fa-users-cog input-icon"></i>
                    <select id="user_type" name="user_type" class="input-with-icon">
                        <option value="">Select User Type</option>
                        <option value="employee">Employee</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="submit-button">
                <i class="fas fa-user-plus"></i> Create User
            </button>
        </form>
    </div>

    <script>
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this;
            
            // Toggle the password visibility
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });

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