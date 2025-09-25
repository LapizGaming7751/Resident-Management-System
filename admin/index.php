<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../ico/house-icon.ico">
    <title>Admin Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css.css">
</head>
    <body>
        <div class="container min-vh-100 d-flex justify-content-center align-items-center">
            <div class="card p-4 d-flex flex-column align-items-center" style="max-width: 400px; width: 100%;">
                <h2 class="mb-3 text-center fw-bold" style="font-size:1.6rem;">Login Admin</h2>
                <form method="POST" action="login_handler.php" class="w-100 d-flex flex-column align-items-center">
                    <div class="mb-3">
                        <label for="user" class="form-label">Username</label>
                        <input type="text" name="user" id="user" class="form-control" required />
                    </div>
                    <div class="mb-3">
                        <label for="pass" class="form-label">Password</label>
                        <input type="password" name="pass" id="pass" class="form-control" required />
                    </div>
                    <button type="submit" class="btn btn-primary w-100 mb-2">Login</button>
                    <div class="text-center mb-2">
                        <a href="../reset_password.php" class="link-primary mb-2 d-block">Forgot your password?</a>
                    </div>
                </form>
            </div>
        </div>
    </body>
</html>