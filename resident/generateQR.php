<?php session_start(); ?>
<?php
if (!isset($_SESSION['id'])) {
    echo '<div style="color:red;text-align:center;margin-top:2em;">Error: Resident session not found. Please log in again.</div>';
    exit;
}
?>

<html>
    <head>
        <title>Add new Visitor</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css.css">
    </head>
    <body>
        <div class="d-flex min-vh-100" style="background: none;">
            <!-- Sidebar -->
            <div class="d-flex flex-column bg-white p-3" style="min-width:200px; height:100vh; border-radius:0; box-shadow:0 4px 16px rgba(0,0,0,0.08); justify-content:space-between; position:sticky; top:0; left:0;">
                <div>
                    <h4 class="mb-4 text-center">Welcome,<br><?=$_SESSION['user']?></h4>
                    <hr class="my-3">
                    <button onclick="window.location.href='manage.php';" class="btn btn-outline-primary w-100 mb-2">Manage QR</button>
                    <button class="btn btn-primary w-100 mb-2" disabled>Create QR</button>
                    <button onclick="window.location.href='chat_resident.php';" class="btn btn-outline-primary w-100 mb-2">Security Chat</button>
                </div>
                <button onclick="window.location.href='logout.php';" class="btn btn-danger w-100 mt-2">Logout</button>
            </div>
            <!-- Main Card -->
            <div class="container d-flex justify-content-center align-items-center flex-grow-1">
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
                    <button type="submit" class="btn btn-primary w-100 mb-2">Generate QR</button>
                </form>
            </div>
        </div>
    </body>

    <script>
        const API_URL = 'http://localhost/Finals_CheckInSystem%20ai/api.php';

        document.getElementById("generationForm").addEventListener("submit", e =>{
            e.preventDefault();

            const name = document.getElementById('guest_name').value;
            const plate = document.getElementById('plate').value;
            const expiry = document.getElementById('expiry').value;

            fetch(API_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 'type':"resident", 'created_by':<?=$_SESSION['id']?>, name, plate, expiry })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Failed to create QR');
                }
                return response.json();
            })
            .then(data => {
                if(!data.error && data.id && data.token){
                    alert(`QR Created!\nID: ${data.id}\nToken: ${data.token}\nVisitor: ${name}\nCar Plate: ${plate}\nExpiry: ${expiry}`);
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