<html>
    <head>
        <title>Resident Login</title>
        <link rel="stylesheet" href="../css.css">
    </head>
    <body>
        <h1>Login Resident</h1>
        <form id="loginForm">
            <label for="user">Username: </label>
            <input type="text" name="user" id="user"/><br/>
            <label for="pass">Password: </label>
            <input type="password" name="pass" id="pass"/><br/>
            <button type="submit">Login</button>
        </form>

        <button onclick="window.location.href='../index.php'">Login as Security</button>
    </body>

    <script>
        const API_URL = 'http://localhost/Finals_CheckInSystem/api.php';

        document.getElementById("loginForm").addEventListener("submit", e =>{
            e.preventDefault();

            const user = document.getElementById('user').value;
            const pass = document.getElementById('pass').value;
            const type = "resident";
            const url = `${API_URL}?type=${type}&user=${user}&pass=${pass}`;

            fetch(url, {
                method: 'GET',
                headers: {'Content-type':'application/json'}
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Unable to find resident');
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
                console.error('Error finding resident: ', error);
            });
        });
    </script>
</html>