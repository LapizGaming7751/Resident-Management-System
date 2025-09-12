<html>
<head>
    <title>Register Security Staff</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css.css">
</head>
<body>
<?php include('../topbar.php'); ?>
<div class="container mt-3 d-flex justify-content-center align-items-center">
    <div class="card p-4 d-flex flex-column align-items-center" style="max-width: 400px; width: 100%;">
        <?php $current_page = 'add_security'; include 'sidebar.php'; ?>
        <h2>Register Security</h2>
        <form id="registerForm">
            <div class="mb-3">
                <label for="user">Username:</label>
                <input type="text" name="user" id="user" class="form-control" required/>
            </div>
            <div class="mb-3">
                <label for="pass">Password:</label>
                <input type="password" name="pass" id="pass" class="form-control" required/>
            </div>
            <button type="submit" class="btn btn-primary w-100 mb-2">Register</button>
        </form>
    </div>
</div>
</body>

<script>
const API_URL = 'http://localhost/Finals_CheckInSystem%20ai/api.php';

document.getElementById("registerForm").addEventListener("submit", e => {
    e.preventDefault();

    const user = document.getElementById('user').value;
    const pass = document.getElementById('pass').value;

    const type = "register_security";
    fetch(API_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ type, user, pass })
    })
    .then(r => {
        if (!r.ok) throw new Error('Unable to register security staff');
        return r.json();
    })
    .then(data => {
        alert(data.message);
        if (!data.error) {
            window.location.href = "manage.php";
        }
    })
    .catch(err => console.error('Error registering security:', err));
});
</script>
</html>
