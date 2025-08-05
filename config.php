<?php
// config.php - Database configuration

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'student_qa_system');
define('DB_USER', 'root');
define('DB_PASS', '');

// Create PDO connection
function getConnection()
{
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]);
        return $pdo;
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
}

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Helper function to check if user is logged in
function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

// Helper function to check if user is admin
function isAdmin()
{
    return isLoggedIn() && $_SESSION['username'] === 'admin';
}

// Helper function to get current user
function getCurrentUser()
{
    if (isLoggedIn()) {
        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'email' => $_SESSION['email']
        ];
    }
    return null;
}

// Helper function to redirect
function redirect($url)
{
    header("Location: $url");
    exit();
}

// Helper function to sanitize input
function sanitize($data)
{
    return htmlspecialchars(strip_tags(trim($data)));
}

function uploadImage() {
    // Check if image was uploaded
    if (!isset($_FILES['image']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE) {
        return null; // No image uploaded - this is OK
    }
    
    if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        return ['error' => 'Upload error occurred'];
    }
    
    // Create uploads directory if it doesn't exist
    $upload_dir = 'uploads/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $file = $_FILES['image'];
    
    // Check file type - only allow images
    $allowed = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($file['type'], $allowed)) {
        return ['error' => 'Only JPG, PNG and GIF images allowed'];
    }
    
    // Check file size - max 5MB
    if ($file['size'] > 5 * 1024 * 1024) {
        return ['error' => 'File too large. Maximum 5MB'];
    }
    
    // Generate unique filename to avoid conflicts
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = time() . '_' . uniqid() . '.' . $extension;
    
    // Move uploaded file to uploads directory
    if (move_uploaded_file($file['tmp_name'], $upload_dir . $filename)) {
        return ['success' => true, 'filename' => $filename];
    } else {
        return ['error' => 'Failed to save file'];
    }
}
?>