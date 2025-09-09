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
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if ($email === '') {
        $message = 'Please enter your email.';
    } else {
        // Connect to database
        $conn = new mysqli('localhost', 'root', '', 'finals_scanner');
        if ($conn->connect_error) {
            $message = 'Database connection failed.';
        } else {
            $stmt = $conn->prepare('SELECT id FROM residents WHERE email = ?');
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows === 1) {
                // Generate a simple reset token
                $token = bin2hex(random_bytes(16));
                $resident = $result->fetch_assoc();
                $resident_id = $resident['id'];
                // Store token in DB (add a column if needed)
                $conn->query("UPDATE residents SET reset_token='$token', reset_token_expiry=DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE id=$resident_id");
                $reset_link = "http://localhost/Finals_CheckInSystem%20ai/resident/reset_password_confirm.php?token=$token";
                // Send email
                $subject = "Password Reset Request";
                $body = "Hello,\n\nA password reset was requested for your resident account. Please click the link below to reset your password:\n\n$reset_link\n\nThis link is valid for 1 hour. If you did not request this, please ignore this email.";
                $headers = "From: no-reply@finals-checkin.local";
                if (mail($email, $subject, $body, $headers)) {
                    $message = "A reset link has been sent to your email address. Please check your inbox.";
                } else {
                    $message = "Failed to send email. Please contact support or try again later.";
                }
            } else {
                $message = 'No resident found with that email.';
            }
            $stmt->close();
            $conn->close();
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Reset Resident Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #e0f7ff 0%, #e0cfff 100%); }
        .card { box-shadow: 0 4px 16px rgba(0,0,0,0.08); border-radius: 1rem; }
    </style>
</head>
<body>
    <div class="container min-vh-100 d-flex justify-content-center align-items-center">
        <div class="card p-4" style="max-width: 400px; width: 100%;">
            <h2 class="mb-3 text-center">Reset Password</h2>
            <?php if ($message): ?>
                <div class="alert alert-info"><?=$message?></div>
            <?php endif; ?>
            <form method="post">
                <div class="mb-3">
                    <label for="email" class="form-label">Email address</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Send Reset Link</button>
            </form>
            <div class="mt-3 text-center">
                <a href="login_handler.php" class="link-secondary">Back to Login</a>
            </div>
        </div>
    </div>
</body>
</html>
