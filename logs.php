<html>
    <head>
        <title>View Logs</title>
        <link rel="stylesheet" href="css.css">
        
    </head>
    <body>
        <h1>View Logs</h1>

        <button onclick="window.location.href = 'scanner.php'">Return to scanner</button>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Token</th>
                    <th>Intended Visitor</th>
                    <th>Visitor Car Plate</th>
                    <th>Scan Time</th>
                    <th>Scan Type</th>
                    <th>Responsible Scanner</th>
                </tr>
            </thead>
            <tbody id="logEntry">

            </tbody>
        </table>

        <script>
            // Use relative URL to avoid hardcoded URLs
            const API_URL = '../api.php';

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
                        
                        // Create cells safely to prevent XSS
                        const cells = [
                            log.id || '',
                            log.token || '',
                            log.intended_visitor || '',
                            log.plate || '',
                            log.scan_time || '',
                            log.scan_type || '',
                            log.scanner_username || ''
                        ];
                        
                        cells.forEach(cellData => {
                            const cell = document.createElement('td');
                            cell.textContent = cellData; // This automatically escapes HTML
                            entry.appendChild(cell);
                        });
                        
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