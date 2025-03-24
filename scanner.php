<?php
session_start();

if (isset($_SESSION['type']) && $_SESSION['type']=="security"){
    

?>


<html>
    <head>
        <title>Scan Here</title>
        <link rel="stylesheet" href="css.css">
    </head>
    <body>
        <h1>Visitor Entry</h1>

        <form id="scannerForm">
            <video id="qr-video" autoplay style="width: 100%;"></video>
            <input type="text" name="token" id="token" placeholder="QR Code" autocomplete="off">
            <button type="submit">Enter</button>
        </form>

        <button onclick="window.location.href='logs.php'">View Logs</button>
        <button onclick="window.location.href='logout.php'">Logout</button>

        <script type="module">
            import QrScanner from './node_modules/qr-scanner/qr-scanner.min.js';

            const video = document.getElementById('qr-video');
            const tokenInput = document.getElementById('token');
            const scanner = new QrScanner(video, result => {
                tokenInput.value = result.data;
                scanner.stop(); // Stop scanning after a successful scan
                const API_URL = 'http://localhost/Finals_CheckInSystem/api.php';
                
                scanCode(tokenInput.value);
            },{
                highlightScanRegion: true,
                highlightCodeOutline: true
            });

            scanner.start();

            const API_URL = 'http://localhost/Finals_CheckInSystem/api.php';

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