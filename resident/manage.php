<?php session_start(); ?>

<html>
    <head>
        <title>Manage Visitors</title>
        <link rel="stylesheet" href="../css.css">
    </head>
    <body>
        <h1>Welcome, <?=$_SESSION['user']?></h1>
        
        <button onclick="window.location.href='generateQR.php';">Generate Invite</button>
        <button onclick="window.location.href='logout.php';">Logout</button>
        
        <h2>Manage Invites</h2>
        <div id="qr">

        </div>

        
    </body>

    <script>
        const API_URL = 'https://siewyaoying.synergy-college.org/Finals_CheckInSystem/api.php';

        function getQR(){
            fetch(`${API_URL}?type=resident&created_by=<?=$_SESSION['id']?>`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                const container = document.getElementById('qr');
                container.innerHTML = '';
                data.forEach(qr => {
                    const div = document.createElement('div');
                    div.innerHTML = `
                        <img src="../qr/${qr.token}.png" alt="">
                        <p>ID: ${qr.id} | Actual Token: ${qr.token} | Expires By: ${qr.expiry}
                        <br/> Intended Visitor: ${qr.intended_visitor} | Car Plate: ${qr.plate_id}
                        <button onclick="revokeInvite(${qr.id})">Delete</button>
                        <button onclick="window.location.href='editQR.php?id=${qr.id}&token=${qr.token}&plate=${qr.plate_id}&visitor=${qr.intended_visitor}&date=${qr.expiry}'">Edit QR</button></p>
                    `;
                    container.appendChild(div);
                });
            })
            .catch(error => {
                console.error('Error fetching QRs:', error);
            });
        }

        function revokeInvite(id){
            if(confirm("Sure to revoke this invite?")){

                fetch(API_URL, {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ "type":"resident", id })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Failed to delete QR');
                    }
                    return response.json();
                })
                .then(data => {
                    alert(data.message);
                    getQR();
                })
                .catch(error => {
                    console.error('Error deleting QR:', error);
                });
            }
        }

        getQR();
    </script>
</html>