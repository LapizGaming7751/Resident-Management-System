<?php session_start(); ?>

<html>
    <head>
        <title>Edit Visitor</title>
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
                <h1 class="mb-4 text-center">Edit Invite</h1>
                <form id="editForm">
                    <div class="mb-3 text-center">
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
                    <button type="submit" class="btn btn-primary w-100 mb-2">Edit QR</button>
                </form>
                <button onclick="window.location.href='manage.php'" class="btn btn-outline-primary w-100">Return to Manage</button>
            </div>
        </div>
    </body>

    <script>
        const API_URL = 'http://localhost/Finals_CheckInSystem%20ai/api.php';

        document.getElementById("editForm").addEventListener("submit", e =>{
            e.preventDefault();

            const id = <?=$_GET['id']?>
            const name = document.getElementById('guest_name').value;
            const plate = document.getElementById('plate').value;
            const expiry = document.getElementById('expiry').value;

            fetch(API_URL, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 'type':"resident", id, 'created_by':<?=$_SESSION['id']?>, name, plate, expiry })
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
                console.error('Error editting QR:', error);
            });
        })
    </script>
</html>