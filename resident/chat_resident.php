<?php
// Include secure configuration
require_once '../config.php';

configureSecureSession();

if (!isset($_SESSION['type']) || $_SESSION['type'] !== 'resident') {
    echo '<div style="color:red;text-align:center;margin-top:2em;">Error: Resident session not found. Please log in again.</div>';
    exit;
}

// Generate CSRF token for chat
$csrf_token = generateCSRFToken();
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
    
    <div class="main-content">
        <!-- Sidebar -->
        <?php $current_page = 'chat'; include 'sidebar.php'; ?>
        <!-- Main Card -->
        <div class="container d-flex justify-content-center align-items-center min-vh-100">
            <div class="card p-4 w-100" style="max-width: 900px;">
                <div class="row">
                    <div class="col-md-4 mb-3 mb-md-0">
                        <div class="card p-3">
                            <input type="text" class="form-control mb-3" id="searchSecurity" placeholder="Search security guards...">
                            <?php
                                // Server-side render initial list of security guards who have chatted with this resident
                                $conn = getDatabaseConnection();
                                $stmt = $conn->prepare("SELECT DISTINCT s.id, s.user FROM security s JOIN messages m ON ((m.sender_id = ? AND m.receiver_id = s.id) OR (m.receiver_id = ? AND m.sender_id = s.id)) ORDER BY s.user ASC");
                                $stmt->bind_param("ii", $_SESSION['id'], $_SESSION['id']);
                                $secHtml = '<div class="user-list" id="securityList">';
                                if ($stmt->execute()) {
                                    $result = $stmt->get_result();
                                    if ($result->num_rows > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                            $name = htmlspecialchars($row['user'], ENT_QUOTES);
                                            $secHtml .= "<div class=\"user-item\" onclick=\"selectSecurity({$row['id']}, '{$name}', event)\">{$name}</div>";
                                        }
                                    } else {
                                        $secHtml .= '<div class="text-muted">No security guards have messaged you yet.</div>';
                                    }
                                } else {
                                    $secHtml .= '<div class="text-danger">Failed to load security guards.</div>';
                                }
                                $secHtml .= '</div>';
                                echo $secHtml;
                                $stmt->close();
                            ?>
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
            fetch(`../api.php?type=chat&fetch=chatted_security&resident_id=<?php echo $_SESSION['id']; ?>`)
                .then(response => response.json())
                .then(security => {
                    const securityList = document.getElementById('securityList');
                    securityList.innerHTML = '';
                    if (!Array.isArray(security) || security.length === 0) {
                        securityList.innerHTML = '<div class="text-muted">No security guards have messaged you yet.</div>';
                        return;
                    }
                    security.forEach(guard => {
                        const div = document.createElement('div');
                        div.className = 'user-item';
                        div.textContent = guard.user;
                        div.addEventListener('click', (e) => selectSecurity(guard.id, guard.user, e));
                        securityList.appendChild(div);
                    });
                    // Apply any existing search filter
                    const searchInput = document.getElementById('searchSecurity');
                    if (searchInput.value) {
                        filterSecurityGuards(searchInput.value);
                    }
                }).catch(err => {
                    console.error('Failed to load security guards', err);
                });
        }


        // Select a security guard to chat with
        function selectSecurity(id, name, event) {
            currentSecurityId = id;
            currentSecurityName = name;
            document.querySelectorAll('.user-item').forEach(item => {
                item.classList.remove('active');
            });
            try {
                const el = event && event.currentTarget ? event.currentTarget : event && event.target ? event.target : null;
                if (el) el.classList.add('active');
            } catch (e) {
                // ignore
            }
            loadMessages();
        }

        // Load messages for selected security guard
        function loadMessages() {
            if (!currentSecurityId) return;
            fetch(`../api.php?type=chat&fetch=messages&security_id=${currentSecurityId}&resident_id=<?php echo $_SESSION['id']; ?>`)
                .then(response => response.json())
                .then(messages => {
                    const chatMessages = document.getElementById('chatMessages');
                    chatMessages.innerHTML = '';
                    messages.forEach(message => {
                        // Prefer sender_type returned by the API; fallback to comparing sender_id
                        const senderType = (message.sender_type) ? (message.sender_type === 'resident' ? 'resident' : 'security') : ((message.sender_id == <?php echo $_SESSION['id']; ?>) ? 'resident' : 'security');
                        const div = document.createElement('div');
                        div.className = `message ${senderType === 'resident' ? 'sent' : 'received'}`;
                        let senderLabel = '';
                        if (senderType === 'resident') {
                            senderLabel = '<span style="font-size:1.1rem;font-weight:700;color:#007bff;background:#e0f7ff;padding:2px 8px;border-radius:6px;">You</span> ';
                        } else {
                            senderLabel = '<span style="font-size:1.1rem;font-weight:700;color:#b47aff;background:#f3eaff;padding:2px 8px;border-radius:6px;">Security</span> ';
                        }
                        div.innerHTML = senderLabel + '<span style="font-size:1.15rem;">' + message.message + '</span>';
                        chatMessages.appendChild(div);
                    });
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                }).catch(err => {
                    console.error('Failed to load messages', err);
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
                    message: message,
                    csrf_token: '<?= $csrf_token ?>'
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