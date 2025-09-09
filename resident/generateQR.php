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
                <h1 class="mb-4 text-center">Generate New Invite</h1>
                <div class="mb-3 text-center text-muted" style="font-size:0.9em;">
                    Resident ID for QR creation: <strong><?=$_SESSION['id']?></strong>
                </div>
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
                <button onclick="window.location.href='manage.php'" class="btn btn-outline-primary w-100">Return to Manage</button>
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