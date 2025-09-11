<?php session_start(); ?>
<html>
<head>
    <title>Admin Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css.css">
</head>
<body>
<?php include('../topbar.php'); ?>

<div class="main-content" style="margin-left: 250px; min-height: calc(100vh - 70px); padding-top: 20px;">
    <?php $current_page = 'logs'; include('sidebar.php'); ?>
    <div class="row g-4">
        <!-- Logs Panel -->
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Visitor Logs</h5>
                </div>
                <div class="card-body" id="logs" style="max-height:400px; overflow-y:auto;">
                    <p class="text-muted">Loading logs...</p>
                </div>
            </div>
        </div>

        <!-- Residents Panel -->
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Residents</h5>
                    <button class="btn btn-sm btn-primary" onclick="window.location.href='registerResident.php'">
                        + Register Resident
                    </button>
                </div>
                <div class="card-body" id="residents" style="max-height:400px; overflow-y:auto;">
                    <p class="text-muted">Loading residents...</p>
                </div>
            </div>
        </div>

        <!-- Security Panel -->
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Security</h5>
                    <button class="btn btn-sm btn-primary" onclick="window.location.href='registerSecurity.php'">
                        + Register Security
                    </button>
                </div>
                <div class="card-body" id="security" style="max-height:400px; overflow-y:auto;">
                    <p class="text-muted">Loading security staff...</p>
                </div>
            </div>
        </div>


        <!-- Admins Panel (only if access level ≥ 2) -->
        <?php if ($_SESSION['access_level'] >= 2): ?>
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Admins</h5>
                    <button class="btn btn-sm btn-primary" onclick="window.location.href='registerAdmin.php'">
                        + Register Admin
                    </button>
                </div>
                <div class="card-body" id="admins" style="max-height:400px; overflow-y:auto;">
                    <p class="text-muted">Loading admins...</p>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
const API_URL = 'http://localhost/Finals_CheckInSystem%20ai/api.php';

function getResidents(){
    fetch(`${API_URL}?type=admin&fetch=resident`)
        .then(r=>r.json())
        .then(data=>{
            const container = document.getElementById('residents');
            container.innerHTML = '';
            if (!data.length) {
                container.innerHTML = '<p class="text-muted">No residents found.</p>';
                return;
            }
            data.forEach(resident=>{
                const div = document.createElement('div');
                div.className = "d-flex justify-content-between align-items-center border-bottom py-2";
                div.innerHTML = `
                    <span>ID: ${resident.id} | ${resident.user} (Room: ${resident.room_code})</span>
                    <span>
                        <button class="btn btn-sm btn-outline-secondary" onclick="window.location.href='editResident.php?id=${resident.id}&name=${resident.user}&room=${resident.room_code}'">Edit</button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteResident(${resident.id})">Delete</button>
                    </span>`;
                container.appendChild(div);
            });
        });
}

function getLogs(){
    fetch(`${API_URL}?type=admin&fetch=log`)
        .then(r=>r.json())
        .then(data=>{
            const container = document.getElementById('logs');
            container.innerHTML = '';
            if (!data.length) {
                container.innerHTML = '<p class="text-muted">No logs yet.</p>';
                return;
            }
            data.forEach(log=>{
                const div = document.createElement('div');
                div.className = "border-bottom py-2";
                div.innerHTML = `
                    <strong>#${log.id}</strong> – Token: ${log.token} <br>
                    <small>Scanned at: ${log.scan_time}</small>`;
                container.appendChild(div);
            });
        });
}

function getAdmins(){
    fetch(`${API_URL}?type=admin&fetch=admin`)
        .then(r=>r.json())
        .then(data=>{
            const container = document.getElementById('admins');
            container.innerHTML = '';
            if (!data.length) {
                container.innerHTML = '<p class="text-muted">No admins found.</p>';
                return;
            }
            data.forEach(admin=>{
                const div = document.createElement('div');
                div.className = "d-flex justify-content-between align-items-center border-bottom py-2";
                div.innerHTML = `
                    <span>ID: ${admin.id} | ${admin.user} (Level: ${admin.access_level})</span>
                    <span>
                        <button class="btn btn-sm btn-outline-secondary" onclick="window.location.href='editAdmin.php?id=${admin.id}&name=${admin.user}&level=${admin.access_level}'">Edit</button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteAdmin(${admin.id})">Delete</button>
                    </span>`;
                container.appendChild(div);
            });
        });
}

function getSecurity(){
    fetch(`${API_URL}?type=admin&fetch=security`)
        .then(r => {
            if (!r.ok) throw new Error(`HTTP error! status: ${r.status}`);
            return r.json();
        })
        .then(data => {
            const container = document.getElementById('security');
            container.innerHTML = '';
            if (!data.length) {
                container.innerHTML = '<p class="text-muted">No security staff found.</p>';
                return;
            }
            data.forEach(sec => {
                const div = document.createElement('div');
                div.className = "d-flex justify-content-between align-items-center border-bottom py-2";
                div.innerHTML = `
                    <span>ID: ${sec.id} | ${sec.user}</span>
                    <span>
                        <button class="btn btn-sm btn-outline-secondary" 
                                onclick="window.location.href='editSecurity.php?id=${sec.id}&name=${sec.user}'">
                            Edit
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteSecurity(${sec.id})">
                            Delete
                        </button>
                    </span>`;
                container.appendChild(div);
            });
        })
        .catch(err => console.error("Error fetching security staff:", err));
}

function deleteResident(id){
    if(confirm("Delete this resident?")){
        fetch(API_URL, {
            method: 'DELETE',
            headers: { 'Content-Type':'application/json' },
            body: JSON.stringify({ type:'admin', fetch:'resident', id })
        })
        .then(r=>r.json())
        .then(data=>{
            alert(data.message);
            getResidents();
        });
    }
}

function deleteAdmin(id){
    if(confirm("Delete this admin?")){
        fetch(API_URL, {
            method: 'DELETE',
            headers: { 'Content-Type':'application/json' },
            body: JSON.stringify({ type:'admin', fetch:'admin', id })
        })
        .then(r=>r.json())
        .then(data=>{
            alert(data.message);
            getAdmins();
        });
    }
}

function deleteSecurity(id){
    if(confirm("Delete this security staff?")){
        fetch(API_URL, {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ type: 'admin', fetch: 'security', id })
        })
        .then(r => {
            if (!r.ok) throw new Error('Failed to delete security staff');
            return r.json();
        })
        .then(data => {
            alert(data.message);
            getSecurity();
        })
        .catch(err => console.error('Error deleting security staff:', err));
    }
}

getSecurity();
getResidents();
getLogs();
<?php if ($_SESSION['access_level'] >= 2): ?> getAdmins(); <?php endif; ?>
</script>
</body>
</html>
