<?php
session_start();
if (isset($_SESSION['type']) && $_SESSION['type']=="security"){
    

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../ico/house-icon.ico">
    <title>Scan Here</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css.css">
    <style>
        .user-item {
            padding: 10px;
            margin: 5px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .user-item:hover {
            background-color: #f8f9fa;
        }
        .user-item.active {
            background-color: #007bff;
            color: white;
        }
        .message {
            margin: 10px 0;
            padding: 10px;
            border-radius: 10px;
            max-width: 80%;
        }
        #scanner-overlay {
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 0.5rem;
        }
    </style>
    </head>
    <body>
        <?php include('../topbar.php'); ?>

        <!-- Mobile Sidebar Toggle Button -->
        <button class="sidebar-toggle d-md-none" onclick="toggleSidebar()">
            <i class="bi bi-list"></i>
        </button>

        <div class="main-content" style="margin-left: 250px; min-height: calc(100vh - 70px); padding-top: 20px;">
            <!-- Sidebar -->
            <?php $current_page = 'scanner'; include 'sidebar.php'; ?>
            <!-- Main Card -->
            <div class="container d-flex justify-content-center align-items-center" style="min-height: calc(100vh - 90px);">
                <div class="card p-4 d-flex flex-column align-items-center" style="max-width: 700px; width: 100%;">
                    <h1 class="mb-4 text-center">Visitor Entry</h1>
                    <form id="scannerForm">
                        <div class="mb-3 d-flex justify-content-center">
                            <div class="position-relative">
                                <video id="qr-video" autoplay muted playsinline style="width: 600px; height: 350px; max-width: 100%; border-radius: 0.5rem; object-fit: cover; background-color: #f8f9fa;"></video>
                                <div id="scanner-overlay" class="position-absolute top-50 start-50 translate-middle text-center" style="display: none;">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="mt-2">Initializing camera...</p>
                                </div>
                                <div id="camera-error" class="position-absolute top-50 start-50 translate-middle text-center" style="display: none;">
                                    <i class="bi bi-camera-video-off text-muted" style="font-size: 3rem;"></i>
                                    <p class="mt-2 text-muted">Camera not available</p>
                                    <p class="text-muted small">Please ensure camera permissions are granted and try refreshing the page.</p>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <input type="text" name="token" id="token" placeholder="QR Code" autocomplete="off" class="form-control">
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Enter</button>
                            <button type="button" class="btn btn-outline-secondary" onclick="restartScanner()">
                                <i class="bi bi-arrow-clockwise me-2"></i>Restart Scanner
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script src="../node_modules/qr-scanner/qr-scanner.umd.min.js"></script>
        <script>
            const API_URL = 'https://siewyaoying.synergy-college.org/ResidentManagementSystem/api.php';
            
            const video = document.getElementById('qr-video');
            const tokenInput = document.getElementById('token');
            const scan_by = <?= isset($_SESSION['id']) ? intval($_SESSION['id']) : 0 ?>;
            let scanner = null;
            
            // Initialize QR Scanner
            function initScanner() {
                const overlay = document.getElementById('scanner-overlay');
                const errorDiv = document.getElementById('camera-error');
                
                QrScanner.hasCamera().then(hasCamera => {
                    if (!hasCamera) {
                        overlay.style.display = 'none';
                        errorDiv.style.display = 'block';
                        return;
                    }

                    overlay.style.display = 'block';
                    errorDiv.style.display = 'none';

                    scanner = new QrScanner(video, result => {
                        console.log('QR Result:', result);
                        tokenInput.value = result.data ?? result;
                        scanner.stop();
                        scanCode(tokenInput.value);
                    }, {
                        highlightScanRegion: true,
                        highlightCodeOutline: true,
                        preferredCamera: 'environment'
                    });

                    scanner.start().then(() => {
                        overlay.style.display = 'none';
                        errorDiv.style.display = 'none';
                    }).catch(err => {
                        console.error('Error starting scanner:', err);
                        overlay.style.display = 'none';
                        errorDiv.style.display = 'block';
                    });
                });
            }
            
            document.addEventListener('DOMContentLoaded', function() {
                initScanner();
            });

            function scanCode(token){
                const type = "guest";

                fetch(API_URL, {
                    method: 'POST',
                    headers: {'Content-type':'application/json'},
                    body: JSON.stringify({ type, token, scan_by })
                })
                .then(response => response.text())   // always get raw text
                .then(text => {
                    try {
                        const data = JSON.parse(text);
                        console.log("API JSON:", data);
                        
                        // Handle different response types
                        if (data.error) {
                            // Show error message with red styling
                            alert(`❌ ${data.message}`);
                        } else {
                            // Show success message with green styling
                            alert(`✅ ${data.message}`);
                        }
                    } catch (err) {
                        console.error("Raw API response (not JSON):", text);
                        alert("Server error – check PHP logs. Raw response is in console.");
                    }
                })
                .catch(error => {
                    console.error('Network/fetch error:', error);
                    alert('Error processing QR code. Please try again.');
                })
                .finally(() => {
                    if (scanner) {
                        scanner.start().catch(err => {
                            console.error('Error restarting scanner:', err);
                        });
                    }
                });
            }
            
            function restartScanner() {
                const overlay = document.getElementById('scanner-overlay');
                const errorDiv = document.getElementById('camera-error');
                
                if (scanner) {
                    scanner.stop();
                    overlay.style.display = 'block';
                    errorDiv.style.display = 'none';
                    
                    scanner.start().then(() => {
                        overlay.style.display = 'none';
                        errorDiv.style.display = 'none';
                    }).catch(err => {
                        console.error('Error restarting scanner:', err);
                        overlay.style.display = 'none';
                        errorDiv.style.display = 'block';
                    });
                } else {
                    initScanner();
                }
            }

            document.getElementById("scannerForm").addEventListener("submit", e =>{
                e.preventDefault();
                scanCode(document.getElementById('token').value);
            });
        </script>
        
        <!-- Mobile JavaScript -->
        <script src="../js/mobile.js"></script>
    </body>
</html>

<?php
}else{
    echo "Unauthorized access";
}
?>
