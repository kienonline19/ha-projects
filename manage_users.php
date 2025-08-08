<?php

$page_title = "Manage Users";
require_once 'includes/config.php';


if (!isLoggedIn()) {
    redirect('login.php');
}

if (!isAdmin()) {
    redirect('index.php');
}

$error = '';
$success = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $pdo = getConnection();

        switch ($_POST['action']) {
            case 'add':
                $username = sanitize($_POST['username']);
                $email = sanitize($_POST['email']);
                $password = $_POST['password'];

                if (empty($username) || empty($email) || empty($password)) {
                    $error = 'Please fill in all fields';
                } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $error = 'Please enter a valid email address';
                } else {
                    try {

                        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
                        $stmt->execute([$username, $email]);

                        if ($stmt->fetch()) {
                            $error = 'Username or email already exists';
                        } else {
                            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                            $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");

                            if ($stmt->execute([$username, $email, $hashed_password])) {
                                $success = 'User added successfully';
                            } else {
                                $error = 'Failed to add user';
                            }
                        }
                    } catch (PDOException $e) {
                        $error = 'Database error occurred';
                    }
                }
                break;

            case 'edit':
                $user_id = (int)$_POST['user_id'];
                $username = sanitize($_POST['username']);
                $email = sanitize($_POST['email']);

                if (empty($username) || empty($email)) {
                    $error = 'Please fill in all fields';
                } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $error = 'Please enter a valid email address';
                } else {
                    try {

                        $stmt = $pdo->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
                        $stmt->execute([$username, $email, $user_id]);

                        if ($stmt->fetch()) {
                            $error = 'Username or email already exists';
                        } else {
                            $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");

                            if ($stmt->execute([$username, $email, $user_id])) {
                                $success = 'User updated successfully';
                            } else {
                                $error = 'Failed to update user';
                            }
                        }
                    } catch (PDOException $e) {
                        $error = 'Database error occurred';
                    }
                }
                break;

            case 'delete':
                $user_id = (int)$_POST['user_id'];

                if ($user_id == $_SESSION['user_id']) {
                    $error = 'You cannot delete your own account';
                } else {
                    try {
                        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");

                        if ($stmt->execute([$user_id])) {
                            $success = 'User deleted successfully';
                        } else {
                            $error = 'Failed to delete user';
                        }
                    } catch (PDOException $e) {
                        $error = 'Cannot delete user - they may have posts in the system';
                    }
                }
                break;
        }
    }
}


try {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT u.*, COUNT(p.id) as post_count FROM users u LEFT JOIN posts p ON u.id = p.user_id GROUP BY u.id ORDER BY u.username");
    $stmt->execute();
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    $users = [];
}

include 'templates/header.php';

include 'templates/manage_users_template.php';

include 'templates/footer.php';
?>
