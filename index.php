<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/x-icon" href="ico/house-icon.ico">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resident Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css.css">
    <style>
        .feature-card {
            background: rgba(35, 35, 91, 0.08);
            box-shadow: 0 4px 24px rgba(24, 24, 74, 0.12);
            border: none;
        }
        .feature-icon {
            font-size: 2rem;
        }
        .footer {
            background: #23235b;
            color: #fff;
            opacity: 0.95;
        }
    </style>
</head>
<body>
    <?php include("topbar.php");?>
    <div class="container text-center" style="margin-top: 80px;">
        <img src="images/house-icon.svg" alt="House Icon" style="width:120px;height:120px;">
        <h1 class="mt-4 fw-bold">Resident Management System</h1>
        <p class="lead">Modern residential management system for seamless resident and visitor management.</p>
        <div class="mt-4 mb-5">
            <?php
            if (isset($_SESSION['type'])) {
                if ($_SESSION['type'] === 'admin') {
                    echo '<a href="admin/manage.php" class="btn btn-primary btn-lg mx-2">Go to Admin Panel</a>';
                } elseif ($_SESSION['type'] === 'security') {
                    echo '<a href="security/scanner.php" class="btn btn-primary btn-lg mx-2">Go to Scanner</a>';
                } elseif ($_SESSION['type'] === 'resident') {
                    echo '<a href="resident/manage.php" class="btn btn-primary btn-lg mx-2">Go to Resident Dashboard</a>';
                }
            } else {
                echo '<a href="resident/index.php" class="btn btn-primary btn-lg mx-2">Sign In</a>';
                echo '<a href="register.php" class="btn btn-outline-primary btn-lg mx-2">Register with Invite Code</a>';
            }
            ?>
        </div>
    </div>

    <div class="w-100 px-5 py-5" style="background: rgba(35, 35, 91, 0.2); box-shadow: 0 4px 24px rgba(24, 24, 74, 0.24);">
        <div class="container">
            <h2 class="fw-bold mb-4 text-center" style="font-size:2rem;"><span style="font-size:1.5rem;">✨</span> Key Features</h2>
            <div class="row justify-content-center">
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm feature-card bg-white bg-opacity-25">
                        <div class="card-body">
                            <div class="feature-icon">🔑</div>
                            <h5 class="card-title mt-2">QR Code Access</h5>
                            <p class="card-text">Generate and manage QR codes for secure access control and real-time verification.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm feature-card bg-white bg-opacity-25">
                        <div class="card-body">
                            <div class="feature-icon">👥</div>
                            <h5 class="card-title mt-2">User Management</h5>
                            <p class="card-text">Comprehensive user roles with admin and resident access levels, complete with profile management and permissions.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm feature-card bg-white bg-opacity-25">
                        <div class="card-body">
                            <div class="feature-icon">📊</div>
                            <h5 class="card-title mt-2">Real-time Monitoring</h5>
                            <p class="card-text">Track check-ins, check-outs, and access with detailed reporting and monitoring capabilities.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm feature-card bg-white bg-opacity-25">
                        <div class="card-body">
                            <div class="feature-icon">📱</div>
                            <h5 class="card-title mt-2">Mobile Friendly</h5>
                            <p class="card-text">Responsive design that works perfectly on all devices - desktop, tablet, and mobile for on-the-go access.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm feature-card bg-white bg-opacity-25">
                        <div class="card-body">
                            <div class="feature-icon">🔒</div>
                            <h5 class="card-title mt-2">Secure &amp; Encrypted</h5>
                            <p class="card-text">Advanced security features including password encryption, session management, and secure token-based authentication.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm feature-card bg-white bg-opacity-25">
                        <div class="card-body">
                            <div class="feature-icon">⚡</div>
                            <h5 class="card-title mt-2">Easy to Use</h5>
                            <p class="card-text">Intuitive interface designed for both administrators and residents with streamlined workflows and clear navigation.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row text-center mt-4 mb-2">
            <div class="col-3"><span class="fw-bold" style="font-size:1.5rem;">100%</span><br>Secure</div>
            <div class="col-3"><span class="fw-bold" style="font-size:1.5rem;">24/7</span><br>Monitoring</div>
            <div class="col-3"><span class="fw-bold" style="font-size:1.5rem;">Fast</span><br>Access</div>
            <div class="col-3"><span class="fw-bold" style="font-size:1.5rem;">Easy</span><br>Setup</div>
        </div>
    </div>

    <div class="w-100 footer mt-0">
        <footer class="text-center py-4">
            &copy; 2025 Resident Management System. Made with PHP.
        </footer>
    </div>
</body>
</html>
