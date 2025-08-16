<?php
$page_title = "Ask a Question";
require_once 'includes/config.php';


if (!isLoggedIn()) {
    redirect('login.php');
}

$error = '';
$success = '';

try {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT id, module_name, module_code FROM modules ORDER BY module_code");
    $stmt->execute();
    $modules = $stmt->fetchAll();
} catch (PDOException $e) {
    $modules = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title']);
    $content = sanitize($_POST['content']);
    $module_id = (int)$_POST['module_id'];

    if (empty($title) || empty($content) || empty($module_id)) {
        $error = 'Please fill in all required fields';
    } else {

        $image_name = null;
        $upload_result = uploadImage();

        if ($upload_result && isset($upload_result['error'])) {
            $error = $upload_result['error'];
        } elseif ($upload_result && $upload_result['success']) {
            $image_name = $upload_result['filename'];
        }

        if (empty($error)) {
            try {
                $pdo = getConnection();
                $stmt = $pdo->prepare("INSERT INTO posts (title, content, image, user_id, module_id) VALUES (?, ?, ?, ?, ?)");
                if ($stmt->execute([$title, $content, $image_name, $_SESSION['user_id'], $module_id])) {
                    redirect('index.php');
                } else {
                    $error = 'Failed to create post';
                }
            } catch (PDOException $e) {
                $error = 'Database error occurred';
            }
        }
    }
}

include 'templates/header.php';
include 'templates/add_post_template.php';
include 'templates/footer.php'; ?>