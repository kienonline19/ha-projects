<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'student_qa_system');
define('DB_USER', 'root');
define('DB_PASS', '');


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


if (session_status() == PHP_SESSION_NONE) {
    session_start();
}


function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

function isAdmin()
{
    return isLoggedIn() && $_SESSION['username'] === 'admin';
}

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

function redirect($url)
{
    header("Location: $url");
    exit();
}

function sanitize($data)
{
    return htmlspecialchars(strip_tags(trim($data)));
}

function uploadImage()
{
    if (!isset($_FILES['image']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        return ['error' => 'Upload error occurred'];
    }


    $upload_dir = 'uploads/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $file = $_FILES['image'];


    $allowed = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($file['type'], $allowed)) {
        return ['error' => 'Only JPG, PNG and GIF images allowed'];
    }


    if ($file['size'] > 5 * 1024 * 1024) {
        return ['error' => 'File too large. Maximum 5MB'];
    }


    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = time() . '_' . uniqid() . '.' . $extension;


    if (move_uploaded_file($file['tmp_name'], $upload_dir . $filename)) {
        return ['success' => true, 'filename' => $filename];
    } else {
        return ['error' => 'Failed to save file'];
    }
}
?>