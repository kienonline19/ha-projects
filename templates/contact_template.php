<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">
                    <i class="fas fa-envelope"></i> Contact Administrator
                    <?php if (isLoggedIn()): ?>
                        <span class="badge bg-success ms-2">Logged In</span>
                    <?php else: ?>
                        <span class="badge bg-secondary ms-2">Guest</span>
                    <?php endif; ?>
                </h4>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <?php echo $success; ?>
                        <?php if (isLoggedIn()): ?>
                            <hr>
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i> 
                                Your message is linked to your account (<?php echo htmlspecialchars($_SESSION['username']); ?>) for faster support.
                            </small>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isLoggedIn()): ?>
                    <div class="alert alert-info">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-user-check fa-2x me-3"></i>
                            <div>
                                <strong>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</strong><br>
                                <small>Your contact information is pre-filled and this message will be linked to your account for priority support.</small>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-user-times fa-2x me-3"></i>
                            <div>
                                <strong>Sending as Guest</strong><br>
                                <small>
                                    <a href="login.php" class="alert-link">Login</a> or 
                                    <a href="signup.php" class="alert-link">create an account</a> 
                                    for faster support and message tracking.
                                </small>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Your Name *</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : (isLoggedIn() ? htmlspecialchars($_SESSION['username']) : ''); ?>" 
                                       <?php echo isLoggedIn() ? 'readonly' : ''; ?>
                                       required>
                                <?php if (isLoggedIn()): ?>
                                    <small class="text-muted"><i class="fas fa-lock"></i> Pre-filled from your account</small>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">Your Email *</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : (isLoggedIn() ? htmlspecialchars($_SESSION['email']) : ''); ?>" 
                                       <?php echo isLoggedIn() ? 'readonly' : ''; ?>
                                       required>
                                <?php if (isLoggedIn()): ?>
                                    <small class="text-muted"><i class="fas fa-lock"></i> Pre-filled from your account</small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="subject" class="form-label">Subject *</label>
                        <select class="form-select" id="subject_select" onchange="updateSubject()">
                            <option value="">Choose a category (or type custom subject below)</option>
                            <option value="Account Issues">Account Issues (Login, Password, etc.)</option>
                            <option value="Technical Problems">Technical Problems (Bugs, Errors)</option>
                            <option value="Feature Request">Feature Request</option>
                            <option value="Content Moderation">Content Moderation (Report inappropriate content)</option>
                            <option value="General Inquiry">General Inquiry</option>
                        </select>
                        <input type="text" class="form-control mt-2" id="subject" name="subject" 
                               value="<?php echo isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : ''; ?>" 
                               placeholder="Enter your subject here..." required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="message" class="form-label">Message *</label>
                        <textarea class="form-control" id="message" name="message" rows="6" 
                                  placeholder="Please describe your issue or question in detail. The more information you provide, the better we can help you." required><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                        <div class="form-text">
                            <strong>Tips for better support:</strong>
                            <ul class="mb-0">
                                <li>Include error messages if any</li>
                                <li>Describe what you were trying to do</li>
                                <li>Mention your browser/device if it's a technical issue</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-paper-plane"></i> Send Message
                        </button>
                        
                        <?php if (!isLoggedIn()): ?>
                            <div class="text-end">
                                <small class="text-muted">
                                    Want faster support?<br>
                                    <a href="login.php" class="btn btn-sm btn-outline-primary">Login</a> or 
                                    <a href="signup.php" class="btn btn-sm btn-outline-success">Sign Up</a>
                                </small>
                            </div>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-info-circle"></i> Contact Information</h6>
            </div>
            <div class="card-body">
                <p><strong>System Administrator</strong></p>
                <p><i class="fas fa-envelope"></i> admin@student.ac.uk</p>
                <p><i class="fas fa-clock"></i> Response time: 24-48 hours</p>
                
                <?php if (isLoggedIn()): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-rocket"></i> 
                        <strong>Priority Support!</strong><br>
                        <small>Logged-in users receive faster responses and message tracking.</small>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-lightbulb"></i> 
                        <strong>Get Priority Support</strong><br>
                        <small>Create an account to get faster responses and track your messages.</small>
                        <div class="mt-2">
                            <a href="signup.php" class="btn btn-sm btn-primary">Sign Up Free</a>
                        </div>
                    </div>
                <?php endif; ?>
                
                <hr>
                <h6>Common Issues:</h6>
                <ul class="list-unstyled">
                    <li><i class="fas fa-check text-success"></i> Password reset problems</li>
                    <li><i class="fas fa-check text-success"></i> Account access issues</li>
                    <li><i class="fas fa-check text-success"></i> Technical problems</li>
                    <li><i class="fas fa-check text-success"></i> Content moderation</li>
                    <li><i class="fas fa-check text-success"></i> Feature suggestions</li>
                </ul>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-question-circle"></i> FAQ</h6>
            </div>
            <div class="card-body">
                <div class="accordion" id="faqAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                How do I reset my password?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Use the contact form above with subject "Account Issues" and mention that you need a password reset. Include your username or email.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                Can I track my messages?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <?php if (isLoggedIn()): ?>
                                    Yes! Your messages are automatically linked to your account for tracking and faster support.
                                <?php else: ?>
                                    Create an account to track your messages and get faster support. Anonymous messages cannot be tracked.
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                How do I report inappropriate content?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Use this form with subject "Content Moderation" and include the URL or details about the inappropriate content.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function updateSubject() {
    const select = document.getElementById('subject_select');
    const input = document.getElementById('subject');
    
    if (select.value) {
        input.value = select.value;
    }
}

document.getElementById('message').addEventListener('input', function() {
    this.style.height = 'auto';
    this.style.height = this.scrollHeight + 'px';
});
</script>