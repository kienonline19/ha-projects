<?php

$page_title = "View Question";
require_once 'config.php';

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

include 'header.php';
?>

<div class="row">
    <div class="col-md-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item active">Question</li>
            </ol>
        </nav>

        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <h4 class="mb-2"><?php echo htmlspecialchars($post['title']); ?></h4>
                        <div class="post-meta">
                            <span class="badge bg-primary me-2">
                                <?php echo htmlspecialchars($post['module_code']); ?> - <?php echo htmlspecialchars($post['module_name']); ?>
                            </span>
                            <br class="d-md-none">
                            <span class="me-3">
                                <i class="fas fa-user"></i> Asked by <strong><?php echo htmlspecialchars($post['username']); ?></strong>
                            </span>
                            <span class="me-3">
                                <i class="fas fa-calendar"></i> <?php echo date('M j, Y g:i A', strtotime($post['created_at'])); ?>
                            </span>
                            <?php if ($post['updated_at'] !== $post['created_at']): ?>
                                <br class="d-md-none">
                                <span class="text-muted">
                                    <i class="fas fa-edit"></i> Last updated: <?php echo date('M j, Y g:i A', strtotime($post['updated_at'])); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if (isLoggedIn() && $_SESSION['user_id'] == $post['user_id']): ?>
                        <div class="ms-3">
                            <div class="btn-group">
                                <a href="edit_post.php?id=<?php echo $post['id']; ?>" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <?php echo nl2br(htmlspecialchars($post['content'])); ?>
                </div>

                <?php if ($post['image']): ?>
                    <div class="mb-3">
                        <h6>Attached Image:</h6>
                        <div class="text-center">
                            <img src="uploads/<?php echo htmlspecialchars($post['image']); ?>"
                                class="img-fluid rounded shadow-sm"
                                style="max-width: 100%; height: auto; cursor: pointer;"
                                alt="Question image"
                                data-bs-toggle="modal"
                                data-bs-target="#imageModal">
                        </div>
                        <small class="text-muted">Click image to view full size</small>
                    </div>
                <?php endif; ?>
            </div>
            <div class="card-footer bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted">
                        <small>
                            <i class="fas fa-eye"></i> Question ID: #<?php echo $post['id']; ?>
                        </small>
                    </div>
                    <div>
                        <?php if (isLoggedIn()): ?>
                            <button class="btn btn-outline-secondary btn-sm" onclick="shareQuestion()">
                                <i class="fas fa-share"></i> Share
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-3 d-flex justify-content-between">
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Questions
            </a>

            <?php if (isLoggedIn()): ?>
                <a href="add_post.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Ask Another Question
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>


<?php if (isLoggedIn() && $_SESSION['user_id'] == $post['user_id']): ?>
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Question</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this question?</p>
                    <p class="text-danger"><strong>This action cannot be undone!</strong></p>
                    <div class="bg-light p-3 rounded">
                        <strong>Question:</strong> <?php echo htmlspecialchars($post['title']); ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <a href="delete_post.php?id=<?php echo $post['id']; ?>" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Delete Question
                    </a>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>


<?php if ($post['image']): ?>
    <div class="modal fade" id="imageModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Question Image</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img src="uploads/<?php echo htmlspecialchars($post['image']); ?>"
                        class="img-fluid" alt="Question image">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <a href="uploads/<?php echo htmlspecialchars($post['image']); ?>"
                        class="btn btn-primary" download>
                        <i class="fas fa-download"></i> Download
                    </a>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
    function shareQuestion() {
        const url = window.location.href;
        const title = "<?php echo addslashes($post['title']); ?>";

        if (navigator.share) {
            navigator.share({
                title: title,
                url: url
            });
        } else {

            navigator.clipboard.writeText(url).then(function() {
                alert('Question link copied to clipboard!');
            }).catch(function() {

                const textArea = document.createElement('textarea');
                textArea.value = url;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                alert('Question link copied to clipboard!');
            });
        }
    }
</script>

<?php include 'footer.php'; ?>