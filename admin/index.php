
    <link rel="stylesheet" href="../css.css">
                <input type="text" name="user" id="user"/><br/>
                <label for="pass">Password: </label>
                <input type="password" name="pass" id="pass"/><br/>
                <button type="submit">Login</button>
            </form>
        </div>
    <link rel="stylesheet" href="../css.css">
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