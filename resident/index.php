<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../ico/house-icon.ico">
    <title>Resident Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css.css">
    <style>
        .alert { border-radius: 0.75rem; border: none; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
    <div class="container min-vh-100 d-flex justify-content-center align-items-center">
        <div class="card p-4 d-flex flex-column align-items-center" style="max-width: 400px; width: 100%;">
            <h2 class="mb-3 text-center fw-bold" style="font-size:1.6rem;">Login Resident</h2>
            
            <?php
            // Display error messages based on URL parameter
            $error = $_GET['error'] ?? '';
            if ($error) {
                $error_message = '';
                switch ($error) {
                    case 'invalid_credentials':
                        $error_message = 'Invalid username or password. Please try again.';
                        break;
                    case 'missing_fields':
                        $error_message = 'Please fill in both username and password.';
                        break;
                    case 'db_error':
                        $error_message = 'System error. Please try again later.';
                        break;
                    case '1': // Legacy error code
                        $error_message = 'Login failed. Please check your credentials.';
                        break;
                }
                if ($error_message) {
                    echo '<div class="alert alert-danger mb-3" role="alert">' . htmlspecialchars($error_message) . '</div>';
                }
            }
            ?>
            
            <form method="POST" action="login_handler.php" class="w-100 d-flex flex-column align-items-center">
                <div class="mb-3 w-100">
                    <label for="user" class="form-label">Username</label>
                    <input type="text" name="user" id="user" class="form-control" 
                           value="<?= htmlspecialchars($_GET['user'] ?? '') ?>" required />
                </div>
                <div class="mb-3 w-100">
                    <label for="pass" class="form-label">Password</label>
                    <input type="password" name="pass" id="pass" class="form-control" required />
                </div>
                <button type="submit" class="btn btn-primary w-100 mb-2">Login</button>
                <div class="text-center mb-2">
                    <a href="reset_password.php" class="link-primary mb-2 d-block">Forgot your password?</a>
                </div>
                <button onclick="window.location.href='../security/index.php'" type="button" class="btn btn-outline-primary w-100">Login as Security</button>
            </form>
        </div>
    </div>
</body>
</html>