<?php session_start(); ?>

<html>
<head>
    <title>Resident Announcements</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css.css">
    <style>
        .announcement-card {
            margin-bottom: 15px;
        }
        .announcement-time {
            font-size: 0.85rem;
            color: gray;
        }
    </style>
</head>
<body>
    <?php include('../topbar.php'); ?>
    <div class="main-content" style="margin-left: 250px; min-height: calc(100vh - 70px); padding-top: 20px;">
        <!-- Sidebar -->
        <?php $current_page = 'announcements'; include 'sidebar.php'; ?>
        
        <div class="container-fluid" style="padding: 20px;">
            <div class="row">
                <div class="col-12">
                    <div class="card p-4">
                        <h2 class="mt-3">Announcements</h2>
                        <input type="text" id="announcement-search" class="form-control mb-3" placeholder="Search announcements...">
                        <div id="announcements" class="mt-4"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const API_URL = 'http://localhost/Finals_CheckInSystem%20ai/api.php';
        const searchInput = document.getElementById('announcement-search');
        const container = document.getElementById('announcements');
        let allAnnouncements = []; // Store fetched announcements

        async function getAnnouncements() {
            try {
                const response = await fetch(`${API_URL}?type=resident&fetch=announcements&id=<?=$_SESSION['id']?>`);
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);

                const data = await response.json();
                allAnnouncements = data; // Save all announcements
                renderAnnouncements(allAnnouncements);

            } catch (error) {
                console.error('Error fetching announcements:', error);
                if (container) container.innerHTML = `<p class="text-danger">Failed to load announcements.</p>`;
            }
        }

        function renderAnnouncements(list) {
            if (!container) return;
            container.innerHTML = '';

            if (list.length === 0) {
                container.innerHTML = '<p>No announcements found.</p>';
                return;
            }

            list.forEach(announcement => {
                const cardDiv = document.createElement('div');
                cardDiv.className = 'card announcement-card mb-3';
                cardDiv.innerHTML = `
                    <div class="card-body">
                        <h5 class="card-title">${announcement.title}</h5>
                        <p class="announcement-time">${announcement.post_time}</p>
                        <p class="card-text">${announcement.content}</p>
                    </div>
                `;
                container.appendChild(cardDiv);
            });
        }

        // Live search
        searchInput.addEventListener('input', (e) => {
            const query = e.target.value.toLowerCase();
            const filtered = allAnnouncements.filter(a =>
                a.title.toLowerCase().includes(query) ||
                a.content.toLowerCase().includes(query)
            );
            renderAnnouncements(filtered);
        });

        // Initial fetch
        getAnnouncements();
        // Refresh every 30 seconds
        setInterval(getAnnouncements, 30000);
    });
    </script>


</body>
</html>
