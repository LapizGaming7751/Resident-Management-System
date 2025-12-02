<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../ico/house-icon.ico">
    <title>Notification Management</title>
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
    <?php $current_page = 'notification_management'; include('sidebar.php'); ?>

    <div class="container mt-3">
        <div class="card p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Notification Management</h2>
                <div>
                    <button class="btn btn-primary" onclick="window.location.href='sendNotification.php'">
                        <i class="bi bi-bell-plus me-2"></i>Send New Notification
                    </button>
                </div>
            </div>
            
            <!-- Statistics Cards -->
            <div class="row mb-4" id="notificationStats">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <h5 class="card-title">Total Notifications</h5>
                            <h3 id="totalNotifications">-</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body text-center">
                            <h5 class="card-title">Unread</h5>
                            <h3 id="unreadNotifications">-</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h5 class="card-title">Read</h5>
                            <h3 id="readNotifications">-</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h5 class="card-title">Today's Notifications</h5>
                            <h3 id="todayNotifications">-</h3>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Search and Filter -->
            <div class="mb-3 d-flex align-items-center" style="gap: 10px;">
                <select id="filterField" class="form-select w-auto">
                    <option value="all">All Fields</option>
                    <option value="message">Message</option>
                    <option value="resident_name">Resident</option>
                    <option value="created_at">Date</option>
                    <option value="is_read">Status</option>
                </select>
                <input type="text" id="notificationSearch" class="form-control" placeholder="Search notifications...">
                <select id="statusFilter" class="form-select w-auto">
                    <option value="all">All Status</option>
                    <option value="unread">Unread Only</option>
                    <option value="read">Read Only</option>
                </select>
            </div>
            
            <!-- Notifications Table -->
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-light">
                        <tr>
                            <th><i class="bi bi-hash"></i> ID</th>
                            <th><i class="bi bi-person"></i> Resident</th>
                            <th><i class="bi bi-chat-text"></i> Message</th>
                            <th><i class="bi bi-calendar"></i> Created At</th>
                            <th><i class="bi bi-eye"></i> Status</th>
                            <th><i class="bi bi-gear"></i> Actions</th>
                        </tr>
                    </thead>
                    <tbody id="notificationTable">
                        <tr>
                            <td colspan="6" class="text-center">Loading notifications...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
const API_URL = 'https://siewyaoying.synergy-college.org/ResidentManagementSystem/api.php';

let notificationsData = [];

// Load all notifications
async function loadNotifications() {
    try {
        // Get all residents first
        const residentsResponse = await fetch(`${API_URL}?type=admin&fetch=resident`);
        const residents = await residentsResponse.json();
        
        // Get notifications for each resident
        let allNotifications = [];
        for (const resident of residents) {
            try {
                const response = await fetch(`${API_URL}?type=resident&created_by=${resident.id}&fetch=notifications`);
                const notifications = await response.json();
                
                // Add resident info to each notification
                notifications.forEach(notification => {
                    notification.resident_name = resident.user;
                    notification.resident_room = resident.room_code;
                });
                
                allNotifications = allNotifications.concat(notifications);
            } catch (error) {
                console.error(`Error loading notifications for resident ${resident.id}:`, error);
            }
        }
        
        // Sort by created_at descending
        allNotifications.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
        
        notificationsData = allNotifications;
        renderNotifications();
        updateStats();
    } catch (error) {
        console.error('Error loading notifications:', error);
        document.getElementById('notificationTable').innerHTML = 
            '<tr><td colspan="6" class="text-center text-danger">Failed to load notifications</td></tr>';
    }
}

