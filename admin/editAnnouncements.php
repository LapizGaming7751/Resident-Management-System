<?php 
session_start();
$id = $_GET['id'] ?? null; // ID of the announcement to edit

if (!$id) {
    header('Location: announcements.php');
    exit;
}
?>

<html>
<head>
    <link rel="icon" type="image/x-icon" href="../ico/house-icon.ico">
    <title>Edit Announcement</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css.css">
    <style>
        textarea, input[type="text"] {
            width: 100%; /* full width */
        }
        textarea {
            resize: vertical;
            min-height: 200px; /* more vertical space */
        }
        .card {
            width: 100%; /* extend card horizontally */
            max-width: 900px;
        }
        .loading {
            text-align: center;
            padding: 20px;
            color: #6c757d;
        }
    </style>
</head>
<body>
<?php include('../topbar.php'); ?>
<div class="container mt-3 d-flex justify-content-center align-items-start">
    <div class="card p-4 flex-grow-1">
        <?php include 'sidebar.php'; ?>
        <h2>Edit Announcement</h2>
        
        <div id="loading" class="loading">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p>Loading announcement...</p>
        </div>
        
        <form id="editAnnouncementForm" style="width: 100%; display: none;">
            <div class="mb-3">
                <input type="text" name="title" id="title" class="form-control" placeholder="Announcement Title" required style="width: 100%;" />
            </div>
            <div class="mb-3">
                <textarea name="content" id="content" class="form-control" placeholder="Write your announcement here..." required style="width: 100%; min-height: 300px;"></textarea>
            </div>
            <button type="submit" class="btn btn-primary w-100 mb-2">Save Changes</button>
            <a href="announcements.php" class="btn btn-secondary w-100">Cancel</a>
        </form>
    </div>
</div>

<script>
const API_URL = 'http://localhost/Finals_CheckInSystem%20ai/api.php';
const announcementId = <?= json_encode($id) ?>;

// Load the announcement's current title and content
async function loadAnnouncement() {
    try {
        // Fetch all announcements from admin endpoint
        const response = await fetch(`${API_URL}?type=admin&fetch=announcements`);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const announcements = await response.json();
        
        // Find the announcement that matches the ID
        const announcement = announcements.find(a => a.id == announcementId);
        
        if (announcement) {
            document.getElementById('title').value = announcement.title || '';
            document.getElementById('content').value = announcement.content || '';
            
            // Hide loading and show form
            document.getElementById('loading').style.display = 'none';
            document.getElementById('editAnnouncementForm').style.display = 'block';
        } else {
            alert('Announcement not found!');
            window.location.href = 'announcements.php';
        }
    } catch (err) {
        console.error('Error loading announcement:', err);
        alert('Error loading announcement data.');
        window.location.href = 'announcements.php';
    }
}

document.getElementById("editAnnouncementForm").addEventListener("submit", async e => {
    e.preventDefault();

    const title = document.getElementById('title').value.trim();
    const content = document.getElementById('content').value.trim();

    // Basic validation
    if (!title) {
        alert('Please enter a title for the announcement');
        return;
    }
    
    if (!content) {
        alert('Please enter content for the announcement');
        return;
    }

    const payload = {
        type: 'edit_announcement', 
        id: announcementId, 
        title: title, 
        content: content
    };

    try {
        const response = await fetch(API_URL, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.error) {
            alert('Error: ' + data.message);
        } else {
            alert(data.message || 'Announcement updated successfully!');
            window.location.href = 'announcements.php';
        }
    } catch (err) {
        console.error('Error saving announcement:', err);
        alert('Error saving announcement. Please try again.');
    }
});

// Load current data on page load
loadAnnouncement();
</script>
</body>
</html>