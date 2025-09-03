<html>
    <head>
        <title>View Logs</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body {
                background: linear-gradient(135deg, #e0f7ff 0%, #e0cfff 100%);
            }
            .card {
                box-shadow: 0 4px 16px rgba(0,0,0,0.08);
                border-radius: 1rem;
            }
        </style>
        
    </head>
    <body>
        <div class="container min-vh-100 d-flex justify-content-center align-items-center">
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