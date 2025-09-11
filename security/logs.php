
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
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css.css">
    </head>
    <body>
        <?php include('../topbar.php'); ?>
        <div class="main-content" style="margin-left: 250px; min-height: calc(100vh - 70px); padding-top: 20px;">
            <!-- Sidebar -->
            <?php $current_page = 'logs'; include 'sidebar.php'; ?>
            <!-- Main Card -->
            <div class="container d-flex justify-content-center align-items-center" style="min-height: calc(100vh - 90px);">
                <div class="card p-4" style="max-width: 900px; width: 100%;">
                    <h1 class="mb-4 text-center">View Logs</h1>
                    <div class="mb-3 d-flex align-items-center" style="gap:0;">
                        <select id="logField" class="form-select w-auto" style="border-top-right-radius:0; border-bottom-right-radius:0; height:38px; padding-top:6px; padding-bottom:6px;">
                            <option value="all">All Fields</option>
                            <option value="id">ID</option>
                            <option value="token">Token</option>
                            <option value="intended_visitor">Intended Visitor</option>
                            <option value="scan_time">Scan Time</option>
                            <option value="scan_type">Scan Type</option>
                            <option value="scanner_username">Responsible Scanner</option>
                        </select>
                        <input type="text" id="logSearch" class="form-control" placeholder="Search logs..." style="border-top-left-radius:0; border-bottom-left-radius:0; height:38px;">
                    </div>
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
            let logsData = [];

            function renderLogs(filtered = null) {
                const container = document.getElementById('logEntry');
                container.innerHTML = '';
                (filtered || logsData).forEach(log => {
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
            }

            function getLogs() {
                fetch(`${API_URL}?type=admin&fetch=log`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        logsData = data;
                        renderLogs();
                    })
                    .catch(error => {
                        console.error('Error fetching logs:', error);
                    });
            }

            document.addEventListener('DOMContentLoaded', () => {
                getLogs();
                const searchInput = document.getElementById('logSearch');
                const fieldSelect = document.getElementById('logField');

                function filterLogs() {
                    const query = searchInput.value.toLowerCase();
                    const field = fieldSelect.value;
                    let filtered;
                    if (!query) {
                        filtered = logsData;
                    } else if (field === 'all') {
                        filtered = logsData.filter(log =>
                            Object.values(log).some(val => String(val).toLowerCase().includes(query))
                        );
                    } else {
                        filtered = logsData.filter(log =>
                            String(log[field]).toLowerCase().includes(query)
                        );
                    }
                    renderLogs(filtered);
                }

                searchInput.addEventListener('input', filterLogs);
                fieldSelect.addEventListener('change', filterLogs);
            });
        </script>
    </body>
</html>