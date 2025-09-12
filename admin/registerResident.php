<html>
    <head>
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
                <h2>Register Resident</h2>
                <form id="registerForm">
                    <div class="mb-3">
                        <label for="user">Username: </label>
                        <input type="text" name="user" id="user" class="form-control" required/><br/>
                    </div>
                    
                    <div class="mb-3">
                        <label for="pass">Password: </label>
                        <input type="password" name="pass" id="pass" class="form-control" required/><br/>
                    </div>

                    <div class="mb-3">
                        <label for="room_code">Room Code (Format: 00-00-A0): </label>
                        <input type="text" name="room_code" id="room_code" 
                            pattern="[0-9]{2}-[0-9]{2}-[A-Z][0-9]" 
                            title="Room code format: 00-00-A0 (e.g. 12-34-B5)" class="form-control" required/><br/>
                    </div>
                    
                    
                    <button type="submit" class="btn btn-primary w-100 mb-2">Register</button>
                </form>
            </div>
        </div>
    </body>

    <script>
        const API_URL = 'http://localhost/Finals_CheckInSystem%20ai/api.php';

        document.getElementById("registerForm").addEventListener("submit", e =>{
            e.preventDefault();

            const user = document.getElementById('user').value;
            const pass = document.getElementById('pass').value;
            const room_code = document.getElementById('room_code').value;
            
            // Additional client-side validation
            const roomCodeRegex = /^[0-9]{2}-[0-9]{2}-[A-Z][0-9]$/;
            if (!roomCodeRegex.test(room_code)) {
                alert("Invalid room code format. Please use format: 00-00-A0 (e.g. 12-34-B5)");
                return;
            }

            const type = "register_resident";
            fetch(API_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ type, user, pass, room_code })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Unable to register resident');
                }
                return response.json();
            })
            .then(data => {
                alert(data.message);
                if(!data.error){
                    window.location.href = "manage.php";
                }
            })
            .catch(error => {
                console.error('Error registering resident: ', error);
            });
        });
    </script>
</html>