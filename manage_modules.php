<?php

$page_title = "Manage Modules";
require_once 'config.php';


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

include 'header.php';
?>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="fas fa-book"></i> Manage Modules</h4>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModuleModal">
                    <i class="fas fa-plus"></i> Add Module
                </button>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Module Code</th>
                                <th>Module Name</th>
                                <th>Posts</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($modules as $module): ?>
                                <tr>
                                    <td><?php echo $module['id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($module['module_code']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($module['module_name']); ?></td>
                                    <td><span class="badge bg-info"><?php echo $module['post_count']; ?></span></td>
                                    <td><?php echo date('M j, Y', strtotime($module['created_at'])); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-warning" onclick="editModule(<?php echo htmlspecialchars(json_encode($module)); ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="deleteModule(<?php echo $module['id']; ?>, '<?php echo htmlspecialchars($module['module_code']); ?>')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-info-circle"></i> Module Management</h6>
            </div>
            <div class="card-body">
                <p><strong>Total Modules:</strong> <?php echo count($modules); ?></p>
                <hr>
                <small class="text-muted">
                    Modules help organize questions by subject. Students can select a module when posting questions.
                </small>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addModuleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Module</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="add_module_code" class="form-label">Module Code</label>
                        <input type="text" class="form-control" id="add_module_code" name="module_code"
                            placeholder="e.g., COMP1841" required>
                    </div>
                    <div class="mb-3">
                        <label for="add_module_name" class="form-label">Module Name</label>
                        <input type="text" class="form-control" id="add_module_name" name="module_name"
                            placeholder="e.g., Web Programming 1" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Module</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editModuleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="module_id" id="edit_module_id">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Module</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_module_code" class="form-label">Module Code</label>
                        <input type="text" class="form-control" id="edit_module_code" name="module_code" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_module_name" class="form-label">Module Name</label>
                        <input type="text" class="form-control" id="edit_module_name" name="module_name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Module</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteModuleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="module_id" id="delete_module_id">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Module</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete module <strong id="delete_module_code"></strong>?</p>
                    <p class="text-danger">This action cannot be undone and will also delete all associated posts.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete Module</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function editModule(module) {
        document.getElementById('edit_module_id').value = module.id;
        document.getElementById('edit_module_code').value = module.module_code;
        document.getElementById('edit_module_name').value = module.module_name;
        new bootstrap.Modal(document.getElementById('editModuleModal')).show();
    }

    function deleteModule(moduleId, moduleCode) {
        document.getElementById('delete_module_id').value = moduleId;
        document.getElementById('delete_module_code').textContent = moduleCode;
        new bootstrap.Modal(document.getElementById('deleteModuleModal')).show();
    }
</script>

<?php include 'footer.php'; ?>