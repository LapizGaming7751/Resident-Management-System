<?php
session_start();

if (isset($_SESSION['type']) && $_SESSION['type']=="security"){
?>
<html>
    <head>
        <title>Security Chat</title>
        <link rel="stylesheet" href="css.css">
        <style>
            .chat-container {
                display: flex;
                height: 100vh;
            }
            .residents-list {
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
            .resident-item {
                padding: 10px;
                margin: 5px 0;
                cursor: pointer;
                border: 1px solid #ccc;
                border-radius: 5px;
            }
            .resident-item:hover {
                background-color: #f0f0f0;
            }
            .search-box {
                width: 100%;
                padding: 10px;
                margin-bottom: 10px;
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
        </style>
    </head>
    <body>
        <div class="chat-container">
            <div class="residents-list">
                <h2>Residents</h2>
                <input type="text" class="search-box" id="searchResident" placeholder="Search residents...">
                <div id="residentsList"></div>
            </div>
            <div class="chat-box">
                <h2>Chat with <span id="selectedResident">Select a resident</span></h2>
                <div class="chat-messages" id="chatMessages"></div>
                <div class="chat-input">
                    <input type="text" id="messageInput" placeholder="Type your message...">
                    <button onclick="sendMessage()">Send</button>
                </div>
                <button class="call-button" id="callButton" onclick="startCall()" disabled>Start Call</button>
            </div>
        </div>

        <script>
            const API_URL = 'https://siewyaoying.synergy-college.org/Finals_CheckInSystem/api.php';
            let selectedResidentId = null;
            let selectedResidentName = null;
            let peerConnection = null;
            let localStream = null;

            // Load residents
            function loadResidents() {
                fetch(`${API_URL}?type=admin&fetch=resident`)
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('residentsList');
                    container.innerHTML = '';
                    data.forEach(resident => {
                        const div = document.createElement('div');
                        div.className = 'resident-item';
                        div.innerHTML = `${resident.user} (${resident.room_code})`;
                        div.onclick = () => selectResident(resident.id, resident.user);
                        container.appendChild(div);
                    });
                });
            }

            // Select resident
            function selectResident(id, name) {
                selectedResidentId = id;
                selectedResidentName = name;
                document.getElementById('selectedResident').textContent = name;
                document.getElementById('callButton').disabled = false;
                loadMessages();
            }

            // Search residents
            document.getElementById('searchResident').addEventListener('input', function(e) {
                const searchTerm = e.target.value.toLowerCase();
                const residents = document.querySelectorAll('.resident-item');
                residents.forEach(resident => {
                    const text = resident.textContent.toLowerCase();
                    resident.style.display = text.includes(searchTerm) ? 'block' : 'none';
                });
            });

            // Load messages
            function loadMessages() {
                if (!selectedResidentId) return;
                
                fetch(`${API_URL}?type=chat&fetch=messages&security_id=<?=$_SESSION['id']?>&resident_id=${selectedResidentId}`)
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
                if (!message || !selectedResidentId) return;

                fetch(API_URL, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        type: 'chat',
                        sender_id: <?=$_SESSION['id']?>,
                        receiver_id: selectedResidentId,
                        message: message
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
                    peerConnection = new RTCPeerConnection();
                    localStream.getTracks().forEach(track => peerConnection.addTrack(track, localStream));
                    
                    peerConnection.onicecandidate = event => {
                        if (event.candidate) {
                            // Send ICE candidate to resident
                            fetch(API_URL, {
                                method: 'POST',
                                headers: {'Content-Type': 'application/json'},
                                body: JSON.stringify({
                                    type: 'call',
                                    action: 'ice_candidate',
                                    security_id: <?=$_SESSION['id']?>,
                                    resident_id: selectedResidentId,
                                    candidate: event.candidate
                                })
                            });
                        }
                    };

                    const offer = await peerConnection.createOffer();
                    await peerConnection.setLocalDescription(offer);

                    // Send offer to resident
                    fetch(API_URL, {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({
                            type: 'call',
                            action: 'offer',
                            security_id: <?=$_SESSION['id']?>,
                            resident_id: selectedResidentId,
                            offer: offer
                        })
                    });

                    // Listen for answer
                    pollForAnswer();
                } catch (error) {
                    console.error('Error starting call:', error);
                }
            }

            // Poll for answer
            function pollForAnswer() {
                setInterval(async () => {
                    if (!peerConnection) return;

                    const response = await fetch(`${API_URL}?type=call&action=get_answer&security_id=<?=$_SESSION['id']?>&resident_id=${selectedResidentId}`);
                    const data = await response.json();
                    
                    if (data.answer) {
                        await peerConnection.setRemoteDescription(new RTCSessionDescription(data.answer));
                    }
                }, 1000);
            }

            // Initialize
            loadResidents();
            setInterval(loadMessages, 3000); // Poll for new messages every 3 seconds
        </script>
    </body>
</html>
<?php
} else {
    echo "Unauthorized access";
}
?> 