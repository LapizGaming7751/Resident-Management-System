<?php 
session_start();

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    header('Location: ../login.php');
    exit;
}
?>

<html>
<head>
    <title>Create Announcement</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css.css">
    <style>
        input[type="text"], textarea {
            width: 100%;
        }
        textarea {
            resize: vertical;
            min-height: 300px;
        }
        .card {
            flex-grow: 1;
            max-width: 900px;
            width: 100%;
        }
        .main-container {
            display: flex;
            gap: 20px;
        }
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
    </style>
</head>
<body>

<?php include('../topbar.php'); ?>
<div class="container mt-3 d-flex justify-content-center align-items-start">
    <div class="card p-4 flex-grow-1">
        <?php include 'sidebar.php'; ?>
        <h2>Create Announcement</h2>
        
        <div id="error-message" class="alert alert-danger" style="display: none;"></div>
        <div id="success-message" class="alert alert-success" style="display: none;"></div>
        
        <form id="createAnnouncementForm" style="width: 100%;">
            <div class="mb-3">
                <label for="title" class="form-label">Announcement Title</label>
                <input type="text" name="title" id="title" class="form-control" placeholder="Enter announcement title" required style="width: 100%;" />
            </div>
            <div class="mb-3">
                <label for="content" class="form-label">Announcement Content</label>
                <textarea name="content" id="content" class="form-control" placeholder="Write your announcement here..." required style="width: 100%; min-height: 300px;"></textarea>
            </div>
            <button type="submit" id="submit-btn" class="btn btn-primary w-100 mb-2">
                <span id="submit-text">Create Announcement</span>
                <span id="submit-loading" class="spinner-border spinner-border-sm ms-2" style="display: none;"></span>
            </button>
            <a href="announcements.php" class="btn btn-secondary w-100">Cancel</a>
        </form>
    </div>
</div>

<script>
const API_URL = 'http://localhost/Finals_CheckInSystem%20ai/api.php';

// Debug information
console.log('=== CREATE ANNOUNCEMENT DEBUG ===');
console.log('API URL:', API_URL);
console.log('Session ID:', '<?= $_SESSION["id"] ?? "Not set" ?>');
console.log('User:', '<?= $_SESSION["user"] ?? "Not set" ?>');

function showError(message) {
    const errorDiv = document.getElementById('error-message');
    const successDiv = document.getElementById('success-message');
    errorDiv.textContent = message;
    errorDiv.style.display = 'block';
    successDiv.style.display = 'none';
}

function showSuccess(message) {
    const errorDiv = document.getElementById('error-message');
    const successDiv = document.getElementById('success-message');
    successDiv.textContent = message;
    successDiv.style.display = 'block';
    errorDiv.style.display = 'none';
}

function hideMessages() {
    document.getElementById('error-message').style.display = 'none';
    document.getElementById('success-message').style.display = 'none';
}

function setLoading(loading) {
    const submitBtn = document.getElementById('submit-btn');
    const submitText = document.getElementById('submit-text');
    const submitLoading = document.getElementById('submit-loading');
    
    if (loading) {
        submitBtn.disabled = true;
        submitText.textContent = 'Creating...';
        submitLoading.style.display = 'inline-block';
    } else {
        submitBtn.disabled = false;
        submitText.textContent = 'Create Announcement';
        submitLoading.style.display = 'none';
    }
}

document.getElementById("createAnnouncementForm").addEventListener("submit", async e => {
    e.preventDefault();
    hideMessages();
    setLoading(true);

    const title = document.getElementById('title').value.trim();
    const content = document.getElementById('content').value.trim();

    console.log('=== FORM SUBMISSION ===');
    console.log('Title:', title);
    console.log('Content length:', content.length);

    // Basic validation
    if (!title) {
        showError('Please enter a title for the announcement');
        setLoading(false);
        return;
    }
    
    if (!content) {
        showError('Please enter content for the announcement');
        setLoading(false);
        return;
    }

    if (title.length > 255) {
        showError('Title is too long (maximum 255 characters)');
        setLoading(false);
        return;
    }

    const payload = { 
        type: 'create_announcement', 
        title: title, 
        content: content 
    };

    console.log('=== API REQUEST ===');
    console.log('Payload:', payload);
    console.log('JSON payload:', JSON.stringify(payload));

    try {
        const response = await fetch(API_URL, {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        });

        console.log('=== API RESPONSE ===');
        console.log('Response status:', response.status);
        console.log('Response ok:', response.ok);
        console.log('Response headers:', [...response.headers.entries()]);

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const responseText = await response.text();
        console.log('Raw response:', responseText);
        
        let data;
        try {
            data = JSON.parse(responseText);
        } catch (parseError) {
            console.error('JSON parse error:', parseError);
            throw new Error('Invalid JSON response from server');
        }
        
        console.log('Parsed response:', data);
        
        if (data.error) {
            showError('Error: ' + (data.message || 'Unknown error occurred'));
        } else {
            showSuccess(data.message || 'Announcement created successfully!');
            
            // Clear form
            document.getElementById('title').value = '';
            document.getElementById('content').value = '';
            
            // Redirect after 2 seconds
            setTimeout(() => {
                window.location.href = 'announcements.php';
            }, 2000);
        }
    } catch (err) {
        console.error('=== ERROR ===');
        console.error('Error creating announcement:', err);
        showError('Error creating announcement: ' + err.message);
    } finally {
        setLoading(false);
    }
});

// Test API connection on page load
document.addEventListener('DOMContentLoaded', async () => {
    try {
        console.log('=== TESTING API CONNECTION ===');
        const testResponse = await fetch(API_URL + '?type=admin&fetch=announcements');
        console.log('API test status:', testResponse.status);
        console.log('API test ok:', testResponse.ok);
        
        if (!testResponse.ok) {
            showError('Warning: Cannot connect to API. Please check your server configuration.');
        }
    } catch (err) {
        console.error('API connection test failed:', err);
        showError('Warning: API connection failed. Please check if your server is running.');
    }
});
</script>

</body>
</html>