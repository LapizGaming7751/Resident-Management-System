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

    <!-- Row for Residents, Security, Admins -->
    <div class="row g-4 px-4"> <!-- px-4 matches inner spacing with logs -->
    <!-- Residents -->
    <div class="col-lg-4">
        <div class="card shadow-sm h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Residents</h5>
                <button class="btn btn-sm btn-primary flex-shrink-0" style="min-width:150px;"
                        onclick="window.location.href='registerResident.php'">
                    + Register Resident
                </button>
            </div>
            <div class="p-2">
                <input type="text" id="residentSearch" class="form-control" placeholder="Search residents...">
            </div>
            <div class="card-body" id="residents" style="max-height:400px; overflow-y:auto;">
                <p class="text-muted">Loading residents...</p>
            </div>
        </div>
    </div>

    <!-- Security -->
    <div class="col-lg-4">
        <div class="card shadow-sm h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Security</h5>
                <button class="btn btn-sm btn-primary flex-shrink-0" style="min-width:150px;"
                        onclick="window.location.href='registerSecurity.php'">
                    + Register Security
                </button>
            </div>
            <div class="p-2">
                <input type="text" id="securitySearch" class="form-control" placeholder="Search security...">
            </div>
            <div class="card-body" id="security" style="max-height:400px; overflow-y:auto;">
                <p class="text-muted">Loading security staff...</p>
            </div>
        </div>
    </div>

    <!-- Admins -->
    <?php if ($_SESSION['access_level'] >= 2): ?>
    <div class="col-lg-4">
        <div class="card shadow-sm h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Admins</h5>
                <button class="btn btn-sm btn-primary flex-shrink-0" style="min-width:150px;"
                        onclick="window.location.href='registerAdmin.php'">
                    + Register Admin
                </button>
            </div>
            <div class="p-2">
                <input type="text" id="adminSearch" class="form-control" placeholder="Search admins...">
            </div>
            <div class="card-body" id="admins" style="max-height:400px; overflow-y:auto;">
                <p class="text-muted">Loading admins...</p>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>


    <!-- Logs Panel (full width, underneath) -->
    <div class="container mt-4">
        <div class="card p-4">
            <h1 class="mb-4 text-center">Visitor Logs</h1>
            <div class="mb-3 d-flex align-items-center" style="gap:0;">
                <select id="logField" class="form-select w-auto" 
                        style="border-top-right-radius:0; border-bottom-right-radius:0; height:38px; padding-top:6px; padding-bottom:6px;">
                    <option value="all">All Fields</option>
                    <option value="id">ID</option>
                    <option value="token">Token</option>
                    <option value="intended_visitor">Intended Visitor</option>
                    <option value="scan_time">Scan Time</option>
                    <option value="scan_type">Scan Type</option>
                    <option value="scanner_username">Responsible Scanner</option>
                </select>
                <input type="text" id="logSearch" class="form-control" placeholder="Search logs..." 
                       style="border-top-left-radius:0; border-bottom-left-radius:0; height:38px;">
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
                    <tbody id="logEntry"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
const API_URL = 'http://localhost/Finals_CheckInSystem%20ai/api.php';

