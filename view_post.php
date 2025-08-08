<?php
$page_title = "View Question";
require_once 'includes/config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect('index.php');
}

$post_id = (int)$_GET['id'];

try {
    $pdo = getConnection();
    $stmt = $pdo->prepare("
        SELECT p.*, u.username, m.module_name, m.module_code 
        FROM posts p 
        JOIN users u ON p.user_id = u.id 
        JOIN modules m ON p.module_id = m.id 
        WHERE p.id = ?
    ");
    $stmt->execute([$post_id]);
    $post = $stmt->fetch();

    if (!$post) {
        redirect('index.php');
    }
} catch (PDOException $e) {
    redirect('index.php');
}

$page_title = htmlspecialchars($post['title']);

include 'templates/header.php';

include 'templates/view_post_template.php';

include 'templates/footer.php';
?>

