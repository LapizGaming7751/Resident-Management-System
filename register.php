<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="ico/house-icon.ico">
    <title>Register Account</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css.css">
</head>
<body>
    <div class="container mt-5 d-flex justify-content-center align-items-center min-vh-100">
        <div class="card p-4" style="max-width: 500px; width: 100%;">
            <div class="text-center mb-4">
                <h2>Register Your Account</h2>
                <p class="text-muted">Use your invite code to create your account</p>
            </div>
            
            <form id="registerForm">
                <div class="mb-3">
                    <label for="invite_code" class="form-label">Invite Code:</label>
                    <input type="text" name="invite_code" id="invite_code" class="form-control" 
                           placeholder="Enter your invite code" required/>
                    <div class="form-text">The invite code was provided by your administrator</div>
                </div>
                
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address:</label>
                    <input type="email" name="email" id="email" class="form-control" 
                           placeholder="Enter your email address" required/>
                    <div class="form-text">Must match the email address the invite was sent to</div>
                </div>
                
                <div class="mb-3">
                    <label for="user" class="form-label">Username:</label>
                    <input type="text" name="user" id="user" class="form-control" 
                           placeholder="Choose a username" required/>
                </div>
                
                <div class="mb-3">
                    <label for="pass" class="form-label">Password:</label>
                    <input type="password" name="pass" id="pass" class="form-control" 
                           placeholder="Choose a password" required/>
                </div>
                
                <div class="mb-3">
                    <label for="confirm_pass" class="form-label">Confirm Password:</label>
                    <input type="password" name="confirm_pass" id="confirm_pass" class="form-control" 
                           placeholder="Confirm your password" required/>
                </div>
                
                <button type="submit" class="btn btn-primary w-100 mb-3">Create Account</button>
            </form>
            
            <div class="text-center">
                <a href="index.php" class="text-decoration-none">
                    <i class="bi bi-arrow-left"></i> Back to Login
                </a>
            </div>
        </div>
    </div>

    <script>
        const API_URL = 'https://siewyaoying.synergy-college.org/ResidentManagementSystem/api.php';

        // Pre-fill form fields from URL parameters
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const inviteCode = urlParams.get('invite_code');
            const email = urlParams.get('email');
            
            if (inviteCode) {
                document.getElementById('invite_code').value = inviteCode;
                // Make the invite code field read-only if it came from URL
                document.getElementById('invite_code').readOnly = true;
                document.getElementById('invite_code').style.backgroundColor = '#f8f9fa';
            }
            
            if (email) {
                document.getElementById('email').value = email;
                // Make the email field read-only if it came from URL
                document.getElementById('email').readOnly = true;
                document.getElementById('email').style.backgroundColor = '#f8f9fa';
            }
            
            // Show a message if fields were pre-filled
            if (inviteCode || email) {
                const messageDiv = document.createElement('div');
                messageDiv.className = 'alert alert-info mb-3';
                messageDiv.innerHTML = '<i class="bi bi-info-circle"></i> Your invite code and email have been pre-filled from your invitation link.';
                document.querySelector('.card').insertBefore(messageDiv, document.getElementById('registerForm'));
            }
        });

        document.getElementById("registerForm").addEventListener("submit", e => {
            e.preventDefault();

            const invite_code = document.getElementById('invite_code').value.trim();
            const email = document.getElementById('email').value.trim();
            const user = document.getElementById('user').value.trim();
            const pass = document.getElementById('pass').value;
            const confirm_pass = document.getElementById('confirm_pass').value;
            
            // Validation
            if (pass !== confirm_pass) {
                alert("Passwords do not match");
                return;
            }
            
            if (pass.length < 6) {
                alert("Password must be at least 6 characters long");
                return;
            }

            const type = "register_with_invite";
            fetch(API_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ type, invite_code, email, user, pass })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Registration failed');
                }
                return response.json();
            })
            .then(data => {
                if (!data.error) {
                    alert("Registration successful! You can now log in with your credentials.");
                    window.location.href = "index.php";
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error registering:', error);
                alert('Registration failed. Please check your invite code and try again.');
            });
        });
    </script>
</body>
</html>
