<?php
// Include secure configuration
require_once '../config.php';

configureSecureSession();

if (isset($_SESSION['type']) && $_SESSION['type']=="resident"){
    
    // Generate CSRF token for chat
    $csrf_token = generateCSRFToken();
?>
<html>
    <head>
        <title>Resident Chat</title>
        <link rel="stylesheet" href="../css.css">
        <style>
            .chat-container {
                display: flex;
                height: 100vh;
            }
            .security-list {
                width: 300px;
                border-right: 1px solid #ccc;
                padding: 20px;
            }
            .chat-box {
                flex: 1;
                display: flex;
                flex-direction: column;
                padding: 20px;
            }
            .chat-messages {
                flex: 1;
                overflow-y: auto;
                border: 1px solid #ccc;
                padding: 10px;
                margin-bottom: 10px;
            }
            .message {
                margin: 5px 0;
                padding: 10px;
                border-radius: 5px;
            }
            .sent {
                background-color: #e3f2fd;
                margin-left: 20%;
            }
            .received {
                background-color: #f5f5f5;
                margin-right: 20%;
            }
            .security-item {
                padding: 10px;
                margin: 5px 0;
                cursor: pointer;
                border: 1px solid #ccc;
                border-radius: 5px;
            }
            .security-item:hover {
                background-color: #f0f0f0;
            }
            .chat-input {
                display: flex;
                gap: 10px;
            }
            .chat-input input {
                flex: 1;
                padding: 10px;
            }
            .chat-input button {
                padding: 10px 20px;
            }
            .call-button {
                background-color: #4CAF50;
                color: white;
                border: none;
                padding: 10px 20px;
                border-radius: 5px;
                cursor: pointer;
                margin-top: 10px;
            }
            .call-button:hover {
                background-color: #45a049;
            }
            .video-container {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.9);
                z-index: var(--z-modal);
            }
            .video-container video {
                width: 100%;
                height: 100%;
                object-fit: contain;
            }
            .close-video {
                position: absolute;
                top: 20px;
                right: 20px;
                color: white;
                font-size: 24px;
                cursor: pointer;
            }
        </style>
    </head>
    <body>
        <div class="chat-container">
            <div class="security-list">
                <h2>Security Guards</h2>
                <div id="securityList"></div>
            </div>
            <div class="chat-box">
                <h2>Chat with <span id="selectedSecurity">Select a security guard</span></h2>
                <div class="chat-messages" id="chatMessages"></div>
                <div class="chat-input">
                    <input type="text" id="messageInput" placeholder="Type your message...">
                    <button onclick="sendMessage()">Send</button>
                </div>
                <button class="call-button" id="callButton" onclick="startCall()" disabled>Start Call</button>
            </div>
        </div>

        <div class="video-container" id="videoContainer">
            <span class="close-video" onclick="endCall()">&times;</span>
            <video id="localVideo" autoplay muted></video>
            <video id="remoteVideo" autoplay></video>
        </div>

        <script>
            // Use relative URL to avoid hardcoded URLs
            const API_URL = '../api.php';
            const CSRF_TOKEN = '<?= $csrf_token ?>';
            let selectedSecurityId = null;
            let selectedSecurityName = null;
            let peerConnection = null;
            let localStream = null;

            // Load security guards
            function loadSecurityGuards() {
                fetch(`${API_URL}?type=admin&fetch=security`)
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('securityList');
                    container.innerHTML = '';
                    data.forEach(security => {
                        const div = document.createElement('div');
                        div.className = 'security-item';
                        div.innerHTML = security.user;
                        div.onclick = () => selectSecurity(security.id, security.user);
                        container.appendChild(div);
                    });
                });
            }

            // Select security guard
            function selectSecurity(id, name) {
                selectedSecurityId = id;
                selectedSecurityName = name;
                document.getElementById('selectedSecurity').textContent = name;
                document.getElementById('callButton').disabled = false;
                loadMessages();
            }

            // Load messages
            function loadMessages() {
                if (!selectedSecurityId) return;
                
                fetch(`${API_URL}?type=chat&fetch=messages&resident_id=<?=$_SESSION['id']?>&security_id=${selectedSecurityId}`)
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('chatMessages');
                    container.innerHTML = '';
                    data.forEach(message => {
                        const div = document.createElement('div');
                        div.className = `message ${message.sender_id == <?=$_SESSION['id']?> ? 'sent' : 'received'}`;
                        div.textContent = message.message;
                        container.appendChild(div);
                    });
                    container.scrollTop = container.scrollHeight;
                });
            }

            // Send message
            function sendMessage() {
                const input = document.getElementById('messageInput');
                const message = input.value.trim();
                if (!message || !selectedSecurityId) return;

                fetch(API_URL, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        type: 'chat',
                        sender_id: <?=$_SESSION['id']?>,
                        receiver_id: selectedSecurityId,
                        message: message,
                        csrf_token: CSRF_TOKEN
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.error) {
                        input.value = '';
                        loadMessages();
                    }
                });
            }

            // Start call
            async function startCall() {
                try {
                    localStream = await navigator.mediaDevices.getUserMedia({ audio: true, video: true });
                    document.getElementById('localVideo').srcObject = localStream;
                    document.getElementById('videoContainer').style.display = 'block';

                    peerConnection = new RTCPeerConnection();
                    localStream.getTracks().forEach(track => peerConnection.addTrack(track, localStream));

                    peerConnection.ontrack = event => {
                        document.getElementById('remoteVideo').srcObject = event.streams[0];
                    };

                    peerConnection.onicecandidate = event => {
                        if (event.candidate) {
                            fetch(API_URL, {
                                method: 'POST',
                                headers: {'Content-Type': 'application/json'},
                                body: JSON.stringify({
                                    type: 'call',
                                    action: 'ice_candidate',
                                    resident_id: <?=$_SESSION['id']?>,
                                    security_id: selectedSecurityId,
                                    candidate: event.candidate
                                })
                            });
                        }
                    };

                    const offer = await peerConnection.createOffer();
                    await peerConnection.setLocalDescription(offer);

                    fetch(API_URL, {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({
                            type: 'call',
                            action: 'offer',
                            resident_id: <?=$_SESSION['id']?>,
                            security_id: selectedSecurityId,
                            offer: offer
                        })
                    });

                    pollForAnswer();
                } catch (error) {
                    console.error('Error starting call:', error);
                }
            }

            // End call
            function endCall() {
                if (localStream) {
                    localStream.getTracks().forEach(track => track.stop());
                }
                if (peerConnection) {
                    peerConnection.close();
                }
                document.getElementById('videoContainer').style.display = 'none';
                document.getElementById('localVideo').srcObject = null;
                document.getElementById('remoteVideo').srcObject = null;
            }

            // Poll for answer
            function pollForAnswer() {
                setInterval(async () => {
                    if (!peerConnection) return;

                    const response = await fetch(`${API_URL}?type=call&action=get_answer&resident_id=<?=$_SESSION['id']?>&security_id=${selectedSecurityId}`);
                    const data = await response.json();
                    
                    if (data.answer) {
                        await peerConnection.setRemoteDescription(new RTCSessionDescription(data.answer));
                    }
                }, 1000);
            }

            // Initialize
            loadSecurityGuards();
            setInterval(loadMessages, 3000); // Poll for new messages every 3 seconds
        </script>
    </body>
</html>
<?php
} else {
    echo "Unauthorized access";
}
?> 