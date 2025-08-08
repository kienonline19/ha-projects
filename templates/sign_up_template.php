<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0"><i class="fas fa-user-plus"></i> Sign Up</h4>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <?php echo $success; ?>
                        <div class="mt-2">
                            <a href="login.php" class="btn btn-sm btn-primary">Go to Login</a>
                        </div>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username"
                            value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                            pattern="[a-zA-Z0-9_]+"
                            title="Username can only contain letters, numbers, and underscores"
                            minlength="3" maxlength="20" required>
                        <div class="form-text">3-20 characters, letters, numbers, and underscores only</div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email"
                            value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                        <div class="form-text">We'll never share your email with anyone else.</div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password"
                            minlength="6" required>
                        <div class="form-text">Password must be at least 6 characters long.</div>
                    </div>

                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Create Account</button>
                </form>

                <div class="text-center mt-3">
                    <p>Already have an account? <a href="login.php">Login here</a></p>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-shield-alt"></i> Account Security</h6>
            </div>
            <div class="card-body">
                <h6>Password Requirements:</h6>
                <ul class="list-unstyled">
                    <li><i class="fas fa-check text-success"></i> At least 6 characters long</li>
                    <li><i class="fas fa-check text-success"></i> Use a unique password</li>
                    <li><i class="fas fa-check text-success"></i> Consider using numbers and symbols</li>
                </ul>

                <hr>

                <h6>Privacy:</h6>
                <ul class="list-unstyled">
                    <li><i class="fas fa-check text-success"></i> Your email is kept private</li>
                    <li><i class="fas fa-check text-success"></i> Only your username is visible</li>
                    <li><i class="fas fa-check text-success"></i> You can delete your account anytime</li>
                </ul>

                <hr>

                <small class="text-muted">
                    By creating an account, you agree to use this system responsibly for academic purposes only.
                </small>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('confirm_password').addEventListener('input', function() {
        const password = document.getElementById('password').value;
        const confirmPassword = this.value;

        if (password !== confirmPassword) {
            this.setCustomValidity('Passwords do not match');
        } else {
            this.setCustomValidity('');
        }
    });


    document.getElementById('username').addEventListener('input', function() {
        const username = this.value;
        const pattern = /^[a-zA-Z0-9_]+$/;

        if (!pattern.test(username)) {
            this.setCustomValidity('Username can only contain letters, numbers, and underscores');
        } else {
            this.setCustomValidity('');
        }
    });
</script>