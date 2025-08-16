<?php
session_start();

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'your_db_username');
define('DB_PASS', 'your_db_password');
define('DB_NAME', 'your_db_name');

// Connect to database
try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    // Validate inputs
    if (empty($username) || empty($password)) {
        header("Location: login.html?error=empty_fields");
        exit();
    }

    // Fetch admin from database
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    
    if ($stmt->rowCount() === 1) {
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Verify password
        if (password_verify($password, $admin['password_hash'])) {
            // Login successful
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_logged_in'] = true;
            
            // Regenerate session ID for security
            session_regenerate_id(true);
            
            header("Location: admin_dashboard.php");
            exit();
        }
    }
    
    // If we get here, login failed
    header("Location: login.html?error=invalid_credentials");
    exit();
} else {
    // Not a POST request
    header("Location: login.html");
    exit();
}
?>