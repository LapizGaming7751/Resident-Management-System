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
    <style>
        :root {
            --primary-color: #007bff;
            --secondary-color: #e0f7ff;
            --text-color: #333;
            --card-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--secondary-color);
            color: var(--text-color);
            line-height: 1.6;
        }

        .navbar {
            background-color: white;
            box-shadow: var(--card-shadow);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .nav-buttons {
            display: flex;
            gap: 1rem;
        }

        .nav-button {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            font-weight: 500;
        }

        .nav-button:hover {
            background-color: #0056b3;
            transform: translateY(-2px);
        }

        .chat-container {
            height: calc(100vh - 200px);
            display: flex;
            flex-direction: column;
        }
        .chat-messages {
            flex-grow: 1;
            overflow-y: auto;
            padding: 1rem;
            background-color: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        .message {
            margin-bottom: 1rem;
            padding: 0.8rem 1.2rem;
            border-radius: 1rem;
            max-width: 70%;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .message.sent {
            background-color: var(--primary-color);
            color: white;
            margin-left: auto;
        }
        .message.received {
            background-color: #e9ecef;
            margin-right: auto;
        }
        .user-list {
            height: calc(100vh - 200px);
            overflow-y: auto;
            background: white;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            padding: 1rem;
        }
        .user-item {
            padding: 0.8rem;
            border-bottom: 1px solid #dee2e6;
            cursor: pointer;
            transition: var(--transition);
        }
        .user-item:hover {
            background-color: #f8f9fa;
        }
        .user-item.active {
            background-color: #e9ecef;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <h2>Security Chat</h2>
        <div class="nav-buttons">
            <a href="scanner.php" class="nav-button">Back to Scanner</a>
            <a href="logout.php" class="nav-button">Logout</a>
        </div>
    </nav>

    <div class="container-fluid mt-3">
        <div class="row">
            <div class="col-md-3">
                <div class="user-list-container">
                    <div class="search-container mb-3">
                        <input type="text" class="form-control" id="searchResident" placeholder="Search residents...">
                    </div>
                    <div class="user-list" id="residentList"></div>
                </div>
            </div>
            <div class="col-md-9">
                <div class="chat-container">
                    <div class="chat-messages" id="chatMessages"></div>
                    <div class="input-group mt-3">
                        <input type="text" class="form-control" id="messageInput" placeholder="Type your message...">
                        <button class="btn btn-primary" id="sendButton">Send</button>
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
                        div.textContent = message.message;
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