<?php
// Resident password reset page
session_start();

// If already logged in, redirect to manage.php
if (isset($_SESSION['id'])) {
    header('Location: manage.php');
    exit();
}

// Handle form submission
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email)) {
        $message = 'Please enter your email address.';
        $message_type = 'danger';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please enter a valid email address.';
        $message_type = 'danger';
    } else {
        try {
            // Connect to database
            $conn = new mysqli('localhost', 'root', '', 'finals_scanner');
            if ($conn->connect_error) {
                throw new Exception('Database connection failed.');
            }
            
            // Use prepared statement to prevent SQL injection
            $stmt = $conn->prepare('SELECT id FROM residents WHERE email = ?');
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $resident = $result->fetch_assoc();
                $resident_id = $resident['id'];
                
                // Generate a secure reset token
                $token = bin2hex(random_bytes(32)); // 64 character token
                $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Store token in database using prepared statement
                $updateStmt = $conn->prepare('UPDATE residents SET reset_token = ?, reset_token_expiry = ? WHERE id = ?');
                $updateStmt->bind_param('ssi', $token, $expiry, $resident_id);
                
                if ($updateStmt->execute()) {
                    // Create reset link with proper URL encoding
                    $base_url = 'http://' . $_SERVER['HTTP_HOST'];
                    $path = dirname($_SERVER['PHP_SELF']);
                    $reset_link = $base_url . $path . '/reset_password_confirm.php?token=' . urlencode($token);
                    
                    // Email content
                    $subject = "Password Reset Request - Resident Portal";
                    $body = "Hello,\n\n";
                    $body .= "A password reset was requested for your resident account.\n\n";
                    $body .= "Please click the link below to reset your password:\n";
                    $body .= $reset_link . "\n\n";
                    $body .= "This link is valid for 1 hour only.\n\n";
                    $body .= "If you did not request this password reset, please ignore this email and your password will remain unchanged.\n\n";
                    $body .= "For security reasons, do not share this link with anyone.\n\n";
                    $body .= "Best regards,\n";
                    $body .= "Resident Portal Support";
                    
                    $headers = "From: no-reply@resident-portal.local\r\n";
                    $headers .= "Reply-To: support@resident-portal.local\r\n";
                    $headers .= "X-Mailer: PHP/" . phpversion();
                    
                    // Attempt to send email
                    if (mail($email, $subject, $body, $headers)) {
                        $message = "A password reset link has been sent to your email address. Please check your inbox and follow the instructions. The link will expire in 1 hour.";
                        $message_type = 'success';
                    } else {
                        // Log error for debugging but show generic message to user
                        error_log("Failed to send reset email to: " . $email);
                        $message = "There was an issue sending the reset email. Please try again later or contact support.";
                        $message_type = 'warning';
                    }
                } else {
                    throw new Exception('Failed to generate reset token.');
                }
                
                $updateStmt->close();
            } else {
                // Don't reveal whether email exists or not for security
                $message = "If an account with that email exists, a reset link has been sent. Please check your inbox.";
                $message_type = 'info';
            }
            
            $stmt->close();
            $conn->close();
            
        } catch (Exception $e) {
            error_log("Reset password error: " . $e->getMessage());
            $message = "An error occurred. Please try again later.";
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
    <link rel="stylesheet" href="../css.css">

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
    </style>
</head>
<body>
    <div class="container min-vh-100 d-flex justify-content-center align-items-center">
        <div class="card p-5" style="max-width: 450px; width: 100%;">
            <div class="text-center mb-4">
                <h2 class="mb-2">Reset Your Password</h2>
                <p class="text-muted">Enter your email address and we'll send you a link to reset your password.</p>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-<?= $message_type ?> mb-4" role="alert">
                    <div class="d-flex align-items-center">
                        <?php if ($message_type === 'success'): ?>
                            <svg class="me-2" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.061L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                            </svg>
                        <?php endif; ?>
                        <?= htmlspecialchars($message) ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <form method="post" novalidate>
                <div class="mb-4">
                    <label for="email" class="form-label fw-semibold">Email Address</label>
                    <input 
                        type="email" 
                        class="form-control form-control-lg" 
                        id="email" 
                        name="email" 
                        placeholder="Enter your email address"
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                        required
                    >
                    <div class="form-text">We'll send reset instructions to this email address.</div>
                </div>
                <button type="submit" class="btn btn-primary btn-lg w-100 mb-3">
                    Send Reset Link
                </button>
            </form>
            
            <div class="text-center">
                <a href="index.php" class="link-secondary text-decoration-none">
                    <svg class="me-1" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/>
                    </svg>
                    Back to Login
                </a>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>