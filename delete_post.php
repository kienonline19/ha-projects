<?php

require_once 'config.php';


if (!isLoggedIn()) {
    redirect('login.php');
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect('index.php');
}

$post_id = (int)$_GET['id'];

try {
    $pdo = getConnection();


    $stmt = $pdo->prepare("SELECT image FROM posts WHERE id = ? AND user_id = ?");
    $stmt->execute([$post_id, $_SESSION['user_id']]);
    $post = $stmt->fetch();

    if ($post) {

        if ($post['image'] && file_exists('uploads/' . $post['image'])) {
            unlink('uploads/' . $post['image']);
        }


        $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ? AND user_id = ?");
        $stmt->execute([$post_id, $_SESSION['user_id']]);


        $_SESSION['message'] = 'Question deleted successfully.';
        $_SESSION['message_type'] = 'success';
    } else {

        $_SESSION['message'] = 'Question not found or you do not have permission to delete it.';
        $_SESSION['message_type'] = 'error';
    }
} catch (PDOException $e) {

    $_SESSION['message'] = 'Failed to delete question. Please try again.';
    $_SESSION['message_type'] = 'error';
}

redirect('index.php');
?>