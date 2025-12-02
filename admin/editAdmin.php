<html>
    <head>
        <link rel="icon" type="image/x-icon" href="../ico/house-icon.ico">
        <title>Edit Admin</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
        <link rel="stylesheet" href="../css.css">
    </head>
    <body>
        <?php include('../topbar.php'); ?>
        <div class="container mt-3 d-flex justify-content-center align-items-center">
            <div class="card p-4 d-flex flex-column align-items-center" style="max-width: 400px; width: 100%;">
                <?php $current_page = 'manage_admin'; include 'sidebar.php'; ?>
                <h2>Edit Admin</h2>
                <form id="editForm">
                    <div class="mb-3">
                        <label for="user">Username: </label>
                        <input type="text" name="user" id="user" class="form-control" required
                        value="<?=$_GET['name']?>"/><br/>
                    </div>

                    <div class="mb-3">
                        <label for="access_level">Access Level: </label>
                        <select name="access_level" id="access_level" class="form-control" required>
                            <option value="1" <?= ($_GET['level']==1?'selected':'') ?>>Standard Admin</option>
                            <option value="2" <?= ($_GET['level']==2?'selected':'') ?>>Super Admin</option>
                        </select><br/>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 mb-2">Update</button>
                </form>
            </div>
        </div>
    </body>

    <script>
        const API_URL = 'https://siewyaoying.synergy-college.org/ResidentManagementSystem/api.php';

        document.getElementById("editForm").addEventListener("submit", e =>{
            e.preventDefault();

            const id = <?=$_GET['id']?>;
            const user = document.getElementById('user').value;
            const access_level = document.getElementById('access_level').value;

            const type = "update_admin";
            fetch(API_URL, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ type, id, user, access_level })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Unable to update admin');
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
                console.error('Error updating admin: ', error);
            });
        });
    </script>
</html>
