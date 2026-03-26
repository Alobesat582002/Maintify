<?php
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['user_id'])) {
    die("User ID is required to start a chat.");
}

$current_user_id = $_SESSION['user_id'];
$other_user_id = $_GET['user_id'];

// جلب بيانات الطرف الآخر لعرض اسمه
$stmt = $pdo->prepare("SELECT first_name, last_name, role FROM users WHERE id = ?");
$stmt->execute([$other_user_id]);
$other_user = $stmt->fetch();

if (!$other_user) {
    die("User not found.");
}

include_once 'includes/header.php';
include_once 'includes/navbar.php';
?>

<style>
    .chat-container { height: 60vh; overflow-y: auto; padding: 15px; background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; margin-bottom: 15px; }
    .message { max-width: 75%; padding: 10px 15px; border-radius: 15px; margin-bottom: 10px; clear: both; font-size: 15px; }
    .message.sent { float: right; background-color: #4f46e5; color: white; border-bottom-right-radius: 2px; }
    .message.received { float: left; background-color: #f3f4f6; color: #111827; border-bottom-left-radius: 2px; }
    .message-time { display: block; font-size: 11px; margin-top: 5px; opacity: 0.7; }
</style>

<div class="container mt-5" style="max-width: 800px; min-height: 75vh;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Chat with <?php echo htmlspecialchars($other_user['first_name'] . ' ' . $other_user['last_name']); ?></h4>
        <span class="badge bg-secondary"><?php echo ucfirst($other_user['role']); ?></span>
    </div>

    <div class="chat-container shadow-sm" id="chatBox">
        </div>

    <form id="chatForm" class="d-flex gap-2">
        <input type="hidden" id="receiver_id" value="<?php echo htmlspecialchars($other_user_id); ?>">
        <input type="text" id="messageInput" class="form-control" placeholder="Type a message..." required autocomplete="off">
        <button type="submit" class="btn btn-primary px-4 fw-bold">Send</button>
    </form>
</div>

<script>
    const currentUserId = <?php echo $current_user_id; ?>;
    const receiverId = document.getElementById('receiver_id').value;
    const chatBox = document.getElementById('chatBox');
    const chatForm = document.getElementById('chatForm');
    const messageInput = document.getElementById('messageInput');

    // دالة لجلب الرسائل
    function fetchMessages() {
        fetch(`ajax_chat.php?action=fetch&other_user=${receiverId}`)
            .then(response => response.json())
            .then(data => {
                chatBox.innerHTML = '';
                data.forEach(msg => {
                    const isSent = (msg.sender_id == currentUserId);
                    const msgDiv = document.createElement('div');
                    msgDiv.className = `message ${isSent ? 'sent' : 'received'}`;
                    
                    // تنسيق الوقت
                    const date = new Date(msg.created_at);
                    const timeString = date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

                    msgDiv.innerHTML = `${msg.message} <span class="message-time">${timeString}</span>`;
                    chatBox.appendChild(msgDiv);
                });
                // التمرير التلقائي للأسفل
                chatBox.scrollTop = chatBox.scrollHeight;
            });
    }

    // إرسال الرسالة
    chatForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const message = messageInput.value.trim();
        if (!message) return;

        const formData = new FormData();
        formData.append('action', 'send');
        formData.append('receiver_id', receiverId);
        formData.append('message', message);

        fetch('ajax_chat.php', {
            method: 'POST',
            body: formData
        }).then(response => response.json())
          .then(data => {
              if (data.status === 'success') {
                  messageInput.value = '';
                  fetchMessages(); // تحديث فوري بعد الإرسال
              }
          });
    });

    // جلب الرسائل أول مرة، ثم تحديثها كل ثانيتين (Polling)
    fetchMessages();
    setInterval(fetchMessages, 2000);
</script>

<?php include_once 'includes/footer.php'; ?>