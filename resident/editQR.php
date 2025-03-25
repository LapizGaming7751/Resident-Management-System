<?php session_start(); ?>

<html>
    <head>
        <title>Edit Visitor</title>
        <link rel="stylesheet" href="../css.css">
    </head>
    <body>
        

        <form id="editForm">
            <h1>Edit Invite</h1>
            <img src="../qr/<?=$_GET['token']?>.png" alt="">
            <label for="guest_name">Guest Name: </label>
            <input type="text" name="guest_name" id="guest_name" value="<?=$_GET['visitor']?>"><br/>
            <label for="plate">Car Plate: </label>
            <input type="text" name="plate" id="plate" value="<?=$_GET['plate']?>"><br/>
            <label for="expiry">Visiting Date: </label>
            <input type="datetime-local" name="expiry" id="expiry" value="<?=$_GET['date']?>"><br/>
            <button type="submit">Edit QR</button>
        </form>

        <button onclick="window.location.href='manage.php'">Return to Manage</button>
    </body>

    <script>
        const API_URL = 'http://localhost/Finals_CheckInSystem/api.php';

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