<?php
// edit_post.php - Edit existing post
$page_title = "Edit Question";
require_once 'config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect('index.php');
}

$post_id = (int)$_GET['id'];
$error = '';
$success = '';

// Get post details
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

// Get modules for dropdown
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
        $image_name = $post['image']; // Keep existing image by default
        
        // Handle image removal
        if (isset($_POST['remove_image']) && $_POST['remove_image'] === '1') {
            if ($post['image'] && file_exists('uploads/' . $post['image'])) {
                unlink('uploads/' . $post['image']);
            }
            $image_name = null;
        }
        
        // Handle new image upload
        $upload_result = uploadImage();
        if ($upload_result && isset($upload_result['error'])) {
            $error = $upload_result['error'];
        } elseif ($upload_result && $upload_result['success']) {
            // Delete old image if we're replacing it
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
    // Pre-fill form with existing data
    $_POST['title'] = $post['title'];
    $_POST['content'] = $post['content'];
    $_POST['module_id'] = $post['module_id'];
}

include 'header.php';
?>

<div class="row">
    <div class="col-md-8">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="view_post.php?id=<?php echo $post_id; ?>">Question</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </nav>
        
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0"><i class="fas fa-edit"></i> Edit Question</h4>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="title" class="form-label">Question Title *</label>
                        <input type="text" class="form-control" id="title" name="title" 
                               value="<?php echo htmlspecialchars($_POST['title']); ?>" 
                               placeholder="What's your question?" maxlength="200" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="module_id" class="form-label">Module *</label>
                        <select class="form-select" id="module_id" name="module_id" required>
                            <option value="">Select a module</option>
                            <?php foreach ($modules as $module): ?>
                                <option value="<?php echo $module['id']; ?>" 
                                        <?php echo ($_POST['module_id'] == $module['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($module['module_code'] . ' - ' . $module['module_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="content" class="form-label">Question Details *</label>
                        <textarea class="form-control" id="content" name="content" rows="6" 
                                  placeholder="Describe your question in detail..." required><?php echo htmlspecialchars($_POST['content']); ?></textarea>
                    </div>
                    
                    <?php if ($post['image']): ?>
                        <div class="mb-3">
                            <label class="form-label">Current Image</label>
                            <div>
                                <img src="uploads/<?php echo htmlspecialchars($post['image']); ?>" 
                                     class="img-thumbnail mb-2" style="max-width: 200px;">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="remove_image" name="remove_image" value="1">
                                    <label class="form-check-label" for="remove_image">
                                        Remove current image
                                    </label>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label for="image" class="form-label">
                            <?php echo $post['image'] ? 'Replace Image (optional)' : 'Add Image (optional)'; ?>
                        </label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        <div class="form-text">Max size: 5MB. Formats: JPG, PNG, GIF</div>
                        
                        <!-- Preview area for new image -->
                        <div id="imagePreview" class="mt-2" style="display: none;">
                            <strong>New Image Preview:</strong><br>
                            <img id="previewImg" class="img-thumbnail" style="max-width: 200px;">
                            <button type="button" class="btn btn-sm btn-danger ms-2" onclick="removePreview()">Remove</button>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Question
                        </button>
                        <a href="view_post.php?id=<?php echo $post_id; ?>" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-info-circle"></i> Edit Information</h6>
            </div>
            <div class="card-body">
                <p><strong>Created:</strong> <?php echo date('M j, Y g:i A', strtotime($post['created_at'])); ?></p>
                <?php if ($post['updated_at'] !== $post['created_at']): ?>
                    <p><strong>Last Updated:</strong> <?php echo date('M j, Y g:i A', strtotime($post['updated_at'])); ?></p>
                <?php endif; ?>
                <hr>
                <small class="text-muted">
                    You can edit your question title, content, module assignment, and image. 
                    The updated timestamp will be automatically set when you save changes.
                </small>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-lightbulb"></i> Editing Tips</h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled">
                    <li class="mb-2"><i class="fas fa-check text-success"></i> Keep your title clear and descriptive</li>
                    <li class="mb-2"><i class="fas fa-check text-success"></i> Add more details if needed</li>
                    <li class="mb-2"><i class="fas fa-check text-success"></i> Update the module if necessary</li>
                    <li class="mb-2"><i class="fas fa-check text-success"></i> Replace images with better screenshots</li>
                </ul>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-image"></i> Image Options</h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled">
                    <li class="mb-2"><i class="fas fa-info-circle text-info"></i> Keep current image (do nothing)</li>
                    <li class="mb-2"><i class="fas fa-info-circle text-info"></i> Replace with new image (upload new)</li>
                    <li class="mb-2"><i class="fas fa-info-circle text-info"></i> Remove image completely (check box)</li>
                    <li class="mb-2"><i class="fas fa-info-circle text-info"></i> Add image if none exists</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
    // Character counter for title
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

    // Preview new uploaded image
    document.getElementById('image').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Check file size (5MB max)
            if (file.size > 5 * 1024 * 1024) {
                alert('File too large. Maximum size is 5MB.');
                this.value = '';
                return;
            }
            
            // Show preview
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

    // Handle remove image checkbox
    document.getElementById('remove_image')?.addEventListener('change', function() {
        const imageUpload = document.getElementById('image');
        const preview = document.getElementById('imagePreview');
        
        if (this.checked) {
            imageUpload.disabled = true;
            imageUpload.value = '';
            if (preview) {
                preview.style.display = 'none';
            }
        } else {
            imageUpload.disabled = false;
        }
    });
</script>

<?php include 'footer.php'; ?>