<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Student Q&A System</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .navbar-brand {
            font-weight: bold;
            color: #007bff !important;
        }

        .card {
            border: none;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }

        .footer {
            background-color: #f8f9fa;
            padding: 20px 0;
            margin-top: 50px;
            border-top: 1px solid #dee2e6;
        }

        .post-meta {
            color: #6c757d;
            font-size: 0.9em;
        }

        .alert {
            border-radius: 8px;
        }

        .dropdown-menu {
            z-index: 1050 !important;
            position: absolute !important;
        }

        .dropdown {
            position: relative;
            z-index: 1000;
        }


        .sticky-top {
            z-index: 999;
        }


        .dropdown-menu-end {
            right: 0;
            left: auto;
        }


        @media (max-width: 768px) {
            .dropdown-menu {
                position: absolute !important;
                z-index: 1050 !important;
                left: auto !important;
                right: 0 !important;
            }
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-question-circle"></i> Student Q&A
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php"><i class="fas fa-home"></i> Home</a>
                    </li>
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="add_post.php"><i class="fas fa-plus"></i> Ask Question</a>
                        </li>
                        <?php if (isAdmin()): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="manage_users.php"><i class="fas fa-users"></i> Manage Users</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="manage_modules.php"><i class="fas fa-book"></i> Manage Modules</a>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php"><i class="fas fa-envelope"></i> Contact</a>
                    </li>
                </ul>

                <ul class="navbar-nav">
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['username']); ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="signup.php"><i class="fas fa-user-plus"></i> Sign Up</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">