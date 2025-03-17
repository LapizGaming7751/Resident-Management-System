<?php session_start(); ?>

<html>
    <head>
        <title>Admin Management</title>
    </head>
    <body>
        <h1>Welcome, <?=$_SESSION['user']?></h1>

        <h2>Manage Logs</h2>
        <div id="logs">

        </div>

        <h2>Manage Residents</h2>
        <div id="residents">

        </div>
        <button onclick="window.location.href='registerResident.php'">Register a Resident</button>

        <?php if($_SESSION['access_level']>= 2){ ?>

        <h2>Manage Admins</h2>
        <div id="admins">

        </div>
        <button onclick="window.location.href='registerAdmin.php'">Register an Admin</button>

        <?php } ?>

        <button onclick="window.location.href='logout.php';">Logout</button>
    </body>

    <script>
        const API_URL = 'http://localhost/Finals_CheckInSystem/api.php';

        function getResidents(){
            fetch(`${API_URL}?type=admin&fetch=resident`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                const container1 = document.getElementById('residents');
                container1.innerHTML = '';
                data.forEach(resident => {
                    const div = document.createElement('div');
                    div.innerHTML = `
                        <p>ID: ${resident.id} | Resident Name: ${resident.user} | Room Code: ${resident.room_code}
                        <button onclick="window.location.href='editResident.php?id=${resident.id}&name=${resident.user}&room=${resident.room_code}'">Edit Resident</button>
                        <button onclick="deleteResident(${resident.id})">Delete</button></p>
                        `;
                    container1.appendChild(div);
                });
            })
            .catch(error => {
                console.error('Error fetching Residents:', error);
            });
        }

        function getLogs(){
            fetch(`${API_URL}?type=admin&fetch=log`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                const container3 = document.getElementById('logs');
                container3.innerHTML = '';
                data.forEach(log => {
                    const div = document.createElement('div');
                    div.innerHTML = `
                        <p>ID: ${log.id} | Token: ${log.token} | Scanned at: ${log.scan_time}</p>
                        `;
                    container3.appendChild(div);
                });
            })
            .catch(error => {
                console.error('Error fetching Residents:', error);
            });
        }

        function getAdmins(){
            fetch(`${API_URL}?type=admin&fetch=admin`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                const container2 = document.getElementById('admins');
                container2.innerHTML = '';
                data.forEach(admin => {
                    const div = document.createElement('div');
                    div.innerHTML = `
                        <p>ID: ${admin.id} | Resident Name: ${admin.user} | Access Level: ${admin.access_level}
                        <button onclick="window.location.href='editAdmin.php?id=${admin.id}&name=${admin.user}&level=${admin.access_level}'">Edit Admin</button>
                        <button onclick="deleteAdmin(${admin.id})">Delete</button></p>
                        `;
                    container2.appendChild(div);
                });
            })
            .catch(error => {
                console.error('Error fetching Admins:', error);
            });
        }

        function deleteResident(id){
            if(confirm("Sure to revoke this invite?")){

                fetch(API_URL, {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ "type":"resident", id })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Failed to delete resident');
                    }
                    return response.json();
                })
                .then(data => {
                    alert(data.message);
                    getQR();
                })
                .catch(error => {
                    console.error('Error deleting resident:', error);
                });
            }
        }

        function deleteResident(id){
            if(confirm("Sure to delete this REsident?")){

                fetch(API_URL, {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ "type":"admin","fetch":"resident",id })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Failed to delete QR');
                    }
                    return response.json();
                })
                .then(data => {
                    alert(data.message);
                    getQR();
                })
                .catch(error => {
                    console.error('Error deleting QR:', error);
                });
            }
        }

        function deleteAdmin(id){
            if(confirm("Sure to delete this Admin?")){

                fetch(API_URL, {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ "type":"admin","fetch":"admin", id })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Failed to delete QR');
                    }
                    return response.json();
                })
                .then(data => {
                    alert(data.message);
                    getQR();
                })
                .catch(error => {
                    console.error('Error deleting QR:', error);
                });
            }
        }

        getResidents();
        getLogs();
        <?php if($_SESSION['access_level'] >= 2){ ?>
        getAdmins();
        <?php } ?>
    </script>
</html>