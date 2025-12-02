<?php
require_once '../config.php';

configureSecureSession();

if (!isset($_SESSION['id'])) {
    echo '<div style="color:red;text-align:center;margin-top:2em;">Error: Resident session not found. Please log in again.</div>';
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../ico/house-icon.ico">
    <title>Generate New Invite</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css.css?v=1">
</head>
<body>
    <?php include('../topbar.php'); ?>
    <div class="main-content" style="min-height: calc(100vh - 70px); padding-top: 20px;">
        <?php $current_page = 'generate'; include 'sidebar.php'; ?>

        <div class="container d-flex justify-content-center align-items-center" style="min-height: calc(100vh - 90px);">
            <div class="card p-4 w-100" style="max-width: 500px;">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="h4 mb-1">Generate New Invite</h1>
                        <p class="text-muted mb-0">Create a QR code for your visitor</p>
                    </div>
                    <button class="btn btn-outline-secondary btn-sm" onclick="window.location.href='manage.php'">
                        <i class="bi bi-arrow-left"></i> Back
                    </button>
                </div>

                <form id="generationForm" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="guest_name" class="form-label">Guest Name</label>
                        <input type="text" name="guest_name" id="guest_name" class="form-control" placeholder="Enter guest full name" required>
                        <div class="invalid-feedback">Please enter the guest's name.</div>
                    </div>
                    <div class="mb-3">
                        <label for="plate" class="form-label">Car Plate (Optional)</label>
                        <input type="text" name="plate" id="plate" class="form-control" placeholder="ABC1234">
                    </div>
                    <div class="mb-4">
                        <label for="expiry" class="form-label">Visiting Date & Time</label>
                        <input type="datetime-local" name="expiry" id="expiry" class="form-control" required>
                        <div class="invalid-feedback">Please select a visiting date and time.</div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Generate QR</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        const API_URL = '../api.php';
        const RESIDENT_ID = <?= (int)$_SESSION['id'] ?>;

        document.getElementById("generationForm").addEventListener("submit", e => {
            e.preventDefault();

            const form = e.target;
            if (!form.checkValidity()) {
                form.classList.add('was-validated');
                return;
            }

            const name = document.getElementById('guest_name').value.trim();
            const plate = document.getElementById('plate').value.trim();
            const expiry = document.getElementById('expiry').value;

            fetch(API_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ type: "resident", created_by: RESIDENT_ID, name, plate, expiry })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Failed to create QR');
                }
                return response.json();
            })
            .then(data => {
                alert(data.message || 'QR generated successfully.');
                if (!data.error) {
                    window.location.href = "manage.php";
                }
            })
            .catch(error => {
                console.error('Error creating QR:', error);
                alert('Unable to create QR right now. Please try again.');
            });
        });
    </script>
</body>
</html>