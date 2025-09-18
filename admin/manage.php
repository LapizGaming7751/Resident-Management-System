<?php session_start(); ?>
<html>
<head>
    <link rel="icon" type="image/x-icon" href="../ico/house-icon.ico">
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
                    + Create Invite
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
                    + Create Invite
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

<!-- Invite Codes Panel (full width, underneath) -->
<div class="container mt-4">
    <div class="card p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0">Invite Codes</h1>
            <div>
                <button class="btn btn-primary" onclick="window.location.href='registerResident.php'">Create Resident Invite</button>
                <button class="btn btn-primary" onclick="window.location.href='registerSecurity.php'">Create Security Invite</button>
            </div>
        </div>
        
        <!-- Invite Statistics -->
        <div class="row mb-4" id="inviteStats">
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body text-center">
                        <h5 class="card-title">Pending</h5>
                        <h3 id="pendingCount">-</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <h5 class="card-title">Accepted</h5>
                        <h3 id="acceptedCount">-</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger text-white">
                    <div class="card-body text-center">
                        <h5 class="card-title">Expired</h5>
                        <h3 id="expiredCount">-</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body text-center">
                        <h5 class="card-title">Total</h5>
                        <h3 id="totalCount">-</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="mb-3 d-flex align-items-center" style="gap:0;">
            <select id="inviteField" class="form-select w-auto" 
                    style="border-top-right-radius:0; border-bottom-right-radius:0; height:38px; padding-top:6px; padding-bottom:6px;">
                <option value="all">All Fields</option>
                <option value="code">Invite Code</option>
                <option value="user_type">User Type</option>
                <option value="email">Email</option>
                <option value="room_code">Room Code</option>
                <option value="created_by_name">Created By</option>
                <option value="created_at">Created At</option>
                <option value="expires_at">Expires At</option>
                <option value="is_used">Status</option>
            </select>
            <input type="text" id="inviteSearch" class="form-control" placeholder="Search invite codes..." 
                   style="border-top-left-radius:0; border-bottom-left-radius:0; height:38px;">
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-light">
                    <tr>
                        <th><i class="bi bi-key"></i> Invite Code</th>
                        <th><i class="bi bi-person-badge"></i> User Type</th>
                        <th><i class="bi bi-envelope"></i> Email</th>
                        <th><i class="bi bi-house"></i> Room Code</th>
                        <th><i class="bi bi-person-plus"></i> Created By</th>
                        <th><i class="bi bi-calendar-plus"></i> Created At</th>
                        <th><i class="bi bi-calendar-x"></i> Expires At</th>
                        <th><i class="bi bi-info-circle"></i> Status</th>
                        <th><i class="bi bi-gear"></i> Actions</th>
                    </tr>
                </thead>
                <tbody id="inviteEntry"></tbody>
            </table>
        </div>
    </div>
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
                    <option value="created_by_username">QR Creator</option>
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
                            <th>QR Creator</th>
                        </tr>
                    </thead>
                    <tbody id="logEntry"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
const API_URL = 'https://siewyaoying.synergy-college.org/ResidentManagementSystem/api.php';

