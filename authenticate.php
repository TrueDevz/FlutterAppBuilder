<?php
session_start();

function authenticate($username, $password) {
    // Hardcoded credentials for simplicity.
    $valid_username = 'admin';
    $valid_password = '12345';

    if ($username === $valid_username && $password === $valid_password) {
        $_SESSION['user'] = $username;
        header("Location: dashboard.php");
        exit();
    } else {
        $_SESSION['error'] = 'Invalid login credentials';
        header("Location: login.php");
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    authenticate($username, $password);
}
?>
