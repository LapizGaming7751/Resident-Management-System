<?php
// Include secure configuration
require_once '../config.php';

configureSecureSession();

// Check if user is logged in as admin with level 2 or higher
if (!isset($_SESSION['id']) || $_SESSION['type'] !== 'admin' || $_SESSION['access_level'] < 2) {
    echo '<div style="color:red;text-align:center;margin-top:2em;">Error: Insufficient permissions. Only high-level admins can register new admins.</div>';
    exit;
}

// Generate CSRF token
$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../ico/house-icon.ico">
    <title>Register New Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css.css">
</head>
<body>
    <?php include('../topbar.php'); ?>
    
    <div class="main-content">
        <?php $current_page = 'register_admin'; include 'sidebar.php'; ?>
        
        <div class="container-fluid p-3 p-md-4">
            <div class="row justify-content-center">
                <div class="col-12 col-lg-8 col-xl-6">
                    <div class="card p-3 p-md-4">
                        <h2 class="h4 h3-md mb-3">Register New Admin</h2>
                        
                        <div id="error-message" class="alert alert-danger" style="display: none;"></div>
                        <div id="success-message" class="alert alert-success" style="display: none;"></div>
                        
                        <form id="registerForm">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" name="username" id="username" class="form-control" placeholder="Enter username" required minlength="3" maxlength="50" />
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" name="email" id="email" class="form-control" placeholder="admin@example.com" required />
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" name="password" id="password" class="form-control" placeholder="Enter password" required minlength="6" />
                                <small class="form-text text-muted">Password must be at least 6 characters long</small>
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm Password</label>
                                <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Confirm password" required />
                            </div>
                            <div class="mb-3">
                                <label for="access_level" class="form-label">Access Level</label>
                                <select name="access_level" id="access_level" class="form-control" required>
                                    <option value="1">Level 1 - Standard Admin</option>
                                    <option value="2">Level 2 - Super Admin (Can manage other admins)</option>
                                </select>
                                <small class="form-text text-muted">Level 2 admins can register and manage other admins</small>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" id="submit-btn" class="btn btn-primary">
                                    <span id="submit-text">Register Admin</span>
                                    <span id="submit-loading" class="spinner-border spinner-border-sm ms-2" style="display: none;"></span>
                                </button>
                                <a href="manage.php" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const API_URL = '../api.php';
        const CSRF_TOKEN = '<?= $csrf_token ?>';

        function showError(message) {
            const errorDiv = document.getElementById('error-message');
            const successDiv = document.getElementById('success-message');
            errorDiv.textContent = message;
            errorDiv.style.display = 'block';
            successDiv.style.display = 'none';
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function showSuccess(message) {
            const errorDiv = document.getElementById('error-message');
            const successDiv = document.getElementById('success-message');
            successDiv.textContent = message;
            successDiv.style.display = 'block';
            errorDiv.style.display = 'none';
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function hideMessages() {
            document.getElementById('error-message').style.display = 'none';
            document.getElementById('success-message').style.display = 'none';
        }

        function setLoading(loading) {
            const submitBtn = document.getElementById('submit-btn');
            const submitText = document.getElementById('submit-text');
            const submitLoading = document.getElementById('submit-loading');
            
            if (loading) {
                submitBtn.disabled = true;
                submitText.textContent = 'Registering...';
                submitLoading.style.display = 'inline-block';
            } else {
                submitBtn.disabled = false;
                submitText.textContent = 'Register Admin';
                submitLoading.style.display = 'none';
            }
        }

        document.getElementById("registerForm").addEventListener("submit", async e => {
            e.preventDefault();
            hideMessages();

            const username = document.getElementById('username').value.trim();
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const accessLevel = parseInt(document.getElementById('access_level').value);

            // Client-side validation
            if (username.length < 3) {
                showError('Username must be at least 3 characters long');
                return;
            }

            if (password.length < 6) {
                showError('Password must be at least 6 characters long');
                return;
            }

            if (password !== confirmPassword) {
                showError('Passwords do not match');
                return;
            }

            if (!email || !email.includes('@')) {
                showError('Please enter a valid email address');
                return;
            }

            setLoading(true);

            try {
                const response = await fetch(API_URL, {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ 
                        type: 'register_admin',
                        username: username,
                        email: email,
                        password: password,
                        access_level: accessLevel,
                        csrf_token: CSRF_TOKEN
                    })
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                
                if (data.error) {
                    showError(data.message || 'Failed to register admin');
                } else {
                    showSuccess(data.message || 'Admin registered successfully!');
                    
                    // Clear form
                    document.getElementById('registerForm').reset();
                    
                    // Redirect after 2 seconds
                    setTimeout(() => {
                        window.location.href = 'manage.php';
                    }, 2000);
                }
            } catch (err) {
                console.error('Error registering admin:', err);
                showError('Error registering admin: ' + err.message);
            } finally {
                setLoading(false);
            }
        });
    </script>

    <!-- Mobile JavaScript -->
    <script src="../js/mobile.js"></script>
</body>
</html>