// ---------- Residents ----------
let residentsData = [];
function renderResidents(filtered = null) {
    const container = document.getElementById('residents');
    container.innerHTML = '';
    (filtered || residentsData).forEach(resident => {
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
}
function getResidents(){
    fetch(`${API_URL}?type=admin&fetch=resident`)
        .then(r=>r.json())
        .then(data=>{ residentsData = data; renderResidents(); });
}
document.getElementById('residentSearch').addEventListener('input', e=>{
    const q = e.target.value.toLowerCase();
    renderResidents(residentsData.filter(r => Object.values(r).some(v=>String(v).toLowerCase().includes(q))));
});

// ---------- Security ----------
let securityData = [];
function renderSecurity(filtered = null) {
    const container = document.getElementById('security');
    container.innerHTML = '';
    (filtered || securityData).forEach(sec => {
        const div = document.createElement('div');
        div.className = "d-flex justify-content-between align-items-center border-bottom py-2";
        div.innerHTML = `
            <span>ID: ${sec.id} | ${sec.user}</span>
            <span>
                <button class="btn btn-sm btn-outline-secondary" onclick="window.location.href='editSecurity.php?id=${sec.id}&name=${sec.user}'">Edit</button>
                <button class="btn btn-sm btn-outline-danger" onclick="deleteSecurity(${sec.id})">Delete</button>
            </span>`;
        container.appendChild(div);
    });
}
function getSecurity(){
    fetch(`${API_URL}?type=admin&fetch=security`)
        .then(r=>r.json())
        .then(data=>{ securityData = data; renderSecurity(); });
}
document.getElementById('securitySearch').addEventListener('input', e=>{
    const q = e.target.value.toLowerCase();
    renderSecurity(securityData.filter(s => Object.values(s).some(v=>String(v).toLowerCase().includes(q))));
});

// ---------- Admins ----------
let adminsData = [];
function renderAdmins(filtered = null) {
    const container = document.getElementById('admins');
    container.innerHTML = '';
    const currentAdminId = <?= json_encode($_SESSION['id']) ?>;
    (filtered || adminsData).forEach(admin => {
        const actions = (admin.id != currentAdminId)
            ? `<button class="btn btn-sm btn-outline-secondary" onclick="window.location.href='editAdmin.php?id=${admin.id}&name=${admin.user}&level=${admin.access_level}'">Edit</button>
               <button class="btn btn-sm btn-outline-danger" onclick="deleteAdmin(${admin.id})">Delete</button>`
            : `<span class="text-muted">You</span>`;
        const div = document.createElement('div');
        div.className = "d-flex justify-content-between align-items-center border-bottom py-2";
        div.innerHTML = `<span>ID: ${admin.id} | ${admin.user} (Level: ${admin.access_level})</span><span>${actions}</span>`;
        container.appendChild(div);
    });
}
function getAdmins(){
    fetch(`${API_URL}?type=admin&fetch=admin`)
        .then(r=>r.json())
        .then(data=>{ adminsData = data; renderAdmins(); });
}
<?php if ($_SESSION['access_level'] >= 2): ?>
document.getElementById('adminSearch').addEventListener('input', e=>{
    const q = e.target.value.toLowerCase();
    renderAdmins(adminsData.filter(a => Object.values(a).some(v=>String(v).toLowerCase().includes(q))));
});
<?php endif; ?>

// ---------- Logs ----------
let logsData = [];
function renderLogs(filtered = null) {
    const container = document.getElementById('logEntry');
    container.innerHTML = '';
    (filtered || logsData).forEach(log => {
        const entry = document.createElement('tr');
        entry.innerHTML = `
            <td>${log.id}</td>
            <td>${log.token}</td>
            <td>${log.intended_visitor}</td>
            <td>${log.scan_time}</td>
            <td>${log.scan_type}</td>
            <td>${log.scanner_username}</td>`;
        container.appendChild(entry);
    });
}
function getLogs(){
    fetch(`${API_URL}?type=admin&fetch=log`)
        .then(r=>r.json())
        .then(data=>{ logsData = data; renderLogs(); });
}
document.getElementById('logSearch').addEventListener('input', filterLogs);
document.getElementById('logField').addEventListener('change', filterLogs);
function filterLogs(){
    const query = document.getElementById('logSearch').value.toLowerCase();
    const field = document.getElementById('logField').value;
    let filtered;
    if (!query) filtered = logsData;
    else if (field === 'all') filtered = logsData.filter(log =>
        Object.values(log).some(v=>String(v).toLowerCase().includes(query)));
    else filtered = logsData.filter(log => String(log[field]).toLowerCase().includes(query));
    renderLogs(filtered);
}

// ---------- Delete Functions ----------
function deleteResident(id){ if(confirm("Delete this resident?")){ fetch(API_URL,{method:'DELETE',headers:{'Content-Type':'application/json'},body:JSON.stringify({type:'admin',fetch:'resident',id})}).then(r=>r.json()).then(d=>{alert(d.message);getResidents();});}}
function deleteSecurity(id){ if(confirm("Delete this security staff?")){ fetch(API_URL,{method:'DELETE',headers:{'Content-Type':'application/json'},body:JSON.stringify({type:'admin',fetch:'security',id})}).then(r=>r.json()).then(d=>{alert(d.message);getSecurity();});}}
function deleteAdmin(id){ if(confirm("Delete this admin?")){ fetch(API_URL,{method:'DELETE',headers:{'Content-Type':'application/json'},body:JSON.stringify({type:'admin',fetch:'admin',id})}).then(r=>r.json()).then(d=>{alert(d.message);getAdmins();});}}

// ---------- Init ----------
getResidents();
getSecurity();
getLogs();
<?php if ($_SESSION['access_level'] >= 2): ?>getAdmins();<?php endif; ?>
</script>
</body>
</html>
