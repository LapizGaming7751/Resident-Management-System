<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../ico/house-icon.ico">
    <title>Send Notification</title>
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
    <?php $current_page = 'notifications'; include('sidebar.php'); ?>

    <div class="container mt-3 d-flex justify-content-center align-items-start">
        <div class="card p-4 flex-grow-1">
            <h2>Send Notification</h2>
            
            <div id="error-message" class="alert alert-danger" style="display: none;"></div>
            <div id="success-message" class="alert alert-success" style="display: none;"></div>
            
            <form id="sendNotificationForm">
                <div class="mb-3">
                    <label for="notificationType" class="form-label">Notification Type</label>
                    <select id="notificationType" class="form-select" onchange="toggleResidentSelection()">
                        <option value="specific">Send to Specific Resident</option>
                        <option value="all">Send to All Residents</option>
                    </select>
                </div>
                
                <div id="residentSelection" class="mb-3">
                    <label for="residentSelect" class="form-label">Select Resident</label>
                    <select id="residentSelect" class="form-select">
                        <option value="">Loading residents...</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="message" class="form-label">Notification Message</label>
                    <textarea id="message" class="form-control" rows="4" placeholder="Enter your notification message..." required></textarea>
                </div>
                
                <div class="d-flex gap-2">
                    <button type="submit" id="submit-btn" class="btn btn-primary">
                        <span id="submit-text">Send Notification</span>
                        <span id="submit-loading" class="spinner-border spinner-border-sm ms-2" style="display: none;"></span>
                    </button>
                    <a href="manage.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const API_URL = 'https://siewyaoying.synergy-college.org/ResidentManagementSystem/api.php';

let residentsData = [];

// Load residents on page load
async function loadResidents() {
    try {
        const response = await fetch(`${API_URL}?type=admin&fetch=resident`);
        const data = await response.json();
        residentsData = data;
        
        const select = document.getElementById('residentSelect');
        select.innerHTML = '<option value="">Select a resident...</option>';
        
        data.forEach(resident => {
            const option = document.createElement('option');
            option.value = resident.id;
            option.textContent = `${resident.user} (Room: ${resident.room_code})`;
            select.appendChild(option);
        });
    } catch (error) {
        console.error('Error loading residents:', error);
        showError('Failed to load residents');
    }
}

function toggleResidentSelection() {
    const type = document.getElementById('notificationType').value;
    const residentSelection = document.getElementById('residentSelection');
    
    if (type === 'all') {
        residentSelection.style.display = 'none';
    } else {
        residentSelection.style.display = 'block';
    }
}

function showError(message) {
    const errorDiv = document.getElementById('error-message');
    errorDiv.textContent = message;
    errorDiv.style.display = 'block';
    
    const successDiv = document.getElementById('success-message');
    successDiv.style.display = 'none';
}

function showSuccess(message) {
    const successDiv = document.getElementById('success-message');
    successDiv.textContent = message;
    successDiv.style.display = 'block';
    
    const errorDiv = document.getElementById('error-message');
    errorDiv.style.display = 'none';
}

function setLoading(loading) {
    const submitBtn = document.getElementById('submit-btn');
    const submitText = document.getElementById('submit-text');
    const submitLoading = document.getElementById('submit-loading');
    
    if (loading) {
        submitBtn.disabled = true;
        submitText.style.display = 'none';
        submitLoading.style.display = 'inline-block';
    } else {
        submitBtn.disabled = false;
        submitText.style.display = 'inline';
        submitLoading.style.display = 'none';
    }
}

document.getElementById('sendNotificationForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const type = document.getElementById('notificationType').value;
    const message = document.getElementById('message').value.trim();
    const residentId = document.getElementById('residentSelect').value;
    
    // Validation
    if (!message) {
        showError('Please enter a notification message');
        return;
    }
    
    if (type === 'specific' && !residentId) {
        showError('Please select a resident');
        return;
    }
    
    setLoading(true);
    
    try {
        const payload = {
            type: 'send_notification',
            message: message,
            send_to_all: type === 'all'
        };
        
        if (type === 'specific') {
            payload.resident_id = residentId;
        }
        
        const response = await fetch(API_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        });
        
        const data = await response.json();
        
        if (data.error) {
            showError(data.message);
        } else {
            showSuccess(data.message);
            document.getElementById('sendNotificationForm').reset();
            document.getElementById('notificationType').value = 'specific';
            toggleResidentSelection();
        }
    } catch (error) {
        console.error('Error sending notification:', error);
        showError('Failed to send notification. Please try again.');
    } finally {
        setLoading(false);
    }
});

// Initialize page
loadResidents();
</script>

<!-- Bootstrap JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Mobile JavaScript -->
<script src="../js/mobile.js"></script>
</body>
</html>
