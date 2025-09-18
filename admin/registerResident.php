<html>
    <head>
        <link rel="icon" type="image/x-icon" href="../ico/house-icon.ico">
        <title>Register new Resident</title>
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
                        <label for="room_code">Room Code (Format: 00-00-A0): </label>
                        <input type="text" name="room_code" id="room_code" 
                            pattern="[0-9]{2}-[0-9]{2}-[A-Z][0-9]" 
                            title="Room code format: 00-00-A0 (e.g. 12-34-B5)" class="form-control" required/><br/>
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

        document.getElementById("registerForm").addEventListener("submit", e =>{
            e.preventDefault();

            const email = document.getElementById('email').value;
            const room_code = document.getElementById('room_code').value;
            const expiry_hours = document.getElementById('expiry_hours').value;
            
            // Additional client-side validation
            const roomCodeRegex = /^[0-9]{2}-[0-9]{2}-[A-Z][0-9]$/;
            if (!roomCodeRegex.test(room_code)) {
                alert("Invalid room code format. Please use format: 00-00-A0 (e.g. 12-34-B5)");
                return;
            }

            const type = "create_resident_invite";
            console.log('Sending request to:', API_URL);
            console.log('Request data:', { type, email, room_code, expiry_hours });
            
            fetch(API_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify({ type, email, room_code, expiry_hours })
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
</html>