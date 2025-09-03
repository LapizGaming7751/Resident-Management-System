<html>
    <head>
        <title>Scanner Login</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body {
                background: linear-gradient(135deg, #e0f7ff 0%, #e0cfff 100%);
            }
            .card {
                box-shadow: 0 4px 16px rgba(0,0,0,0.08);
                border-radius: 1rem;
            }
        </style>
    </head>
    <body>
        <div class="container min-vh-100 d-flex justify-content-center align-items-center">
            <div class="card p-4" style="max-width: 400px; width: 100%;">
                <h1 class="mb-4 text-center">Login Security Guard</h1>
                <form id="loginForm">
                    <div class="mb-3">
                        <label for="user" class="form-label">Username</label>
                        <input type="text" name="user" id="user" class="form-control"/>
                    </div>
                    <div class="mb-3">
                        <label for="pass" class="form-label">Password</label>
                        <input type="password" name="pass" id="pass" class="form-control"/>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 mb-2">Login</button>
                </form>
                <button onclick="window.location.href='resident/index.php'" class="btn btn-outline-primary w-100">Login as Owner</button>
            </div>
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