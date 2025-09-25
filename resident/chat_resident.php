<?php
session_start();
if (!isset($_SESSION['type']) || $_SESSION['type'] !== 'resident') {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/x-icon" href="../ico/house-icon.ico">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resident Chat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css.css">
</head>
<body>
    <?php include('../topbar.php'); ?>
    
    <!-- Mobile Sidebar Toggle Button -->
    <button class="sidebar-toggle d-md-none" onclick="toggleSidebar()">
        <i class="bi bi-list"></i>
    </button>
    
    <div class="main-content" style="margin-left: 250px; min-height: calc(100vh - 70px); padding-top: 20px;">
        <!-- Sidebar -->
        <?php $current_page = 'chat'; include 'sidebar.php'; ?>
        <!-- Main Card -->
        <div class="container d-flex justify-content-center align-items-center" style="min-height: calc(100vh - 90px);">
            <div class="card p-4 w-100" style="max-width: 900px;">
                <div class="row">
                    <div class="col-md-4 mb-3 mb-md-0">
                        <div class="card p-3">
                            <input type="text" class="form-control mb-3" id="searchSecurity" placeholder="Search security guards...">
                            <div class="user-list" id="securityList"></div>
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
        let currentSecurityId = null;
        let currentSecurityName = null;

        // Add search functionality
        function filterSecurityGuards(searchTerm) {
            const securityList = document.getElementById('securityList');
            const items = securityList.getElementsByClassName('user-item');
            
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
        document.getElementById('searchSecurity').addEventListener('input', (e) => {
            filterSecurityGuards(e.target.value);
        });

        // Fetch and display only security guards who have messaged the resident before
        function loadSecurityGuards() {
            fetch('../api.php?type=resident&fetch=messaged_security_list&created_by=<?php echo $_SESSION['id']; ?>')
                .then(response => response.json())
                .then(security => {
                    const securityList = document.getElementById('securityList');
                    securityList.innerHTML = '';
                    if (security.length === 0) {
                        securityList.innerHTML = '<div class="text-muted">No security guards have messaged you yet.</div>';
                        return;
                    }
                    security.forEach(guard => {
                        const div = document.createElement('div');
                        div.className = 'user-item';
                        div.textContent = guard.user;
                        div.onclick = () => selectSecurity(guard.id, guard.user);
                        securityList.appendChild(div);
                    });
                    // Apply any existing search filter
                    const searchInput = document.getElementById('searchSecurity');
                    if (searchInput.value) {
                        filterSecurityGuards(searchInput.value);
                    }
                });
        }


        // Select a security guard to chat with
        function selectSecurity(id, name) {
            currentSecurityId = id;
            currentSecurityName = name;
            document.querySelectorAll('.user-item').forEach(item => {
                item.classList.remove('active');
            });
            event.target.classList.add('active');
            loadMessages();
        }

        // Load messages for selected security guard
        function loadMessages() {
            if (!currentSecurityId) return;
            fetch(`../api.php?type=resident&fetch=messages&security_id=${currentSecurityId}&created_by=<?php echo $_SESSION['id']; ?>`)
                .then(response => response.json())
                .then(messages => {
                    const chatMessages = document.getElementById('chatMessages');
                    chatMessages.innerHTML = '';
                    messages.forEach(message => {
                        const div = document.createElement('div');
                        div.className = `message ${message.sender_type === 'resident' ? 'sent' : 'received'}`;
                        let senderLabel = '';
                        if (message.sender_type === 'resident') {
                            senderLabel = '<span style="font-size:1.1rem;font-weight:700;color:#007bff;background:#e0f7ff;padding:2px 8px;border-radius:6px;">You</span> ';
                        } else {
                            senderLabel = '<span style="font-size:1.1rem;font-weight:700;color:#b47aff;background:#f3eaff;padding:2px 8px;border-radius:6px;">Security</span> ';
                        }
                        div.innerHTML = senderLabel + '<span style="font-size:1.15rem;">' + message.message + '</span>';
                        chatMessages.appendChild(div);
                    });
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                });
        }

        // Send a message
        function sendMessage() {
            if (!currentSecurityId) return;
            
            const messageInput = document.getElementById('messageInput');
            const message = messageInput.value.trim();
            if (!message) return;

            fetch('../api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    type: 'chat',
                    sender_id: <?php echo $_SESSION['id']; ?>,
                    sender_type: 'resident',
                    receiver_id: currentSecurityId,
                    receiver_type: 'security',
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

        // Load security guards on page load
        loadSecurityGuards();

        // Poll for new messages every 5 seconds
        setInterval(() => {
            if (currentSecurityId) loadMessages();
            loadSecurityGuards();
        }, 5000);
    </script>
    
    <!-- Mobile JavaScript -->
    <script src="../js/mobile.js"></script>
</body>
</html> 