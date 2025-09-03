<?php session_start(); ?>

<html>
    <head>
        <title>Manage Visitors</title>
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
            <div class="card p-4" style="max-width: 700px; width: 100%;">
                <h1 class="mb-4 text-center">Welcome, <?=$_SESSION['user']?></h1>
                <div class="d-flex flex-wrap gap-2 mb-4 justify-content-center">
                    <button onclick="window.location.href='generateQR.php';" class="btn btn-primary">Generate Invite</button>
                    <button onclick="window.location.href='chat_resident.php';" class="btn btn-outline-primary">Chat with Security</button>
                    <button onclick="window.location.href='logout.php';" class="btn btn-danger">Logout</button>
                </div>
                <h2 class="mt-3">Notifications</h2>
                <div id="notifications" class="mb-4"></div>
                <h2>Manage Invites</h2>
                <div id="qr"></div>
            </div>
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
                const row = document.createElement('div');
                row.className = 'row g-3';
                data.forEach(qr => {
                    const col = document.createElement('div');
                    col.className = 'col-md-6 col-lg-4';
                    col.innerHTML = `
                        <div class="card h-100">
                            <div class="card-body d-flex flex-column align-items-center">
                                <img src="../qr/${qr.token}.png" alt="QR Code" class="mb-3" style="width: 128px; height: 128px; object-fit: contain;">
                                <div class="mb-2 w-100">
                                    <div><strong>ID:</strong> ${qr.id}</div>
                                    <div><strong>Token:</strong> ${qr.token}</div>
                                    <div><strong>Expires:</strong> ${qr.expiry}</div>
                                    <div><strong>Visitor:</strong> ${qr.intended_visitor}</div>
                                    <div><strong>Car Plate:</strong> ${qr.plate_id}</div>
                                </div>
                                <div class="d-flex gap-2 mt-auto w-100">
                                    <button onclick="revokeInvite(${qr.id})" class="btn btn-danger btn-sm flex-fill">Delete</button>
                                    <button onclick="window.location.href='editQR.php?id=${qr.id}&token=${qr.token}&plate=${qr.plate_id}&visitor=${qr.intended_visitor}&date=${qr.expiry}'" class="btn btn-outline-primary btn-sm flex-fill">Edit QR</button>
                                </div>
                            </div>
                        </div>
                    `;
                    row.appendChild(col);
                });
                container.appendChild(row);
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