function renderNotifications(filtered = null) {
    const container = document.getElementById('notificationTable');
    container.innerHTML = '';
    
    const data = filtered || notificationsData;
    
    if (data.length === 0) {
        container.innerHTML = '<tr><td colspan="6" class="text-center">No notifications found</td></tr>';
        return;
    }
    
    data.forEach(notification => {
        const row = document.createElement('tr');
        const statusBadge = notification.is_read == 1 ? 
            '<span class="badge bg-success">Read</span>' : 
            '<span class="badge bg-warning">Unread</span>';
        
        const createdDate = new Date(notification.created_at).toLocaleString();
        
        row.innerHTML = `
            <td>${notification.id}</td>
            <td>
                <div class="fw-bold">${notification.resident_name || 'Unknown'}</div>
                <small class="text-muted">Room: ${notification.resident_room || 'N/A'}</small>
            </td>
            <td>
                <div style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" 
                     title="${notification.message}">
                    ${notification.message}
                </div>
            </td>
            <td>${createdDate}</td>
            <td>${statusBadge}</td>
            <td>
                <button class="btn btn-sm btn-outline-info" onclick="viewNotification(${notification.id})" title="View Details">
                    <i class="bi bi-eye"></i>
                </button>
                ${notification.is_read == 0 ? 
                    `<button class="btn btn-sm btn-outline-success" onclick="markAsRead(${notification.id})" title="Mark as Read">
                        <i class="bi bi-check"></i>
                    </button>` : 
                    `<span class="text-muted">Already Read</span>`
                }
            </td>
        `;
        container.appendChild(row);
    });
}

function updateStats() {
    const total = notificationsData.length;
    const unread = notificationsData.filter(n => n.is_read == 0).length;
    const read = total - unread;
    
    // Count today's notifications
    const today = new Date().toDateString();
    const todayCount = notificationsData.filter(n => 
        new Date(n.created_at).toDateString() === today
    ).length;
    
    document.getElementById('totalNotifications').textContent = total;
    document.getElementById('unreadNotifications').textContent = unread;
    document.getElementById('readNotifications').textContent = read;
    document.getElementById('todayNotifications').textContent = todayCount;
}

function filterNotifications() {
    const query = document.getElementById('notificationSearch').value.toLowerCase();
    const field = document.getElementById('filterField').value;
    const status = document.getElementById('statusFilter').value;
    
    let filtered = notificationsData;
    
    // Filter by status
    if (status === 'unread') {
        filtered = filtered.filter(n => n.is_read == 0);
    } else if (status === 'read') {
        filtered = filtered.filter(n => n.is_read == 1);
    }
    
    // Filter by search query
    if (query) {
        if (field === 'all') {
            filtered = filtered.filter(notification =>
                Object.values(notification).some(v => 
                    String(v).toLowerCase().includes(query)
                )
            );
        } else {
            filtered = filtered.filter(notification => 
                String(notification[field]).toLowerCase().includes(query)
            );
        }
    }
    
    renderNotifications(filtered);
}

function viewNotification(id) {
    const notification = notificationsData.find(n => n.id == id);
    if (notification) {
        alert(`Notification Details:\n\nResident: ${notification.resident_name}\nMessage: ${notification.message}\nCreated: ${new Date(notification.created_at).toLocaleString()}\nStatus: ${notification.is_read == 1 ? 'Read' : 'Unread'}`);
    }
}

async function markAsRead(id) {
    try {
        const response = await fetch(API_URL, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ type: 'resident', id: id })
        });
        
        if (response.ok) {
            // Update local data
            const notification = notificationsData.find(n => n.id == id);
            if (notification) {
                notification.is_read = 1;
            }
            
            renderNotifications();
            updateStats();
            alert('Notification marked as read');
        } else {
            alert('Failed to mark notification as read');
        }
    } catch (error) {
        console.error('Error marking notification as read:', error);
        alert('Error marking notification as read');
    }
}

// Event listeners
document.getElementById('notificationSearch').addEventListener('input', filterNotifications);
document.getElementById('filterField').addEventListener('change', filterNotifications);
document.getElementById('statusFilter').addEventListener('change', filterNotifications);

// Initialize
loadNotifications();
</script>

<!-- Bootstrap JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Mobile JavaScript -->
<script src="../js/mobile.js"></script>
</body>
</html>
