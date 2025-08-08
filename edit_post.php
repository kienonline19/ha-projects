<?php

$page_title = "Edit Question";
require_once 'includes/config.php';


if (!isLoggedIn()) {
    redirect('login.php');
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect('index.php');
}

$post_id = (int)$_GET['id'];
$error = '';
$success = '';


try {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ? AND user_id = ?");
    $stmt->execute([$post_id, $_SESSION['user_id']]);
    $post = $stmt->fetch();

    if (!$post) {
        redirect('index.php');
    }
} catch (PDOException $e) {
    redirect('index.php');
}


try {
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
        $image_name = $post['image'];


        if (isset($_POST['remove_image']) && $_POST['remove_image'] === '1') {
            if ($post['image'] && file_exists('uploads/' . $post['image'])) {
                unlink('uploads/' . $post['image']);
            }
            $image_name = null;
        }


        $upload_result = uploadImage();
        if ($upload_result && isset($upload_result['error'])) {
            $error = $upload_result['error'];
        } elseif ($upload_result && $upload_result['success']) {

            if ($post['image'] && file_exists('uploads/' . $post['image'])) {
                unlink('uploads/' . $post['image']);
            }
            $image_name = $upload_result['filename'];
        }

        if (empty($error)) {
            try {
                $stmt = $pdo->prepare("UPDATE posts SET title = ?, content = ?, image = ?, module_id = ? WHERE id = ? AND user_id = ?");
                if ($stmt->execute([$title, $content, $image_name, $module_id, $post_id, $_SESSION['user_id']])) {
                    redirect('view_post.php?id=' . $post_id);
                } else {
                    $error = 'Failed to update post';
                }
            } catch (PDOException $e) {
                $error = 'Database error occurred';
            }
        }
    }
} else {

    $_POST['title'] = $post['title'];
    $_POST['content'] = $post['content'];
    $_POST['module_id'] = $post['module_id'];
}

include 'templates/header.php';
include 'templates/edit_post_template.php';
include 'templates/footer.php'; 
?>