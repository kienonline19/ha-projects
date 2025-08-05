<?php

$page_title = "Manage Users";
require_once 'config.php';


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

include 'header.php';
?>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="fas fa-users"></i> Manage Users</h4>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="fas fa-plus"></i> Add User
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
                                <th>Username</th>
                                <th>Email</th>
                                <th>Posts</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo $user['id']; ?></td>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><span class="badge bg-info"><?php echo $user['post_count']; ?></span></td>
                                    <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-warning" onclick="editUser(<?php echo htmlspecialchars(json_encode($user)); ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                            <button class="btn btn-sm btn-danger" onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php endif; ?>
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
                <h6 class="mb-0"><i class="fas fa-info-circle"></i> User Management</h6>
            </div>
            <div class="card-body">
                <p><strong>Total Users:</strong> <?php echo count($users); ?></p>
                <p><strong>Your Account:</strong> <?php echo htmlspecialchars($_SESSION['username']); ?></p>
                <hr>
                <small class="text-muted">
                    You can add, edit, and delete user accounts. Note that you cannot delete your own account.
                </small>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-header">
                    <h5 class="modal-title">Add New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="add_username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="add_username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="add_email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="add_email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="add_password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="add_password" name="password" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add User</button>
                </div>
            </form>
        </div>
    </div>
</div>


<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="user_id" id="edit_user_id">
                <div class="modal-header">
                    <h5 class="modal-title">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="edit_username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="edit_email" name="email" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update User</button>
                </div>
            </form>
        </div>
    </div>
</div>


<div class="modal fade" id="deleteUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="user_id" id="delete_user_id">
                <div class="modal-header">
                    <h5 class="modal-title">Delete User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete user <strong id="delete_username"></strong>?</p>
                    <p class="text-danger">This action cannot be undone and will also delete all their posts.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function editUser(user) {
        document.getElementById('edit_user_id').value = user.id;
        document.getElementById('edit_username').value = user.username;
        document.getElementById('edit_email').value = user.email;
        new bootstrap.Modal(document.getElementById('editUserModal')).show();
    }

    function deleteUser(userId, username) {
        document.getElementById('delete_user_id').value = userId;
        document.getElementById('delete_username').textContent = username;
        new bootstrap.Modal(document.getElementById('deleteUserModal')).show();
    }
</script>

<?php include 'footer.php'; ?>