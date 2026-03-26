<?php
require_once 'config/db.php';

// حماية الواجهة للمسجلين فقط
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// استعلام لجلب آخر رسالة من كل محادثة
$sql = "
    SELECT m.*, 
           u.id as contact_id, 
           u.first_name, 
           u.last_name, 
           u.role
    FROM messages m
    INNER JOIN (
        SELECT MAX(id) as max_id
        FROM messages
        WHERE sender_id = :uid1 OR receiver_id = :uid2
        GROUP BY IF(sender_id = :uid3, receiver_id, sender_id)
    ) latest ON m.id = latest.max_id
    INNER JOIN users u ON u.id = IF(m.sender_id = :uid4, m.receiver_id, m.sender_id)
    ORDER BY m.created_at DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    'uid1' => $user_id, 
    'uid2' => $user_id, 
    'uid3' => $user_id, 
    'uid4' => $user_id
]);
$conversations = $stmt->fetchAll();

include_once 'includes/header.php';
include_once 'includes/navbar.php';
?>

<div class="container mt-5" style="min-height: 70vh; max-width: 800px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Messages</h2>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="list-group list-group-flush rounded">
                <?php if (count($conversations) > 0): ?>
                    <?php foreach ($conversations as $conv): ?>
                        <?php 
                            // التحقق إذا كانت الرسالة غير مقروءة ومرسلة إليك
                            $is_unread = ($conv['is_read'] == 0 && $conv['receiver_id'] == $user_id); 
                        ?>
                        <a href="chat.php?user_id=<?php echo $conv['contact_id']; ?>" class="list-group-item list-group-item-action p-3 <?php echo $is_unread ? 'bg-light' : ''; ?>">
                            <div class="d-flex w-100 justify-content-between align-items-center mb-1">
                                <h6 class="mb-0 fw-bold <?php echo $is_unread ? 'text-primary' : 'text-dark'; ?>">
                                    <?php echo htmlspecialchars($conv['first_name'] . ' ' . $conv['last_name']); ?>
                                    <span class="badge bg-secondary ms-2" style="font-size: 0.7em;"><?php echo ucfirst($conv['role']); ?></span>
                                </h6>
                                <small class="text-muted"><?php echo date('M d, h:i A', strtotime($conv['created_at'])); ?></small>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <p class="mb-0 text-truncate text-muted" style="max-width: 85%; font-size: 14px;">
                                    <?php 
                                        // إضافة "You:" إذا كنت أنت من أرسل آخر رسالة
                                        if ($conv['sender_id'] == $user_id) echo 'You: '; 
                                        echo htmlspecialchars($conv['message']); 
                                    ?>
                                </p>
                                <?php if ($is_unread): ?>
                                    <span class="badge bg-danger rounded-circle p-2"><span class="visually-hidden">Unread messages</span></span>
                                <?php endif; ?>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="p-5 text-center text-muted">
                        <i class="bi bi-chat-square-text fs-1 d-block mb-3"></i>
                        <h5>No messages yet</h5>
                        <p>When you start chatting with technicians, your conversations will appear here.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>