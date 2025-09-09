<?php session_start(); ?>

<html>
    <head>
        <title>Edit Visitor</title>
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
                    <button type="submit" class="btn btn-primary w-100 mb-2">Edit QR</button>
                </form>
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