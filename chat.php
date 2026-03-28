<?php
require_once 'config/db.php';

// حماية الصفحة
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$other_user_id = $_GET['user_id'] ?? null;

if (!$other_user_id) {
    header("Location: messages.php");
    exit();
}

// 1. جلب بيانات المستخدم الآخر
$stmt_user = $pdo->prepare("SELECT first_name, last_name, profile_image, role FROM users WHERE id = ?");
$stmt_user->execute([$other_user_id]);
$other_user = $stmt_user->fetch();

if (!$other_user) {
    die("User not found.");
}

// 2. تحديد كافة الرسائل كمقروءة من قبل الطرف الآخر
$stmt_read = $pdo->prepare("UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ? AND is_read = 0");
$stmt_read->execute([$other_user_id, $user_id]);

// 3. معالجة إرسال رسالة جديدة
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_msg'])) {
    $message = trim($_POST['message']);
    
    if (!empty($message)) {
        // --- Smart Sender/Receiver ID Fix ---
        // في هذا الملف، $_SESSION['user_id'] هو دائماً الـ Sender
        // و $_GET['user_id'] ($other_user_id) هو دائماً الـ Receiver
        $sql = "INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id, $other_user_id, $message]);
        
        // إعادة تحميل الصفحة لتحديث الرسائل
        header("Location: chat.php?user_id=" . $other_user_id);
        exit();
    }
}

// 4. جلب كافة الرسائل بين المستخدمين
$query = "
    SELECT * FROM messages 
    WHERE (sender_id = ? AND receiver_id = ?) 
       OR (sender_id = ? AND receiver_id = ?)
    ORDER BY created_at ASC
";
$stmt = $pdo->prepare($query);
$stmt->execute([$user_id, $other_user_id, $other_user_id, $user_id]);
$messages = $stmt->fetchAll();

include_once 'includes/header.php';
include_once 'includes/navbar.php';
?>

<div class="google-wrapper">
    <?php include_once 'includes/user_sidebar.php'; ?>

    <main class="google-content">
        <div class="d-flex align-items-center mb-4">
            <a href="messages.php" class="btn btn-sm btn-light border rounded-pill me-3 text-muted px-3 fw-bold">
                <i class="bi bi-arrow-return-left"></i> <?php echo $lang['back'] ?? 'Back'; ?>
            </a>
            <h3 class="fw-bold mb-1"><?php echo htmlspecialchars($other_user['first_name']); ?></h3>
        </div>

        <div class="google-card p-0 d-flex flex-column h-100 overflow-hidden" style="max-height: 80vh;">
            
            <div class="p-3 border-bottom bg-light d-flex align-items-center rounded-top-4">
                <?php 
                    $img_src = (!empty($other_user['profile_image']) && $other_user['profile_image'] !== 'default.png') 
                        ? "assets/images/avatars/" . $other_user['profile_image'] 
                        : "assets/images/logo.png";
                ?>
                <img src="<?php echo htmlspecialchars($img_src); ?>" class="rounded-circle object-fit-cover border me-3" style="width: 45px; height: 45px;">
                <div>
                    <h6 class="fw-bold mb-0 text-dark"><?php echo htmlspecialchars($other_user['first_name'] . ' ' . $other_user['last_name']); ?></h6>
                    <span class="badge bg-primary rounded-pill small fw-normal ms-1" style="font-size: 0.6rem;"><?php echo ucfirst($other_user['role']); ?></span>
                </div>
            </div>

            <div class="flex-grow-1 p-4 overflow-y-auto" id="messageArea" style="background-color: #f8fafc;">
                <?php if (count($messages) > 0): ?>
                    <?php foreach ($messages as $msg): ?>
                        <?php $is_my_msg = ($msg['sender_id'] == $user_id); ?>
                        <div class="d-flex mb-4 <?php echo $is_my_msg ? 'justify-content-end' : 'justify-content-start'; ?>">
                            <div class="google-card p-3 rounded-4 shadow-sm" 
                                 style="max-width: 70%; <?php echo $is_my_msg ? 'background-color: #e3f2fd; border-color: #90caf9; border-inline-start-color: #1e88e3 !important;' : 'background-color: #ffffff; border-color: #dadce0;'; ?>">
                                
                                <p class="text-dark mb-2 small" style="line-height: 1.6; white-space: pre-wrap;"><?php echo htmlspecialchars($msg['message']); ?></p>
                                
                                <div class="text-end border-top pt-1 mt-1">
                                    <small class="text-muted fw-bold" dir="ltr" style="font-size: 10px;">
                                        <?php echo date('M d, H:i', strtotime($msg['created_at'])); ?>
                                        <?php if($is_my_msg): ?>
                                            <i class="bi bi-check2-all ms-1 <?php echo $msg['is_read'] ? 'text-primary' : ''; ?>"></i>
                                        <?php endif; ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="bi bi-chat-dots fs-1 text-light-subtle d-block mb-3"></i>
                        <h6 class="fw-bold text-dark">No messages yet</h6>
                        <p class="text-muted">Start the conversation with <?php echo htmlspecialchars($other_user['first_name']); ?>.</p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="p-3 border-top bg-white rounded-bottom-4 mt-auto">
                <form action="chat.php?user_id=<?php echo $other_user_id; ?>" method="POST" class="row g-2 align-items-center">
                    <div class="col">
                        <input type="text" name="message" class="form-control rounded-pill border-0 bg-light py-2 px-3" placeholder="<?php echo $lang['chat_placeholder']; ?>" required autofocus>
                    </div>
                    
                    <div class="col-auto">
                        <button type="button" class="btn btn-light rounded-circle text-warning fs-5 p-0 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="bi bi-emoji-laughing"></i>
                        </button>
                    </div>
                    
                    <div class="col-auto">
                        <button type="submit" name="submit_msg" class="btn btn-primary rounded-circle p-0 fs-5 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="bi bi-send-fill ms-1"></i>
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </main>
</div>

<script>
// سكربت لجعل منطقة الرسائل تمرر للأسفل عند تحميل الصفحة
document.addEventListener('DOMContentLoaded', function() {
    var messageArea = document.getElementById('messageArea');
    messageArea.scrollTop = messageArea.scrollHeight;
});
</script>

<?php include_once 'includes/footer.php'; ?>