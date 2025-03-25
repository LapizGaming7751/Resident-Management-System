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
            const API_URL = 'https://siewyaoying.synergy-college.org/Finals_CheckInSystem/api.php';

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
                            <td> ${log.plate} </td>
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