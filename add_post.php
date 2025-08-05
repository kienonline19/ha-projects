<?php
$page_title = "Ask a Question";
require_once 'config.php';


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

include 'header.php';
?>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0"><i class="fas fa-question-circle"></i> Ask a Question</h4>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="title" class="form-label">Question Title *</label>
                        <input type="text" class="form-control" id="title" name="title"
                            value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>"
                            placeholder="What's your question?" maxlength="200" required>
                    </div>

                    <div class="mb-3">
                        <label for="module_id" class="form-label">Module *</label>
                        <select class="form-select" id="module_id" name="module_id" required>
                            <option value="">Select a module</option>
                            <?php foreach ($modules as $module): ?>
                                <option value="<?php echo $module['id']; ?>"
                                    <?php echo (isset($_POST['module_id']) && $_POST['module_id'] == $module['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($module['module_code'] . ' - ' . $module['module_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="content" class="form-label">Question Details *</label>
                        <textarea class="form-control" id="content" name="content" rows="6"
                            placeholder="Describe your question in detail..." required><?php echo isset($_POST['content']) ? htmlspecialchars($_POST['content']) : ''; ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="image" class="form-label">Screenshot/Image (optional)</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        <div class="form-text">Max size: 5MB. Formats: JPG, PNG, GIF</div>


                        <div id="imagePreview" class="mt-2" style="display: none;">
                            <img id="previewImg" class="img-thumbnail" style="max-width: 200px;">
                            <button type="button" class="btn btn-sm btn-danger ms-2" onclick="removePreview()">Remove</button>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Post Question
                        </button>
                        <a href="index.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-lightbulb"></i> Tips for Good Questions</h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled">
                    <li class="mb-2"><i class="fas fa-check text-success"></i> Be specific and clear in your title</li>
                    <li class="mb-2"><i class="fas fa-check text-success"></i> Include relevant code or error messages</li>
                    <li class="mb-2"><i class="fas fa-check text-success"></i> Add screenshots if helpful</li>
                    <li class="mb-2"><i class="fas fa-check text-success"></i> Search existing questions first</li>
                    <li class="mb-2"><i class="fas fa-check text-success"></i> Choose the correct module</li>
                </ul>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-image"></i> Image Guidelines</h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled">
                    <li class="mb-2"><i class="fas fa-info-circle text-info"></i> Supported formats: JPEG, PNG, GIF</li>
                    <li class="mb-2"><i class="fas fa-info-circle text-info"></i> Maximum size: 5MB</li>
                    <li class="mb-2"><i class="fas fa-info-circle text-info"></i> Screenshots of code or errors are helpful</li>
                    <li class="mb-2"><i class="fas fa-info-circle text-info"></i> Images are optional but recommended</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('title').addEventListener('input', function() {
        const maxLength = 200;
        const currentLength = this.value.length;
        const remaining = maxLength - currentLength;

        let counter = document.getElementById('title-counter');
        if (!counter) {
            counter = document.createElement('div');
            counter.id = 'title-counter';
            counter.className = 'form-text';
            this.parentNode.appendChild(counter);
        }

        counter.textContent = `${remaining} characters remaining`;
        counter.className = remaining < 20 ? 'form-text text-warning' : 'form-text text-muted';
    });


    document.getElementById('image').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {

            if (file.size > 5 * 1024 * 1024) {
                alert('File too large. Maximum size is 5MB.');
                this.value = '';
                return;
            }


            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('previewImg').src = e.target.result;
                document.getElementById('imagePreview').style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    });

    function removePreview() {
        document.getElementById('image').value = '';
        document.getElementById('imagePreview').style.display = 'none';
    }
</script>

<?php include 'footer.php'; ?>