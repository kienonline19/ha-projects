<?php

$page_title = "Manage Modules";
require_once 'includes/config.php';


if (!isLoggedIn()) {
    redirect('login.php');
}

$error = '';
$success = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $pdo = getConnection();

        switch ($_POST['action']) {
            case 'add':
                $module_name = sanitize($_POST['module_name']);
                $module_code = sanitize($_POST['module_code']);

                if (empty($module_name) || empty($module_code)) {
                    $error = 'Please fill in all fields';
                } else {
                    try {

                        $stmt = $pdo->prepare("SELECT id FROM modules WHERE module_code = ?");
                        $stmt->execute([$module_code]);

                        if ($stmt->fetch()) {
                            $error = 'Module code already exists';
                        } else {
                            $stmt = $pdo->prepare("INSERT INTO modules (module_name, module_code) VALUES (?, ?)");

                            if ($stmt->execute([$module_name, $module_code])) {
                                $success = 'Module added successfully';
                            } else {
                                $error = 'Failed to add module';
                            }
                        }
                    } catch (PDOException $e) {
                        $error = 'Database error occurred';
                    }
                }
                break;

            case 'edit':
                $module_id = (int)$_POST['module_id'];
                $module_name = sanitize($_POST['module_name']);
                $module_code = sanitize($_POST['module_code']);

                if (empty($module_name) || empty($module_code)) {
                    $error = 'Please fill in all fields';
                } else {
                    try {

                        $stmt = $pdo->prepare("SELECT id FROM modules WHERE module_code = ? AND id != ?");
                        $stmt->execute([$module_code, $module_id]);

                        if ($stmt->fetch()) {
                            $error = 'Module code already exists';
                        } else {
                            $stmt = $pdo->prepare("UPDATE modules SET module_name = ?, module_code = ? WHERE id = ?");

                            if ($stmt->execute([$module_name, $module_code, $module_id])) {
                                $success = 'Module updated successfully';
                            } else {
                                $error = 'Failed to update module';
                            }
                        }
                    } catch (PDOException $e) {
                        $error = 'Database error occurred';
                    }
                }
                break;

            case 'delete':
                $module_id = (int)$_POST['module_id'];

                try {
                    $stmt = $pdo->prepare("DELETE FROM modules WHERE id = ?");

                    if ($stmt->execute([$module_id])) {
                        $success = 'Module deleted successfully';
                    } else {
                        $error = 'Failed to delete module';
                    }
                } catch (PDOException $e) {
                    $error = 'Cannot delete module - it may be assigned to posts';
                }
                break;
        }
    }
}


try {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT m.*, COUNT(p.id) as post_count FROM modules m LEFT JOIN posts p ON m.id = p.module_id GROUP BY m.id ORDER BY m.module_code");
    $stmt->execute();
    $modules = $stmt->fetchAll();
} catch (PDOException $e) {
    $modules = [];
}

include 'templates/header.php';
include 'templates/manage_modules_template.php';
include 'templates/footer.php';
?>
