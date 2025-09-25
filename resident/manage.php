<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../ico/house-icon.ico">
    <title>Manage Visitors</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css.css">
</head>
<body>
    <?php include('../topbar.php'); ?>
    
    <!-- Mobile Sidebar Toggle Button -->
    <button class="sidebar-toggle d-md-none" onclick="toggleSidebar()">
        <i class="bi bi-list"></i>
    </button>
    
    <div class="main-content" style="margin-left: 250px; min-height: calc(100vh - 70px); padding-top: 20px;">
        <!-- Sidebar -->
        <?php $current_page = 'manage'; include 'sidebar.php'; ?>
            <!-- Main Card -->
            <div class="container-fluid" style="padding: 20px;">
                <div class="row">
                    <div class="col-12">
                        <div class="card p-4 mb-4">
                            <h2 class="mt-3">Notifications</h2>
                            <div id="notifications" class="mb-4"></div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card p-4">
                            <h2>Manage Invites</h2>
                            <div id="qr"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        
    </body>

    <script>
        const API_URL = 'https://siewyaoying.synergy-college.org/ResidentManagementSystem/api.php';

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
                const response = await fetch(`${API_URL}?type=resident&created_by=<?=$_SESSION['id']?>&fetch=qr`);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const data = await response.json();
                
                const container = document.getElementById('qr');
                container.innerHTML = '';

                console.log('Loading QR codes:', data.length, 'items');

                // Create a proper grid container
                container.innerHTML = '';
                container.className = 'qr-grid-container';
                
                data.forEach(qr => {
                    const cardDiv = document.createElement('div');
                    cardDiv.className = 'qr-card-item';
                    cardDiv.innerHTML = `
                        <div class="card qr-card-uniform">
                            <div class="card-body">
                                <div class="qr-image-container">
                                    <img src="../qr/${qr.token}.png" alt="QR Code" class="qr-image">
                                </div>
                                <div class="qr-content">
                                    <div class="qr-field"><strong>ID:</strong> ${qr.id}</div>
                                    <div class="qr-field"><strong>Token:</strong> ${qr.token}</div>
                                    <div class="qr-field"><strong>Expires:</strong> ${qr.expiry}</div>
                                    <div class="qr-field"><strong>Visitor:</strong> ${qr.intended_visitor || 'N/A'}</div>
                                    <div class="qr-field"><strong>Car Plate:</strong> ${qr.plate_id || 'N/A'}</div>
                                    <div class="qr-field"><strong>Exit Status:</strong> 
                                        <span class="badge ${qr.is_blocked == 1 ? 'bg-danger' : 'bg-success'}">
                                            ${qr.is_blocked == 1 ? 'Exit Blocked' : 'Normal'}
                                        </span>
                                    </div>
                                </div>
                                <div class="qr-actions">
                                    <button onclick="revokeInvite(${qr.id})" class="btn btn-danger btn-sm">Delete</button>
                                    <button onclick="window.location.href='editQR.php?id=${qr.id}&token=${qr.token}&plate=${qr.plate_id}&visitor=${qr.intended_visitor}&date=${qr.expiry}&is_blocked=${qr.is_blocked}'" class="btn btn-outline-primary btn-sm">Edit QR</button>
                                    <button onclick="toggleExitBlock(${qr.id}, ${qr.is_blocked})" class="btn ${qr.is_blocked == 1 ? 'btn-warning' : 'btn-outline-warning'} btn-sm">
                                        ${qr.is_blocked == 1 ? 'Unblock Exit' : 'Block Exit'}
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                    container.appendChild(cardDiv);
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

        async function toggleExitBlock(id, currentStatus) {
            const newStatus = currentStatus == 1 ? 0 : 1;
            const action = newStatus == 1 ? 'block' : 'unblock';
            
            if(confirm(`Are you sure you want to ${action} exit for this QR code?`)) {
                try {
                    const response = await fetch(API_URL, {
                        method: 'PUT',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ 
                            "type": "toggle_exit_block", 
                            id, 
                            is_blocked: newStatus 
                        })
                    });
                    if (!response.ok) {
                        throw new Error('Failed to update exit block status');
                    }
                    const data = await response.json();
                    alert(data.message);
                    getQR();
                } catch (error) {
                    console.error('Error updating exit block status:', error);
                }
            }
        }

        getQR();
        getNotifications();
        // Refresh notifications every second
        setInterval(getNotifications, 1000);
    </script>
    
    <!-- Mobile JavaScript -->
    <script src="../js/mobile.js"></script>
</html>