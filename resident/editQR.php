<?php
// Include secure configuration
require_once '../config.php';

configureSecureSession();

if (!isset($_SESSION['id'])) {
    echo '<div style="color:red;text-align:center;margin-top:2em;">Error: Resident session not found. Please log in again.</div>';
    exit;
}

// Generate CSRF token
$csrf_token = generateCSRFToken();
?>

<html>
    <head>
        <link rel="icon" type="image/x-icon" href="../ico/house-icon.ico">
        <title>Edit Visitor</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css.css">
    </head>
    <body>
        <?php include('../topbar.php'); ?>
        <div class="main-content" style="min-height: calc(100vh - 70px); padding-top: 20px;">
            <!-- Sidebar -->
            <?php $current_page = 'generate'; include 'sidebar.php'; ?>
            <!-- Main Card -->
            <div class="container d-flex justify-content-center align-items-center" style="min-height: calc(100vh - 90px);">
                <div class="card p-4 d-flex flex-column align-items-center" style="max-width: 400px; width: 100%;">
                <h1 class="mb-4 text-center">Edit Invite</h1>
                <form id="editForm">
                    <div class="mb-3 text-center">
                        <div class="mb-2 text-center">
                            <span class="fw-bold">Token:</span> <?=$_GET['token']?><br>
                        </div>
                        <img src="../qr/<?=$_GET['token']?>.png" alt="QR Code" style="width:128px;height:128px;object-fit:contain;" class="mb-3">
                    </div>
                    <div class="mb-3">
                        <label for="guest_name" class="form-label">Guest Name</label>
                        <input type="text" name="guest_name" id="guest_name" class="form-control" value="<?=$_GET['visitor']?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="plate" class="form-label">Car Plate</label>
                        <input type="text" name="plate" id="plate" class="form-control" value="<?=$_GET['plate']?>">
                    </div>
                    <div class="mb-3">
                        <label for="expiry" class="form-label">Visiting Date</label>
                        <input type="datetime-local" name="expiry" id="expiry" class="form-control" value="<?=$_GET['date']?>" required>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_blocked" id="is_blocked" value="1" <?=isset($_GET['is_blocked']) && $_GET['is_blocked'] == 1 ? 'checked' : ''?>>
                            <label class="form-check-label" for="is_blocked">
                                Block Exit (Visitor can enter but cannot exit until unblocked)
                            </label>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 mb-2">Edit QR</button>
                </form>
            </div>
        </div>
    </body>

    <script>
        // Use relative URL to avoid hardcoded URLs
        const API_URL = '../api.php';
        const CSRF_TOKEN = '<?= $csrf_token ?>';

        // Wait for DOM to be fully loaded
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById("editForm");
            if (!form) {
                console.error('Form element not found');
                return;
            }

            form.addEventListener("submit", e => {
                e.preventDefault();

                const id = <?=$_GET['id']?>;
                const name = document.getElementById('guest_name').value;
                const plate = document.getElementById('plate').value;
                const expiry = document.getElementById('expiry').value;
                const is_blocked = document.getElementById('is_blocked').checked ? 1 : 0;

                fetch(API_URL, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ 
                        'type': "resident", 
                        'id': id, 
                        'created_by': <?=$_SESSION['id']?>, 
                        'name': name, 
                        'plate': plate, 
                        'expiry': expiry, 
                        'is_blocked': is_blocked,
                        'csrf_token': CSRF_TOKEN
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Failed to edit QR');
                    }
                    return response.json();
                })
                .then(data => {
                    alert(data.message);
                    if(!data.error){
                        window.location.href = "manage.php";
                    }
                })
                .catch(error => {
                    console.error('Error editing QR:', error);
                });
            });
        });
    </script>
</html>