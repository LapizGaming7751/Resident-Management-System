<?php session_start(); ?>

<html>
    <head>
        <title>Add new Visitor</title>
        <link rel="stylesheet" href="../css.css">
    </head>
    <body>
        <h1>Generate New Invite</h1>

        <form id="generationForm">
            <label for="guest_name">Guest Name: </label>
            <input type="text" name="guest_name" id="guest_name"><br/>
            <label for="plate">Car Plate: </label>
            <input type="text" name="plate" id="plate"><br/>
            <label for="expiry">Visiting Date: </label>
            <input type="datetime-local" name="expiry" id="expiry"><br/>
            <button type="submit">Generate QR</button>
        </form>

        <button onclick="window.location.href='manage.php'">Return to Manage</button>
    </body>

    <script>
        const API_URL = 'http://localhost/Finals_CheckInSystem/api.php';

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
                alert(data.message);
                if(!data.error){
                    window.location.href = "manage.php";
                }
            })
            .catch(error => {
                console.error('Error creating QR:', error);
            });
        })
    </script>
</html>