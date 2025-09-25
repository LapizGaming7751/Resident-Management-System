<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Session - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Admin Session Test</h2>
        
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
                <h5>Session Info</h5>
            </div>
            <div class="card-body">
                <p><strong>Session ID:</strong> <?= session_id(); ?></p>
                <p><strong>Session Status:</strong> <?= session_status(); ?></p>
                <p><strong>Session Name:</strong> <?= session_name(); ?></p>
                <p><strong>Cookie Params:</strong></p>
                <pre><?php print_r(session_get_cookie_params()); ?></pre>
            </div>
        </div>
        
        <div class="card mb-3">
            <div class="card-header">
                <h5>Test API Calls</h5>
            </div>
            <div class="card-body">
                <button class="btn btn-primary me-2" onclick="testGET()">Test GET (fetch residents)</button>
                <button class="btn btn-warning me-2" onclick="testPOST()">Test POST (create invite)</button>
                <div id="results" class="mt-3"></div>
            </div>
        </div>
    </div>

    <script>
        const API_URL = '../api.php';
        
        function testGET() {
            document.getElementById('results').innerHTML = '<div class="alert alert-info">Testing GET request...</div>';
            
            fetch(`${API_URL}?type=admin&fetch=resident`)
                .then(response => {
                    console.log('GET Response status:', response.status);
                    return response.json();
                })
                .then(data => {
                    document.getElementById('results').innerHTML = 
                        '<div class="alert alert-success"><h6>GET Success:</h6><pre>' + JSON.stringify(data, null, 2) + '</pre></div>';
                })
                .catch(error => {
                    console.error('GET Error:', error);
                    document.getElementById('results').innerHTML = 
                        '<div class="alert alert-danger">GET Error: ' + error.message + '</div>';
                });
        }
        
        function testPOST() {
            document.getElementById('results').innerHTML = '<div class="alert alert-info">Testing POST request...</div>';
            
            const testData = {
                type: 'create_resident_invite',
                email: 'test@example.com',
                room_code: '12-34-A5',
                expiry_hours: 24
            };
            
            console.log('Sending POST to:', API_URL);
            console.log('POST data:', testData);
            
            fetch(API_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify(testData)
            })
            .then(response => {
                console.log('POST Response status:', response.status);
                return response.json();
            })
            .then(data => {
                document.getElementById('results').innerHTML = 
                    '<div class="alert alert-success"><h6>POST Success:</h6><pre>' + JSON.stringify(data, null, 2) + '</pre></div>';
            })
            .catch(error => {
                console.error('POST Error:', error);
                document.getElementById('results').innerHTML = 
                    '<div class="alert alert-danger">POST Error: ' + error.message + '</div>';
            });
        }
    </script>
</body>
</html>
