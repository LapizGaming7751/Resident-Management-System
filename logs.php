
<?php
session_start();
if (!isset($_SESSION['type']) || $_SESSION['type'] !== 'security') {
    header('Location: login.php');
    exit;
}
?>
<html>
    <head>
        <title>View Logs</title>
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
                    <button onclick="window.location.href='scanner.php';" class="btn btn-outline-primary w-100 mb-2">Scanner</button>
                    <button class="btn btn-primary w-100 mb-2" disabled>Manage Logs</button>
                    <button onclick="window.location.href='chat_security.php';" class="btn btn-outline-primary w-100 mb-2">Security Chat</button>
                </div>
                <button onclick="window.location.href='logout.php';" class="btn btn-danger w-100 mt-2">Logout</button>
            </div>
            <!-- Main Card -->
            <div class="container d-flex justify-content-center align-items-center flex-grow-1">
                <div class="card p-4" style="max-width: 900px; width: 100%;">
                    <h1 class="mb-4 text-center">View Logs</h1>
                    <button onclick="window.location.href = 'scanner.php'" class="btn btn-outline-primary w-100 mb-3">Return to scanner</button>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Token</th>
                                    <th>Intended Visitor</th>
                                    <th>Scan Time</th>
                                    <th>Scan Type</th>
                                    <th>Responsible Scanner</th>
                                </tr>
                            </thead>
                            <tbody id="logEntry">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <script>
            const API_URL = 'http://localhost/Finals_CheckInSystem%20ai/api.php';

            function getLogs(){
                fetch(`${API_URL}?type=admin&fetch=log`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    const container = document.getElementById('logEntry');
                    container.innerHTML = '';
                    data.forEach(log => {
                        const entry = document.createElement('tr');
                        entry.innerHTML = `
                            <td> ${log.id} </td>
                            <td> ${log.token} </td>
                            <td> ${log.intended_visitor} </td>
                            <td> ${log.scan_time} </td>
                            <td> ${log.scan_type} </td>
                            <td> ${log.scanner_username} </td>
                            `;
                        container.appendChild(entry);
                });
            })
                .catch(error => {
                    console.error('Error fetching logs:', error);
            });
        }

        getLogs();
        </script>
    </body>
</html>