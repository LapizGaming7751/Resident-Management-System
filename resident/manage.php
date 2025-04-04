<?php session_start(); ?>

<html>
    <head>
        <title>Manage Visitors</title>
        <link rel="stylesheet" href="../css.css">
    </head>
    <body>
        <h1>Welcome, <?=$_SESSION['user']?></h1>
        
        <button onclick="window.location.href='generateQR.php';">Generate Invite</button>
        <button onclick="window.location.href='chat_resident.php';">Chat with Security</button>
        <button onclick="window.location.href='logout.php';">Logout</button>
        
        <h2>Notifications</h2>
        <div id="notifications">
        </div>

        <h2>Manage Invites</h2>
        <div id="qr">

        </div>

        
    </body>

    <script>
        const API_URL = 'http://localhost/Finals_CheckInSystem%20ai/api.php';

        async function getNotifications() {
            try {
                const response = await fetch(`${API_URL}?type=resident&created_by=<?=$_SESSION['id']?>&fetch=notifications`);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const data = await response.json();
                
                const container = document.getElementById('notifications');
                container.innerHTML = '';
                data.forEach(notification => {
                    const div = document.createElement('div');
                    div.innerHTML = `
                        <p>${notification.message} (${notification.created_at})</p>
                        <button class="read-button" onclick="readNotification(${notification.id})">Read</button>
                    `;
                    container.appendChild(div);
                });
            } catch (error) {
                console.error('Error fetching notifications:', error);
            }
        }

        async function readNotification(id) {
            try {
                const response = await fetch(API_URL, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ "type":"resident", id })
                });
                if (!response.ok) {
                    throw new Error('Failed to read notification');
                }
                const data = await response.json();
                alert(data.message);
                getNotifications();
            } catch (error) {
                console.error('Error reading notification:', error);
            }
        }

        async function getQR() {
            try {
                const response = await fetch(`${API_URL}?type=resident&created_by=<?=$_SESSION['id']?>`);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const data = await response.json();
                
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
            } catch (error) {
                console.error('Error fetching QRs:', error);
            }
        }

        async function revokeInvite(id) {
            if(confirm("Sure to revoke this invite?")) {
                try {
                    const response = await fetch(API_URL, {
                        method: 'DELETE',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ "type":"resident", id })
                    });
                    if (!response.ok) {
                        throw new Error('Failed to delete QR');
                    }
                    const data = await response.json();
                    alert(data.message);
                    getQR();
                } catch (error) {
                    console.error('Error deleting QR:', error);
                }
            }
        }

        getQR();
        getNotifications();
        // Refresh notifications every second
        setInterval(getNotifications, 1000);
    </script>
</html>