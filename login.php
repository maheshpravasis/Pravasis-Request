<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_type'] = $user['user_type'];
        
        if ($user['user_type'] === 'admin') {
            header('Location: admin_dashboard.php');
        } else {
            header('Location: request_form.php');
        }
        exit();
    } else {
        header('Location: index.html?error=1');
        exit();
    }
}
?>