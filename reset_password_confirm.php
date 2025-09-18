<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="ico/house-icon.ico">
    <title>Reset Password - Resident Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css.css">
    <style>
        .alert { border-radius: 0.75rem; border: none; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .password-reset-container {
            margin-top: 80px;
        }
    </style>
</head>
<body>
    <?php include("topbar.php"); ?>
    
    <div class="container password-reset-container d-flex justify-content-center align-items-center min-vh-100">
        <div class="card p-4 shadow-sm" style="max-width: 500px; width: 100%; border-radius: 12px; border: none;">
            <div class="text-center mb-4">
                <div class="mb-3">
                    <img src="images/house-icon.svg" alt="House Icon" style="width: 60px; height: 60px; opacity: 0.8;">
                </div>
                <h2 class="mb-3 fw-bold" style="font-size:1.8rem; color: #23235b;">
                    <i class="bi bi-shield-lock"></i> Set New Password
                </h2>
                <p class="text-muted">Enter your new password below</p>
            </div>
            
            <form id="resetConfirmForm">
                <input type="hidden" id="token" value="<?= htmlspecialchars($_GET['token'] ?? '') ?>">
                
                <div class="mb-3">
                    <label for="new_password" class="form-label fw-semibold">New Password:</label>
                    <input type="password" name="new_password" id="new_password" class="form-control" 
                           placeholder="Enter new password" required style="border-radius: 6px; border: 1px solid #b3e0f2;"/>
                    <div class="form-text">Password must be at least 6 characters long</div>
                </div>
                
                <div class="mb-3">
                    <label for="confirm_password" class="form-label fw-semibold">Confirm Password:</label>
                    <input type="password" name="confirm_password" id="confirm_password" class="form-control" 
                           placeholder="Confirm new password" required style="border-radius: 6px; border: 1px solid #b3e0f2;"/>
                </div>
                
                <button type="submit" class="btn btn-primary w-100 mb-3" style="border-radius: 6px; padding: 0.8rem;">
                    <i class="bi bi-check-circle"></i> Reset Password
                </button>
            </form>
            
            <div class="text-center">
                <a href="resident/index.php" class="text-decoration-none fw-semibold" style="color: #007bff;">
                    <i class="bi bi-arrow-left"></i> Back to Login
                </a>
            </div>
            
            <!-- Results will be displayed here -->
            <div id="result" class="mt-3"></div>
        </div>
    </div>

    <script>
        const API_URL = 'https://siewyaoying.synergy-college.org/ResidentManagementSystem/api.php';

        // Check if token is provided and verify it
        const token = document.getElementById('token').value;
        if (!token) {
            document.getElementById('result').innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i> No reset token provided. Please use the password reset link from your email.
                </div>
            `;
        } else {
            // Verify the token and get account information
            verifyToken(token);
        }

        function verifyToken(token) {
            const resultDiv = document.getElementById('result');
            resultDiv.innerHTML = '<div class="alert alert-info"><i class="bi bi-hourglass-split"></i> Verifying reset link...</div>';

            fetch(API_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify({ type: 'verify_reset_token', token })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Token verification failed');
                }
                return response.json();
            })
            .then(data => {
                if (!data.error) {
                    // Show account information
                    resultDiv.innerHTML = `
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle"></i> Reset link verified successfully
                            <br><br>
                            <strong>Account:</strong> ${data.email}<br>
                            <strong>Type:</strong> ${data.user_type.charAt(0).toUpperCase() + data.user_type.slice(1)}<br>
                            <strong>Expires:</strong> ${new Date(data.expires_at).toLocaleString()}
                        </div>
                    `;
                } else {
                    resultDiv.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle"></i> ${data.message}
                            <br><br>
                            <a href="reset_password.php" class="btn btn-primary">
                                <i class="bi bi-arrow-left"></i> Request New Reset Link
                            </a>
                        </div>
                    `;
                    // Disable the form if token is invalid
                    document.getElementById('resetConfirmForm').style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                resultDiv.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i> Failed to verify reset link. Please try again.
                        <br><br>
                        <a href="reset_password.php" class="btn btn-primary">
                            <i class="bi bi-arrow-left"></i> Request New Reset Link
                        </a>
                    </div>
                `;
                document.getElementById('resetConfirmForm').style.display = 'none';
            });
        }

        document.getElementById("resetConfirmForm").addEventListener("submit", e => {
            e.preventDefault();

            const new_password = document.getElementById('new_password').value;
            const confirm_password = document.getElementById('confirm_password').value;
            
            // Client-side validation
            if (new_password !== confirm_password) {
                document.getElementById('result').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i> Passwords do not match.
                    </div>
                `;
                return;
            }
            
            if (new_password.length < 6) {
                document.getElementById('result').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i> Password must be at least 6 characters long.
                    </div>
                `;
                return;
            }

            const resultDiv = document.getElementById('result');
            resultDiv.innerHTML = '<div class="alert alert-info"><i class="bi bi-hourglass-split"></i> Resetting password...</div>';

            const type = "reset_password";
            fetch(API_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify({ 
                    type, 
                    token, 
                    new_password, 
                    confirm_password 
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Request failed');
                }
                return response.json();
            })
            .then(data => {
                if (!data.error) {
                    resultDiv.innerHTML = `
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle"></i> ${data.message}
                            <br><br>
                            <a href="resident/index.php" class="btn btn-primary">
                                <i class="bi bi-box-arrow-in-right"></i> Go to Login
                            </a>
                        </div>
                    `;
                    
                    // Clear the form
                    document.getElementById('resetConfirmForm').reset();
                } else {
                    resultDiv.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle"></i> ${data.message}
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                resultDiv.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i> An error occurred. Please try again.
                    </div>
                `;
            });
        });
    </script>
</body>
</html>
