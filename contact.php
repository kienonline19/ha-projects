<?php
$page_title = "Contact Us";
require_once 'includes/config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $subject = sanitize($_POST['subject']);
    $message = sanitize($_POST['message']);

    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = 'Please fill in all fields';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } else {
        try {
            $pdo = getConnection();

            $user_id = NULL;

            if (isLoggedIn()) {
                $checkUser = $pdo->prepare("SELECT id FROM users WHERE id = ?");
                $checkUser->execute([$_SESSION['user_id']]);

                if ($checkUser->fetch()) {
                    $user_id = $_SESSION['user_id'];
                } else {
                    $user_id = NULL;
                }
            }

            $checkColumns = $pdo->query("DESCRIBE contact_messages");
            $columns = $checkColumns->fetchAll(PDO::FETCH_COLUMN);

            $hasUserId = in_array('user_id', $columns);
            $hasStatus = in_array('status', $columns);

            if ($hasUserId && $hasStatus) {
                $stmt = $pdo->prepare("INSERT INTO contact_messages (user_id, name, email, subject, message, status) VALUES (?, ?, ?, ?, ?, 'new')");
                $result = $stmt->execute([$user_id, $name, $email, $subject, $message]);
            } elseif ($hasUserId) {
                $stmt = $pdo->prepare("INSERT INTO contact_messages (user_id, name, email, subject, message) VALUES (?, ?, ?, ?, ?)");
                $result = $stmt->execute([$user_id, $name, $email, $subject, $message]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
                $result = $stmt->execute([$name, $email, $subject, $message]);
            }

            if ($result) {
                if ($user_id) {
                    $success = 'Your message has been sent successfully! As a logged-in user, your message will receive priority attention. We will get back to you soon.';
                } else {
                    $success = 'Your message has been sent successfully! We will get back to you soon.';
                }

                $_POST = [];
            } else {
                $error = 'Failed to send message. Please try again.';
            }
        } catch (PDOException $e) {
            if ($e->getCode() == '23000') {
                $error = 'User validation error. Please try logging out and logging back in.';
            } else {
                $error = 'Database error occurred. Please try again.';
            }
        }
    }
}

include 'templates/header.php';
include 'templates/contact_template.php';
include 'templates/footer.php';
?>