
<html>
    <head>
        <title>Admin Login</title>
    </head>
    <body>
        <div class="cd-1">
        <h1>Login Admin</h1>
            <form id="loginForm">
                <label for="user">Username: </label>
                <input type="text" name="user" id="user"/><br/>
                <label for="pass">Password: </label>
                <input type="password" name="pass" id="pass"/><br/>
                <button type="submit">Login</button>
            </form>
        </div>
    </body>

    <script>
        const API_URL = 'http://localhost/Finals_CheckInSystem%20ai/api.php';

        document.getElementById("loginForm").addEventListener("submit", e =>{
            e.preventDefault();

            const user = document.getElementById('user').value;
            const pass = document.getElementById('pass').value;
                const url = `${API_URL}?type=admin&user=${user}&pass=${pass}`;

            fetch(url, {
                    method: 'GET',
                headers: {'Content-type':'application/json'}
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Unable to find admin');
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
                console.error('Error finding admin: ', error);
            });
        });
    </script>
</html>