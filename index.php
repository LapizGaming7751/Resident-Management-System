<html>
    <head>
        <title>Scanner Login</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body {
                background: linear-gradient(135deg, #e0f7ff 0%, #e0cfff 100%);
            }
            .card {
                box-shadow: 0 4px 16px rgba(0,0,0,0.08);
                border-radius: 1rem;
            }
        </style>
    </head>
    <body>
        <div class="container min-vh-100 d-flex justify-content-center align-items-center">
            <div class="card p-4" style="max-width: 400px; width: 100%;">
                <h1 class="mb-4 text-center">Login Security Guard</h1>
                <form method="POST" action="login_handler.php">
                    <div class="mb-3">
                        <label for="user" class="form-label">Username</label>
                        <input type="text" name="user" id="user" class="form-control" required />
                    </div>
                    <div class="mb-3">
                        <label for="pass" class="form-label">Password</label>
                        <input type="password" name="pass" id="pass" class="form-control" required />
                    </div>
                    <button type="submit" class="btn btn-primary w-100 mb-2">Login</button>
                </form>
                <button onclick="window.location.href='resident/index.php'" class="btn btn-outline-primary w-100">Login as Owner</button>
            </div>
        </div>
    </body>

    <!-- JS login removed: now handled by PHP form POST for proper session management -->
</html>