// ---------- Residents ----------
let residentsData = [];
function renderResidents(filtered = null) {
    const container = document.getElementById('residents');
    container.innerHTML = '';
    (filtered || residentsData).forEach(resident => {
        const div = document.createElement('div');
        div.className = "d-flex justify-content-between align-items-center border-bottom py-2";
        const statusBadge = resident.is_active ? 
            '<span class="badge bg-success">Active</span>' : 
            '<span class="badge bg-secondary">Inactive</span>';
        
        div.innerHTML = `
            <div class="d-flex flex-column">
                <div class="d-flex align-items-center">
                    <span class="fw-bold">${resident.user}</span>
                    <span class="badge bg-primary ms-2">ID: ${resident.id}</span>
                    ${statusBadge}
                </div>
                <div class="text-muted small">
                    <i class="bi bi-envelope"></i> ${resident.email}
                </div>
                <div class="text-muted small">
                    <i class="bi bi-house"></i> Room: <span class="fw-bold text-dark">${resident.room_code}</span>
                </div>
            </div>
            <div>
                <button class="btn btn-sm btn-outline-secondary" onclick="window.location.href='editResident.php?id=${resident.id}&name=${resident.user}&room=${resident.room_code}&email=${resident.email}'">Edit</button>
                <button class="btn btn-sm btn-outline-danger" onclick="deleteResident(${resident.id})">Delete</button>
            </div>`;
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
        const statusBadge = sec.is_active ? 
            '<span class="badge bg-success">Active</span>' : 
            '<span class="badge bg-secondary">Inactive</span>';
        
        div.innerHTML = `
            <div class="d-flex flex-column">
                <div class="d-flex align-items-center">
                    <span class="fw-bold">${sec.user}</span>
                    <span class="badge bg-info ms-2">ID: ${sec.id}</span>
                    ${statusBadge}
                </div>
                <div class="text-muted small">
                    <i class="bi bi-envelope"></i> ${sec.email}
                </div>
            </div>
            <div>
                <button class="btn btn-sm btn-outline-secondary" onclick="window.location.href='editSecurity.php?id=${sec.id}&name=${sec.user}&email=${sec.email}'">Edit</button>
                <button class="btn btn-sm btn-outline-danger" onclick="deleteSecurity(${sec.id})">Delete</button>
            </div>`;
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
            <td>${log.scanner_username}</td>
            <td>${log.created_by_username || 'N/A'} ${log.created_by_room ? '(' + log.created_by_room + ')' : ''}</td>`;
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

// ---------- Invite Codes ----------
let inviteCodesData = [];
function renderInviteCodes(filtered = null) {
    const container = document.getElementById('inviteEntry');
    container.innerHTML = '';
    (filtered || inviteCodesData).forEach(invite => {
        console.log('Processing invite:', invite.code, 'is_used:', invite.is_used, 'type:', typeof invite.is_used);
        
        const now = new Date();
        const expiresAt = new Date(invite.expires_at);
        const isExpired = expiresAt < now;
        
        let status, statusClass;
        if (invite.is_used == 1 || invite.is_used === '1' || invite.is_used === true) {
            status = 'Accepted';
            statusClass = 'bg-success';
        } else if (isExpired) {
            status = 'Expired';
            statusClass = 'bg-danger';
        } else {
            status = 'Pending';
            statusClass = 'bg-warning';
        }
        
        const roomCode = invite.room_code || '-';
        const usedAt = invite.used_at ? new Date(invite.used_at).toLocaleString() : '-';
        const timeRemaining = !invite.is_used && !isExpired ? 
            Math.ceil((expiresAt - now) / (1000 * 60 * 60)) + 'h left' : '';
        
        const entry = document.createElement('tr');
        entry.innerHTML = `
            <td>
                <code class="bg-light p-1 rounded">${invite.code}</code>
                ${timeRemaining ? `<br><small class="text-muted">${timeRemaining}</small>` : ''}
            </td>
            <td><span class="badge bg-${invite.user_type === 'resident' ? 'primary' : 'info'}">${invite.user_type}</span></td>
            <td>
                <i class="bi bi-envelope"></i> ${invite.email}
            </td>
            <td>
                ${invite.user_type === 'resident' ? 
                    `<i class="bi bi-house"></i> ${roomCode}` : 
                    '<span class="text-muted">-</span>'
                }
            </td>
            <td>${invite.created_by_name || 'Unknown'}</td>
            <td>${new Date(invite.created_at).toLocaleString()}</td>
            <td>${expiresAt.toLocaleString()}</td>
            <td>
                <span class="badge ${statusClass}">${status}</span>
                ${invite.is_used ? `<br><small class="text-muted">Used: ${usedAt}</small>` : ''}
            </td>
            <td>
                ${!invite.is_used ? 
                    `<button class="btn btn-sm btn-outline-danger" onclick="deleteInviteCode(${invite.id})" title="Delete unused invite">
                        <i class="bi bi-trash"></i>
                    </button>` : 
                    `<span class="text-muted">
                        <i class="bi bi-check-circle text-success"></i> Completed
                    </span>`
                }
            </td>`;
        container.appendChild(entry);
    });
}
function getInviteCodes(){
    fetch(`${API_URL}?type=admin&fetch=invite_codes`)
        .then(r=>r.json())
        .then(data=>{ 
            console.log('Invite codes data received:', data);
            inviteCodesData = data; 
            renderInviteCodes(); 
            updateInviteStats();
        });
}

function updateInviteStats() {
    const now = new Date();
    let pending = 0, accepted = 0, expired = 0;
    
    inviteCodesData.forEach(invite => {
        const expiresAt = new Date(invite.expires_at);
        const isExpired = expiresAt < now;
        
        if (invite.is_used == 1 || invite.is_used === '1' || invite.is_used === true) {
            accepted++;
        } else if (isExpired) {
            expired++;
        } else {
            pending++;
        }
    });
    
    document.getElementById('pendingCount').textContent = pending;
    document.getElementById('acceptedCount').textContent = accepted;
    document.getElementById('expiredCount').textContent = expired;
    document.getElementById('totalCount').textContent = inviteCodesData.length;
}
document.getElementById('inviteSearch').addEventListener('input', filterInviteCodes);
document.getElementById('inviteField').addEventListener('change', filterInviteCodes);
function filterInviteCodes(){
    const query = document.getElementById('inviteSearch').value.toLowerCase();
    const field = document.getElementById('inviteField').value;
    let filtered;
    if (!query) filtered = inviteCodesData;
    else if (field === 'all') filtered = inviteCodesData.filter(invite =>
        Object.values(invite).some(v=>String(v).toLowerCase().includes(query)));
    else filtered = inviteCodesData.filter(invite => String(invite[field]).toLowerCase().includes(query));
    renderInviteCodes(filtered);
}

// ---------- Delete Functions ----------
function deleteResident(id){ if(confirm("Delete this resident?")){ fetch(API_URL,{method:'DELETE',headers:{'Content-Type':'application/json'},body:JSON.stringify({type:'admin',fetch:'resident',id})}).then(r=>r.json()).then(d=>{alert(d.message);getResidents();});}}
function deleteSecurity(id){ if(confirm("Delete this security staff?")){ fetch(API_URL,{method:'DELETE',headers:{'Content-Type':'application/json'},body:JSON.stringify({type:'admin',fetch:'security',id})}).then(r=>r.json()).then(d=>{alert(d.message);getSecurity();});}}
function deleteAdmin(id){ if(confirm("Delete this admin?")){ fetch(API_URL,{method:'DELETE',headers:{'Content-Type':'application/json'},body:JSON.stringify({type:'admin',fetch:'admin',id})}).then(r=>r.json()).then(d=>{alert(d.message);getAdmins();});}}
function deleteInviteCode(id){ if(confirm("Delete this invite code?")){ fetch(API_URL,{method:'DELETE',headers:{'Content-Type':'application/json'},body:JSON.stringify({type:'admin',fetch:'invite_code',id})}).then(r=>r.json()).then(d=>{alert(d.message);getInviteCodes();});}}

// ---------- Init ----------
getResidents();
getSecurity();
getLogs();
getInviteCodes();
<?php if ($_SESSION['access_level'] >= 2): ?>getAdmins();<?php endif; ?>
</script>
</body>
</html>
