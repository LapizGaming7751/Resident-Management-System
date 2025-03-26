<html>
    <head>
        <title>Scanner Login</title>
        <link rel="stylesheet" href="css.css">
    </head>
    <body>
        <div class="cd-1">
        <h1>Login Security Guard</h1>
            <form id="loginForm">
                <label for="user">Username: </label>
                <input type="text" name="user" id="user"/><br/>
                <label for="pass">Password: </label>
                <input type="password" name="pass" id="pass"/><br/>
                <button type="submit">Login</button>
            </form>

            <button onclick="window.location.href='resident/index.php'">Login as Owner</button>
        </div>
    </body>

    <script>
        const API_URL = 'http://localhost/Finals_CheckInSystem%20ai/api.php';

        document.getElementById("loginForm").addEventListener("submit", e =>{
            e.preventDefault();

            const user = document.getElementById('user').value;
            const pass = document.getElementById('pass').value;
            const type = "security";
            const url = `${API_URL}?type=${type}&user=${user}&pass=${pass}`;

            fetch(url, {
                method: 'GET',
                headers: {'Content-type':'application/json'}
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Unable to find security guard');
                }
                return response.json();
            })
            .then(data => {
                alert(data.message);
                if(!data.error){
                    window.location.href = "scanner.php";
                }
            })
            .catch(error => {
                console.error('Error finding security guard: ', error);
            });
        });
    </script>
</html>