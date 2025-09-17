<?php
// Password reset confirmation page
session_start();

// If already logged in, redirect to manage.php
if (isset($_SESSION['id'])) {
    header('Location: manage.php');
    exit();
}

$message = '';
$message_type = '';
$token = $_GET['token'] ?? '';
$valid_token = false;
$resident_id = null;

// Validate token
if (!empty($token)) {
    try {
        $conn = new mysqli('localhost', 'root', '', 'finals_scanner');
        if ($conn->connect_error) {
            throw new Exception('Database connection failed.');
        }
        
        // Check if token exists and is not expired
        $stmt = $conn->prepare('SELECT id FROM residents WHERE reset_token = ? AND reset_token_expiry > NOW()');
        $stmt->bind_param('s', $token);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $valid_token = true;
            $resident = $result->fetch_assoc();
            $resident_id = $resident['id'];
        } else {
            $message = 'Invalid or expired reset link. Please request a new password reset.';
            $message_type = 'danger';
        }
        
        $stmt->close();
        $conn->close();
    } catch (Exception $e) {
        error_log("Token validation error: " . $e->getMessage());
        $message = 'An error occurred. Please try again.';
        $message_type = 'danger';
    }
} else {
    $message = 'No reset token provided.';
    $message_type = 'danger';
}

