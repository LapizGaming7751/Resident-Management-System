<?php session_start(); ?>

<html>
<head>
    <link rel="icon" type="image/x-icon" href="../ico/house-icon.ico">
    <title>Admin Announcements</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css.css">
    <style>
        .announcement-card {
            margin-bottom: 15px;
            position: relative;
        }
        .announcement-time {
            font-size: 0.85rem;
            color: gray;
        }
        .announcement-actions {
            position: absolute;
            top: 10px;
            right: 10px;
        }
        .announcement-actions button {
            background: transparent;
            border: none;
            margin-left: 5px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <?php include('../topbar.php'); ?>
    <div class="main-content" style="margin-left: 250px; min-height: calc(100vh - 70px); padding-top: 20px;">
        <!-- Sidebar -->
        <?php $current_page = 'announcements'; include 'sidebar.php'; ?>
        
        <div class="container-fluid" style="padding: 20px;">
            <div class="row">
                <div class="col-12">
                    <div class="card p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h2>Announcements</h2>
                            <div class="d-flex align-items-center">
                                <input type="text" id="announcement-search" class="form-control me-2" placeholder="Search announcements...">
                                <button id="create-announcement" class="btn btn-primary" title="Create New Announcement">
                                    <i class="bi bi-plus-lg"></i>
                                </button>
                            </div>
                        </div>
                        <div id="announcements" class="mt-2"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const API_URL = 'http://localhost/Finals_CheckInSystem%20ai/api.php';
    const searchInput = document.getElementById('announcement-search');
    const container = document.getElementById('announcements');
    const createBtn = document.getElementById('create-announcement');
    let allAnnouncements = [];

    async function getAnnouncements() {
        try {
            // Fixed: Use admin type to fetch announcements
            const response = await fetch(`${API_URL}?type=admin&fetch=announcements`);
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            const data = await response.json();
            allAnnouncements = data;
            renderAnnouncements(allAnnouncements);
        } catch (error) {
            console.error('Error fetching announcements:', error);
            container.innerHTML = `<p class="text-danger">Failed to load announcements.</p>`;
        }
    }

    function renderAnnouncements(list) {
        container.innerHTML = '';

        if (!list.length) {
            container.innerHTML = '<p>No announcements found.</p>';
            return;
        }

        list.forEach(announcement => {
            const cardDiv = document.createElement('div');
            cardDiv.className = 'card announcement-card mb-3';
            cardDiv.innerHTML = `
                <div class="card-body">
                    <h5 class="card-title">${announcement.title}</h5>
                    <p class="announcement-time">${announcement.post_time}</p>
                    <p class="card-text">${announcement.content}</p>
                    <div class="announcement-actions">
                        <button class="edit-btn" title="Edit">
                            <i class="bi bi-pencil-square"></i>
                        </button>
                        <button class="delete-btn" title="Delete">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            `;

            // Edit button redirects
            cardDiv.querySelector('.edit-btn').addEventListener('click', () => {
                window.location.href = `editAnnouncements.php?id=${announcement.id}`;
            });

            // Delete button calls API
            cardDiv.querySelector('.delete-btn').addEventListener('click', () => {
                if (confirm("Are you sure you want to delete this announcement?")) {
                    deleteAnnouncement(announcement.id);
                }
            });

            container.appendChild(cardDiv);
        });
    }

    // Live search
    searchInput.addEventListener('input', (e) => {
        const query = e.target.value.toLowerCase();
        const filtered = allAnnouncements.filter(a =>
            a.title.toLowerCase().includes(query) ||
            a.content.toLowerCase().includes(query)
        );
        renderAnnouncements(filtered);
    });

    // Create button redirects
    createBtn.addEventListener('click', () => {
        window.location.href = 'createAnnouncements.php';
    });

    // Delete announcement via API - Fixed payload structure
    async function deleteAnnouncement(id) {
        try {
            const res = await fetch(API_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 
                    type: 'admin', 
                    fetch: 'announcement', 
                    id: id, 
                    action: 'delete' 
                })
            });
            const result = await res.json();
            if (!result.error) {
                alert(result.message || 'Announcement deleted successfully!');
                getAnnouncements(); // Refresh the list
            } else {
                alert('Error: ' + result.message);
            }
        } catch (err) { 
            console.error(err);
            alert('Error deleting announcement');
        }
    }

    // Initial fetch and refresh every 30s
    getAnnouncements();
    setInterval(getAnnouncements, 30000);
});
</script>

</body>
</html>