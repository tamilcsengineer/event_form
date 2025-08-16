<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.html?error=not_logged_in");
    exit();
}

// Database connection (same as login_handler.php)
// ... include your DB connection code here ...

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
</head>
<body>
    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>!</h1>
    <p>This is your admin dashboard.</p>
    <a href="logout.php">Logout</a>
    
    <!-- Add your admin content here -->
</body>
</html>