// Handle password reset form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid_token) {
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validate passwords
    if (empty($new_password)) {
        $message = 'Please enter a new password.';
        $message_type = 'danger';
    } elseif (strlen($new_password) < 8) {
        $message = 'Password must be at least 8 characters long.';
        $message_type = 'danger';
    } elseif ($new_password !== $confirm_password) {
        $message = 'Passwords do not match.';
        $message_type = 'danger';
    } else {
        try {
            $conn = new mysqli('localhost', 'root', '', 'finals_scanner');
            if ($conn->connect_error) {
                throw new Exception('Database connection failed.');
            }
            
            // Hash the new password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            // Update password and clear reset token - FIXED: using 'pass' column instead of 'password'
            $stmt = $conn->prepare('UPDATE residents SET pass = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id = ?');
            $stmt->bind_param('si', $hashed_password, $resident_id);
            
            if ($stmt->execute() && $stmt->affected_rows === 1) {
                $message = 'Your password has been successfully reset. You can now log in with your new password.';
                $message_type = 'success';
                $valid_token = false; // Prevent form from showing again
            } else {
                throw new Exception('Failed to update password.');
            }
            
            $stmt->close();
            $conn->close();
        } catch (Exception $e) {
            error_log("Password reset error: " . $e->getMessage());
            $message = 'An error occurred while resetting your password. Please try again.';
            $message_type = 'danger';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Resident Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="../ico/house-icon.ico">
    <style>
        body { 
            background: linear-gradient(135deg, #e0f7ff 0%, #e0cfff 100%); 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .card { 
            box-shadow: 0 8px 32px rgba(0,0,0,0.1); 
            border-radius: 1rem; 
            border: none;
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
        }
        .btn-primary {
            background: linear-gradient(45deg, #007bff, #0056b3);
            border: none;
            padding: 12px;
            font-weight: 500;
        }
        .btn-primary:hover {
            background: linear-gradient(45deg, #0056b3, #004085);
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        .btn-success {
            background: linear-gradient(45deg, #28a745, #20c997);
            border: none;
            padding: 12px;
            font-weight: 500;
        }
        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        h2 {
            color: #2c3e50;
            font-weight: 600;
        }
        .alert {
            border-radius: 0.75rem;
            border: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .password-requirements {
            font-size: 0.875rem;
            color: #6c757d;
        }
        .password-strength {
            height: 4px;
            border-radius: 2px;
            margin-top: 0.5rem;
            transition: all 0.3s ease;
        }
    </style>
</head>
<body>
    <div class="container min-vh-100 d-flex justify-content-center align-items-center">
        <div class="card p-5" style="max-width: 500px; width: 100%;">
            <?php if ($valid_token): ?>
                <div class="text-center mb-4">
                    <h2 class="mb-2">Create New Password</h2>
                    <p class="text-muted">Please enter your new password below.</p>
                </div>
                
                <?php if ($message): ?>
                    <div class="alert alert-<?= $message_type ?> mb-4" role="alert">
                        <?= htmlspecialchars($message) ?>
                    </div>
                <?php endif; ?>
                
                <form method="post" id="resetForm" novalidate>
                    <div class="mb-3">
                        <label for="new_password" class="form-label fw-semibold">New Password</label>
                        <input 
                            type="password" 
                            class="form-control form-control-lg" 
                            id="new_password" 
                            name="new_password" 
                            placeholder="Enter your new password"
                            required
                            minlength="8"
                        >
                        <div class="password-strength" id="strengthBar"></div>
                        <div class="password-requirements mt-2">
                            <small>Password must be at least 8 characters long</small>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="confirm_password" class="form-label fw-semibold">Confirm Password</label>
                        <input 
                            type="password" 
                            class="form-control form-control-lg" 
                            id="confirm_password" 
                            name="confirm_password" 
                            placeholder="Confirm your new password"
                            required
                        >
                        <div id="passwordMatch" class="mt-1"></div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-lg w-100 mb-3" id="submitBtn">
                        Reset Password
                    </button>
                </form>
            <?php else: ?>
                <div class="text-center mb-4">
                    <h2 class="mb-2">Reset Password</h2>
                </div>
                
                <div class="alert alert-<?= $message_type ?> mb-4" role="alert">
                    <?= htmlspecialchars($message) ?>
                </div>
                
                <?php if ($message_type === 'success'): ?>
                    <div class="text-center">
                        <a href="index.php" class="btn btn-success btn-lg">
                            Go to Login
                        </a>
                    </div>
                <?php else: ?>
                    <div class="text-center">
                        <a href="reset_password.php" class="btn btn-primary me-2">Request New Reset Link</a>
                        <a href="index.php" class="btn btn-outline-secondary">Back to Login</a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Password strength and validation
        document.addEventListener('DOMContentLoaded', function() {
            const newPassword = document.getElementById('new_password');
            const confirmPassword = document.getElementById('confirm_password');
            const strengthBar = document.getElementById('strengthBar');
            const passwordMatch = document.getElementById('passwordMatch');
            const submitBtn = document.getElementById('submitBtn');
            
            if (!newPassword || !confirmPassword) return;
            
            function checkPasswordStrength(password) {
                let strength = 0;
                let color = '#dc3545'; // Red
                
                if (password.length >= 8) strength++;
                if (/[A-Z]/.test(password)) strength++;
                if (/[a-z]/.test(password)) strength++;
                if (/[0-9]/.test(password)) strength++;
                if (/[^A-Za-z0-9]/.test(password)) strength++;
                
                if (strength >= 3) color = '#ffc107'; // Yellow
                if (strength >= 4) color = '#28a745'; // Green
                
                strengthBar.style.width = (strength * 20) + '%';
                strengthBar.style.backgroundColor = color;
                
                return strength >= 2;
            }
            
            function checkPasswordMatch() {
                const match = newPassword.value === confirmPassword.value && confirmPassword.value !== '';
                
                if (confirmPassword.value === '') {
                    passwordMatch.innerHTML = '';
                } else if (match) {
                    passwordMatch.innerHTML = '<small class="text-success">✓ Passwords match</small>';
                } else {
                    passwordMatch.innerHTML = '<small class="text-danger">✗ Passwords do not match</small>';
                }
                
                return match;
            }
            
            function updateSubmitButton() {
                const strongEnough = checkPasswordStrength(newPassword.value);
                const passwordsMatch = checkPasswordMatch();
                
                submitBtn.disabled = !strongEnough || !passwordsMatch || newPassword.value.length < 8;
            }
            
            newPassword.addEventListener('input', updateSubmitButton);
            confirmPassword.addEventListener('input', updateSubmitButton);
            
            // Initial check
            updateSubmitButton();
        });
    </script>
</body>
</html>