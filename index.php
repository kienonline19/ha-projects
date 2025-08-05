<?php

$page_title = "Home - Questions & Answers";
require_once 'config.php';


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

include 'header.php';
?>

<div class="row">
    <div class="col-md-12">
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show">
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-question-circle"></i> Recent Questions</h2>
            <?php if (isLoggedIn()): ?>
                <a href="add_post.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Ask a Question
                </a>
            <?php else: ?>
                <div class="text-muted">
                    <a href="login.php" class="btn btn-outline-primary">Login</a> to ask questions
                </div>
            <?php endif; ?>
        </div>

        <?php if (empty($posts)): ?>
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-question-circle fa-4x text-muted mb-3"></i>
                    <h5>No questions yet</h5>
                    <p class="text-muted">Be the first to ask a question and help build our knowledge base!</p>
                    <?php if (isLoggedIn()): ?>
                        <a href="add_post.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Ask the First Question
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-outline-primary">Login to Ask Questions</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="row">
                <div class="col-md-9">
                    <?php foreach ($posts as $post): ?>
                        <div class="card mb-3">
                            <div class="card-header">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <h5 class="mb-1">
                                            <a href="view_post.php?id=<?php echo $post['id']; ?>" class="text-decoration-none">
                                                <?php echo htmlspecialchars($post['title']); ?>
                                            </a>
                                        </h5>
                                        <div class="post-meta">
                                            <span class="badge bg-primary me-2"><?php echo htmlspecialchars($post['module_code']); ?></span>
                                            <span class="me-3">
                                                <i class="fas fa-user"></i> by <strong><?php echo htmlspecialchars($post['username']); ?></strong>
                                            </span>
                                            <span class="me-3">
                                                <i class="fas fa-calendar"></i> <?php echo date('M j, Y g:i A', strtotime($post['created_at'])); ?>
                                            </span>
                                            <?php if ($post['updated_at'] !== $post['created_at']): ?>
                                                <span class="text-muted">
                                                    <i class="fas fa-edit"></i> Updated
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php if (isLoggedIn() && $_SESSION['user_id'] == $post['user_id']): ?>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                                type="button" data-bs-toggle="dropdown">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item" href="edit_post.php?id=<?php echo $post['id']; ?>">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item text-danger"
                                                        href="delete_post.php?id=<?php echo $post['id']; ?>"
                                                        onclick="return confirm('Are you sure you want to delete this question?')">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="card-body">
                                <p class="card-text">
                                    <?php
                                    $preview_length = 200;
                                    $content_preview = substr($post['content'], 0, $preview_length);
                                    echo nl2br(htmlspecialchars($content_preview));
                                    if (strlen($post['content']) > $preview_length): ?>
                                        ...
                                        <br><a href="view_post.php?id=<?php echo $post['id']; ?>" class="btn btn-sm btn-outline-primary mt-2">
                                            <i class="fas fa-arrow-right"></i> Read More
                                        </a>
                                    <?php endif; ?>
                                </p>

                                <?php if ($post['image']): ?>
                                    <div class="mt-2">
                                        <img src="uploads/<?php echo htmlspecialchars($post['image']); ?>"
                                            class="img-thumbnail"
                                            style="max-width: 200px; max-height: 150px; cursor: pointer;"
                                            alt="Question image"
                                            onclick="window.open('view_post.php?id=<?php echo $post['id']; ?>', '_self')">
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="card-footer bg-light">
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <i class="fas fa-hashtag"></i> Question #<?php echo $post['id']; ?>
                                    </small>
                                    <a href="view_post.php?id=<?php echo $post['id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i> View Full Question
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="col-md-3">
                    <div class="card sticky-top" style="top: 20px;">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-chart-bar"></i> Statistics</h6>
                        </div>
                        <div class="card-body">
                            <p><strong>Total Questions:</strong> <?php echo count($posts); ?></p>
                            <p><strong>Active Users:</strong>
                                <?php
                                $unique_users = array_unique(array_column($posts, 'user_id'));
                                echo count($unique_users);
                                ?>
                            </p>
                            <p><strong>Modules Covered:</strong>
                                <?php
                                $unique_modules = array_unique(array_column($posts, 'module_id'));
                                echo count($unique_modules);
                                ?>
                            </p>
                        </div>
                    </div>

                    <div class="card mt-3">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-fire"></i> Popular Modules</h6>
                        </div>
                        <div class="card-body">
                            <?php

                            $module_counts = [];
                            foreach ($posts as $post) {
                                $key = $post['module_code'];
                                if (!isset($module_counts[$key])) {
                                    $module_counts[$key] = 0;
                                }
                                $module_counts[$key]++;
                            }
                            arsort($module_counts);
                            $top_modules = array_slice($module_counts, 0, 5, true);
                            ?>

                            <?php if (empty($top_modules)): ?>
                                <p class="text-muted">No data yet</p>
                            <?php else: ?>
                                <?php foreach ($top_modules as $module_code => $count): ?>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="badge bg-primary"><?php echo htmlspecialchars($module_code); ?></span>
                                        <span class="badge bg-secondary"><?php echo $count; ?> question<?php echo $count !== 1 ? 's' : ''; ?></span>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if (!isLoggedIn()): ?>
                        <div class="card mt-3">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="fas fa-info-circle"></i> Get Started</h6>
                            </div>
                            <div class="card-body">
                                <p>Join our student community to:</p>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-check text-success"></i> Ask questions</li>
                                    <li><i class="fas fa-check text-success"></i> Share knowledge</li>
                                    <li><i class="fas fa-check text-success"></i> Help other students</li>
                                </ul>
                                <div class="d-grid gap-2">
                                    <a href="signup.php" class="btn btn-primary btn-sm">Sign Up</a>
                                    <a href="login.php" class="btn btn-outline-primary btn-sm">Login</a>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>