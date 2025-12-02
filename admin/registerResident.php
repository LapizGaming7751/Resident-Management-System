<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../ico/house-icon.ico">
    <title>Register Resident</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css.css">
</head>
<body>
    <?php include('../topbar.php'); ?>
    
    <div class="container mt-3 d-flex justify-content-center align-items-center">
        <div class="card p-4 d-flex flex-column align-items-center" style="max-width: 400px; width: 100%;">
            <?php $current_page = 'add_resident'; include 'sidebar.php'; ?>
            <h2>Create Resident Invite</h2>
            <form id="registerForm">
                <div class="mb-3">
                    <label for="email">Email Address: </label>
                    <input type="email" name="email" id="email" class="form-control" required/><br/>
                </div>
                <div class="mb-3">
                    <label for="expiry_hours">Invite Expires In (Hours): </label>
                    <select name="expiry_hours" id="expiry_hours" class="form-control" required>
                        <option value="24">24 Hours</option>
                        <option value="48">48 Hours</option>
                        <option value="72">72 Hours</option>
                        <option value="168">7 Days</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary w-100 mb-2">Generate Invite Code</button>
            </form>
        </div>
    </div>
</body>

    <script>
        const API_URL = '../api.php';

        // Get CSRF token on page load
        document.addEventListener('DOMContentLoaded', function() {
            fetch(API_URL + '?type=get_csrf_token', {
                method: 'GET',
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                if (data.csrf_token) {
                    window.csrfToken = data.csrf_token;
                }
            })
            .catch(error => {
                console.error('Error getting CSRF token:', error);
            });
        });

        document.getElementById("registerForm").addEventListener("submit", e => {
            e.preventDefault();

            const email = document.getElementById('email').value;
            const expiry_hours = document.getElementById('expiry_hours').value;

            const type = "create_resident_invite";
            console.log('Sending request to:', API_URL);
            console.log('Request data:', { type, email, expiry_hours });
            
            fetch(API_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify({ 
                    type, 
                    email, 
                    expiry_hours, 
                    csrf_token: window.csrfToken || '' 
                })
            })
            .then(response => {
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                if (!response.ok) {
                    throw new Error('Unable to create resident invite');
                }
                return response.json();
            })
            .then(data => {
                if(!data.error){
                    if (data.email_sent) {
                        alert(`Invite code created and email sent successfully!\n\nInvite Code: ${data.invite_code}\nEmail: ${email}\nExpires: ${data.expires_at}\n\nAn email has been sent to the resident with registration instructions.`);
                    } else {
                        alert(`Invite code created successfully!\n\nInvite Code: ${data.invite_code}\nEmail: ${email}\nExpires: ${data.expires_at}\n\nNote: Email could not be sent. Please share this code manually with the resident.`);
                    }
                    window.location.href = "manage.php";
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error creating resident invite: ', error);
                alert('Error creating resident invite. Please try again.');
            });
        });
    </script>

<!-- Mobile JavaScript -->
<script src="../js/mobile.js"></script>
</html>
