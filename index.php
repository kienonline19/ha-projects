<?php

$page_title = "Home - Questions & Answers";
require_once 'includes/config.php';


$message = '';
$message_type = '';
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'];
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}


try {
    $pdo = getConnection();
    $stmt = $pdo->prepare("
        SELECT p.*, u.username, m.module_name, m.module_code 
        FROM posts p 
        JOIN users u ON p.user_id = u.id 
        JOIN modules m ON p.module_id = m.id 
        ORDER BY p.created_at DESC
    ");
    $stmt->execute();
    $posts = $stmt->fetchAll();
} catch (PDOException $e) {
    $posts = [];
    $error = "Failed to load posts";
}

include 'templates/header.php';
include 'templates/layout_template.php';
include 'templates/footer.php'; 
?>