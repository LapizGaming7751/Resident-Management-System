<html>
    <head>
        <title>Register new Resident</title>
    </head>
    <body>
        <h1>Register Resident</h1>
        <form id="registerForm">
            <label for="user">Username: </label>
            <input type="text" name="user" id="user" required/><br/>
            <label for="pass">Password: </label>
            <input type="password" name="pass" id="pass" required/><br/>
            <label for="room_code">Room Code (Format: 00-00-A0): </label>
            <input type="text" name="room_code" id="room_code" 
                   pattern="[0-9]{2}-[0-9]{2}-[A-Z][0-9]" 
                   title="Room code format: 00-00-A0 (e.g. 12-34-B5)" required/><br/>
            <button type="submit">Register</button>
        </form>
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