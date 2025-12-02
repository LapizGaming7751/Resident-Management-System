<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="ico/house-icon.ico">
    <title>Forgot Password - Resident Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css.css">
    <style>
        .alert { border-radius: 0.75rem; border: none; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .info-box {
            background-color: #e6f3ff;
            border: 1px solid #b3d9ff;
            border-radius: 0.75rem;
            padding: 15px;
            margin-bottom: 20px;
            font-size: 14px;
            color: #0066cc;
        }
        .password-reset-container {
            margin-top: 80px;
        }
    </style>
</head>
<body>
    <?php include("topbar.php"); ?>
    
    <div class="container password-reset-container min-vh-100 d-flex justify-content-center align-items-center">
        <div class="card p-4 d-flex flex-column align-items-center shadow-sm" style="max-width: 450px; width: 100%; border-radius: 12px; border: none;">
            <div class="text-center mb-4">
                <div class="mb-3">
                    <img src="images/house-icon.svg" alt="House Icon" style="width: 60px; height: 60px; opacity: 0.8;">
                </div>
                <h2 class="mb-3 fw-bold" style="font-size:1.8rem; color: #23235b;">
                    <i class="bi bi-key"></i> Forgot Password?
                </h2>
                <p class="text-muted">Enter your email address and we'll send you a link to reset your password</p>
            </div>
            
            <form id="resetForm" class="w-100">
                <div id="result" class="mb-3"></div>
                
                <div class="info-box">
                    <i class="bi bi-info-circle"></i> <strong>Note:</strong> Make sure to check your spam/junk folder if you don't see the email in your inbox.
                </div>
                
                <div class="mb-3">
                    <label for="user_type" class="form-label fw-semibold">Account Type</label>
                    <select name="user_type" id="user_type" class="form-select" required style="border-radius: 6px; border: 1px solid #b3e0f2;">
                        <option value="resident">Resident</option>
                        <option value="security">Security Staff</option>
                        <option value="admin">Administrator</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold">Email Address</label>
                    <input type="email" name="email" id="email" class="form-control" required 
                           placeholder="Enter your email address" style="border-radius: 6px; border: 1px solid #b3e0f2;">
                </div>
                
                <button type="submit" class="btn btn-primary w-100 mb-3" style="border-radius: 6px; padding: 0.8rem;">
                    <i class="bi bi-send"></i> Send Reset Link
                </button>
            </form>
            
            <div class="text-center mt-3">
                <small class="text-muted">
                    Remember your password? <a href="resident/index.php" class="text-decoration-none fw-semibold" style="color: #007bff;">Back to Login</a>
                </small>
            </div>
        </div>
    </div>

    <script>
        const API_URL = 'https://siewyaoying.synergy-college.org/ResidentManagementSystem/api.php';

        document.getElementById("resetForm").addEventListener("submit", e => {
            e.preventDefault();

            const email = document.getElementById('email').value.trim();
            const user_type = document.getElementById('user_type').value;
            
            const resultDiv = document.getElementById('result');
            resultDiv.innerHTML = '<div class="alert alert-info"><i class="bi bi-hourglass-split"></i> Sending reset link...</div>';

            const type = "request_password_reset";
            fetch(API_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify({ type, email, user_type })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Request failed');
                }
                return response.json();
            })
            .then(data => {
                if (!data.error) {
                    resultDiv.innerHTML = `<div class="alert alert-success">
                        <i class="bi bi-check-circle"></i> ${data.message}
                    </div>`;
                    
                    // Clear the form
                    document.getElementById('resetForm').reset();
                } else {
                    resultDiv.innerHTML = `<div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i> ${data.message}
                    </div>`;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                resultDiv.innerHTML = `<div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i> An error occurred. Please try again.
                </div>`;
            });
        });
    </script>
</body>
</html>