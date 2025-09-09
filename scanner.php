<?php
session_start();

if (isset($_SESSION['type']) && $_SESSION['type']=="security"){
    

?>


<html>
    <head>
        <title>Scan Here</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="css.css">
    </head>
    <body>

        <div style="display: flex; min-height: 100vh;">
            <!-- Sidebar -->
            <div class="d-flex flex-column bg-white p-3" style="min-width:200px; height:100vh; border-radius:0; box-shadow:0 4px 16px rgba(0,0,0,0.08); justify-content:space-between; position:sticky; top:0; left:0;">
                <div>
                    <h4 class="mb-4 text-center">Welcome,<br><?=$_SESSION['user']?></h4>
                    <hr class="my-3">
                    <button class="btn btn-primary w-100 mb-2" disabled>Scanner</button>
                    <button onclick="window.location.href='logs.php';" class="btn btn-outline-primary w-100 mb-2">Manage Logs</button>
                    <button onclick="window.location.href='chat_security.php';" class="btn btn-outline-primary w-100 mb-2">Security Chat</button>
                </div>
                <button onclick="window.location.href='logout.php';" class="btn btn-danger w-100 mt-2">Logout</button>
            </div>
            <!-- Main Card -->
            <div class="container d-flex justify-content-center align-items-center flex-grow-1">
                <div class="card p-4 d-flex flex-column align-items-center" style="max-width: 700px; width: 100%;">
                    <h1 class="mb-4 text-center">Visitor Entry</h1>
                    <form id="scannerForm">
                        <div class="mb-3 d-flex justify-content-center">
                            <video id="qr-video" autoplay style="width: 600px; height: 350px; max-width: 100%; border-radius: 0.5rem; object-fit: cover;"></video>
                        </div>
                        <div class="mb-3">
                            <input type="text" name="token" id="token" placeholder="QR Code" autocomplete="off" class="form-control">
                        </div>
                        <button type="submit" class="btn btn-primary w-100 mb-2">Enter</button>
                    </form>
                </div>
            </div>
        </div>

        <script type="module">
            import QrScanner from './node_modules/qr-scanner/qr-scanner.min.js';
            
            const API_URL = 'http://localhost/Finals_CheckInSystem%20ai/api.php';
            
            const video = document.getElementById('qr-video');
            const tokenInput = document.getElementById('token');
            const scanner = new QrScanner(video, result => {
                tokenInput.value = result.data;
                scanner.stop(); // Stop scanning after a successful scan
                
                scanCode(tokenInput.value);
            },{
                highlightScanRegion: true,
                highlightCodeOutline: true
            });

            scanner.start();

            function scanCode(token){
                
                const scan_by = <?=$_SESSION['id']?>;
                const type = "guest";

                fetch(API_URL, {
                    method: 'POST',
                    headers: {'Content-type':'application/json'},
                    body: JSON.stringify({ type, token, scan_by })
                })
                .then(response => {
                   if (!response.ok) {
                        throw new Error('Unable to find guest');
                    }
                    return response.json();
                })
                .then(data => {
                    alert(data.message);
                    location.reload();
                })
                .catch(error => {
                    console.error('Error finding guest:', error);
                });
            }

            document.getElementById("scannerForm").addEventListener("submit", e =>{
                e.preventDefault();
                scanCode(document.getElementById('token').value);
            });
        </script>
    </body>
</html>

<?php
}else{
    echo "Unauthorized access";
}
?>