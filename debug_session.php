<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Debug Session</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Session Debug Information</h2>
        
        <div class="card mb-3">
            <div class="card-header">
                <h5>Current Session Data</h5>
            </div>
            <div class="card-body">
                <pre><?php print_r($_SESSION); ?></pre>
            </div>
        </div>
        
        <div class="card mb-3">
            <div class="card-header">
                <h5>Session Status</h5>
            </div>
            <div class="card-body">
                <p><strong>Session ID:</strong> <?= session_id(); ?></p>
                <p><strong>Session Status:</strong> <?= session_status(); ?></p>
                <p><strong>Session Name:</strong> <?= session_name(); ?></p>
            </div>
        </div>
        
        <div class="card mb-3">
            <div class="card-header">
                <h5>Test API Call</h5>
            </div>
            <div class="card-body">
                <button class="btn btn-primary" onclick="testAPI()">Test Create Resident Invite</button>
                <div id="apiResult" class="mt-3"></div>
            </div>
        </div>
    </div>

    <script>
        function testAPI() {
            const API_URL = 'http://localhost/Finals_CheckInSystem%20ai/api.php';
            
            const testData = {
                type: 'create_resident_invite',
                email: 'test@example.com',
                room_code: '12-34-A5',
                expiry_hours: 24
            };
            
            fetch(API_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(testData)
            })
            .then(response => {
                console.log('Response status:', response.status);
                return response.json();
            })
            .then(data => {
                document.getElementById('apiResult').innerHTML = 
                    '<div class="alert alert-info"><pre>' + JSON.stringify(data, null, 2) + '</pre></div>';
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('apiResult').innerHTML = 
                    '<div class="alert alert-danger">Error: ' + error.message + '</div>';
            });
        }
    </script>
</body>
</html>
