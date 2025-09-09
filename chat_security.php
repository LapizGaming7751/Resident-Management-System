<?php
session_start();
if (!isset($_SESSION['type']) || $_SESSION['type'] !== 'security') {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Chat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css.css">
</head>
<body>
    <div class="d-flex min-vh-100" style="background: none;">
        <!-- Sidebar -->
        <div class="d-flex flex-column bg-white p-3" style="min-width:200px; height:100vh; border-radius:0; box-shadow:0 4px 16px rgba(0,0,0,0.08); justify-content:space-between; position:sticky; top:0; left:0;">
            <div>
                <h4 class="mb-4 text-center">Welcome,<br><?=$_SESSION['user']?></h4>
                <hr class="my-3">
                <button onclick="window.location.href='scanner.php';" class="btn btn-outline-primary w-100 mb-2">Scanner</button>
                <button onclick="window.location.href='logs.php';" class="btn btn-outline-primary w-100 mb-2">Manage Logs</button>
                <button class="btn btn-primary w-100 mb-2" disabled>Security Chat</button>
            </div>
            <button onclick="window.location.href='logout.php';" class="btn btn-danger w-100 mt-2">Logout</button>
        </div>
        <!-- Main Card -->
        <div class="container d-flex justify-content-center align-items-center flex-grow-1">
            <div class="card p-4 w-100" style="max-width: 900px;">
                <div class="row">
                    <div class="col-md-4 mb-3 mb-md-0">
                        <div class="card p-3">
                            <input type="text" class="form-control mb-3" id="searchResident" placeholder="Search residents...">
                            <div class="user-list" id="residentList"></div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="card p-3">
                            <div class="chat-messages mb-3" id="chatMessages" style="height:500px; overflow-y:auto; background: linear-gradient(135deg, #5ecbff 0%, #b47aff 100%);"></div>
                            <div class="input-group">
                                <input type="text" class="form-control" id="messageInput" placeholder="Type your message...">
                                <button class="btn btn-primary" id="sendButton">Send</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentResidentId = null;
        let currentResidentName = null;

        // Fetch and display resident list
        function loadResidents() {
            fetch('api.php?type=security&fetch=resident_list')
                .then(response => response.json())
                .then(residents => {
                    const residentList = document.getElementById('residentList');
                    residentList.innerHTML = '';
                    residents.forEach(resident => {
                        const div = document.createElement('div');
                        div.className = 'user-item';
                        div.textContent = `${resident.user} (${resident.room_code})`;
                        div.onclick = () => selectResident(resident.id, resident.user);
                        residentList.appendChild(div);
                    });
                    
                    // Apply any existing search filter
                    const searchInput = document.getElementById('searchResident');
                    if (searchInput.value) {
                        filterResidents(searchInput.value);
                    }
                });
        }

        // Select a resident to chat with
        function selectResident(id, name) {
            currentResidentId = id;
            currentResidentName = name;
            document.querySelectorAll('.user-item').forEach(item => {
                item.classList.remove('active');
            });
            event.target.classList.add('active');
            loadMessages();
        }

        // Load messages for selected resident
        function loadMessages() {
            if (!currentResidentId) return;
            fetch(`api.php?type=security&fetch=messages&resident_id=${currentResidentId}`)
                .then(response => response.json())
                .then(messages => {
                    const chatMessages = document.getElementById('chatMessages');
                    chatMessages.innerHTML = '';
                    messages.forEach(message => {
                        const div = document.createElement('div');
                        div.className = `message ${message.sender_type === 'security' ? 'sent' : 'received'}`;
                        let senderLabel = '';
                        if (message.sender_type === 'security') {
                            senderLabel = '<span style="font-size:1.1rem;font-weight:700;color:#fff;background:#007bff;padding:2px 8px;border-radius:6px;">You</span> ';
                        } else {
                            senderLabel = '<span style="font-size:1.1rem;font-weight:700;color:#007bff;background:#e0f7ff;padding:2px 8px;border-radius:6px;">Resident</span> ';
                        }
                        div.innerHTML = senderLabel + '<span style="font-size:1.15rem;">' + message.message + '</span>';
                        chatMessages.appendChild(div);
                    });
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                });
        }

        // Send a message
        function sendMessage() {
            if (!currentResidentId) return;
            
            const messageInput = document.getElementById('messageInput');
            const message = messageInput.value.trim();
            if (!message) return;

            fetch('api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    type: 'chat',
                    sender_id: <?php echo $_SESSION['id']; ?>,
                    sender_type: 'security',
                    receiver_id: currentResidentId,
                    receiver_type: 'resident',
                    message: message
                })
            })
            .then(response => response.json())
            .then(data => {
                if (!data.error) {
                    messageInput.value = '';
                    loadMessages();
                }
            });
        }

        // Event listeners
        document.getElementById('sendButton').addEventListener('click', sendMessage);
        document.getElementById('messageInput').addEventListener('keypress', (e) => {
            if (e.key === 'Enter') sendMessage();
        });

        // Load residents on page load
        loadResidents();

        // Poll for new messages every 5 seconds
        setInterval(() => {
            if (currentResidentId) loadMessages();
        }, 5000);

        // Add search functionality
        function filterResidents(searchTerm) {
            const residentList = document.getElementById('residentList');
            const items = residentList.getElementsByClassName('user-item');
            
            for (let item of items) {
                const text = item.textContent.toLowerCase();
                if (text.includes(searchTerm.toLowerCase())) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            }
        }

        // Add search event listener
        document.getElementById('searchResident').addEventListener('input', (e) => {
            filterResidents(e.target.value);
        });
    </script>
</body>
</html> 