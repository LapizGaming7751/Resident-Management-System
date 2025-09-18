<?php session_start(); ?>
<?php
if (!isset($_SESSION['id'])) {
    echo '<div style="color:red;text-align:center;margin-top:2em;">Error: Resident session not found. Please log in again.</div>';
    exit;
}
?>

<html>
    <head>
        <link rel="icon" type="image/x-icon" href="../ico/house-icon.ico">
        <title>Add new Visitor</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css.css">
    </head>
    <body>
        <?php include('../topbar.php'); ?>
        <div class="main-content" style="margin-left: 250px; min-height: calc(100vh - 70px); padding-top: 20px;">
            <!-- Sidebar -->
            <?php $current_page = 'generate'; include 'sidebar.php'; ?>
            <!-- Main Card -->
            <div class="container d-flex justify-content-center align-items-center" style="min-height: calc(100vh - 90px);">
                <div class="card p-4 d-flex flex-column align-items-center" style="max-width: 400px; width: 100%;">
                <h1 class="mb-4 text-center">Generate New Invite</h1>
                <form id="generationForm">
                    <div class="mb-3">
                        <label for="guest_name" class="form-label">Guest Name</label>
                        <input type="text" name="guest_name" id="guest_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="plate" class="form-label">Car Plate</label>
                        <input type="text" name="plate" id="plate" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="expiry" class="form-label">Visiting Date</label>
                        <input type="datetime-local" name="expiry" id="expiry" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address (Optional)</label>
                        <input type="email" name="email" id="email" class="form-control" placeholder="Enter email to send QR code">
                        <div class="form-text">If provided, the QR code will be sent to this email address</div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 mb-2">Generate QR</button>
                </form>
            </div>
        </div>
    </body>

    <script>
        const API_URL = 'https://siewyaoying.synergy-college.org/ResidentManagementSystem/api.php';

        document.getElementById("generationForm").addEventListener("submit", e =>{
            e.preventDefault();

            const name = document.getElementById('guest_name').value;
            const plate = document.getElementById('plate').value;
            const expiry = document.getElementById('expiry').value;
            const email = document.getElementById('email').value.trim();

            fetch(API_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 'type':"resident", 'created_by':<?=$_SESSION['id']?>, name, plate, expiry, email })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Failed to create QR');
                }
                return response.json();
            })
            .then(data => {
                if(!data.error && data.id && data.token){
                    let message = `QR Created!\nID: ${data.id}\nToken: ${data.token}\nVisitor: ${name}\nCar Plate: ${plate}\nExpiry: ${expiry}`;
                    if (data.email_sent) {
                        message += `\n\nQR code has been sent to: ${email}`;
                    } else if (email && !data.email_sent) {
                        message += `\n\nNote: Email could not be sent to ${email}. Please share the QR code manually.`;
                    }
                    alert(message);
                } else {
                    alert(data.message || 'QR creation failed.');
                }
                if(!data.error){
                    window.location.href = "manage.php";
                }
            })
            .catch(error => {
                alert('Error creating QR: ' + error);
                console.error('Error creating QR:', error);
            });
        })
    </script>
</html>