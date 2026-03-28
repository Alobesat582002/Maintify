<?php
require_once 'config/db.php';

// حماية الصفحة
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// 1. معالجة طلب حذف محادثة
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $sql = "DELETE FROM messages WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)";
    $stmt = $pdo->prepare($sql);
    if ($stmt->execute([$user_id, $delete_id, $delete_id, $user_id])) {
        $success = "Conversation deleted successfully.";
    } else {
        $error = "Failed to delete conversation.";
    }
}

// 2. معالجة تحديد كافة الإشعارات كـ "مقروءة"
$stmt_read = $pdo->prepare("UPDATE messages SET is_read = 1 WHERE receiver_id = ? AND is_read = 0");
$stmt_read->execute([$user_id]);

// 3. جلب قائمة الأشخاص الذين تفاعل معهم المستخدم (صندوق الوارد)
$query = "
    SELECT DISTINCT u.id, u.first_name, u.last_name, u.role, u.profile_image, 
           (SELECT message FROM messages 
            WHERE (sender_id = ? AND receiver_id = u.id) 
               OR (sender_id = u.id AND receiver_id = ?) 
            ORDER BY created_at DESC LIMIT 1) as last_message,
           (SELECT created_at FROM messages 
            WHERE (sender_id = ? AND receiver_id = u.id) 
               OR (sender_id = u.id AND receiver_id = ?) 
            ORDER BY created_at DESC LIMIT 1) as last_date,
           (SELECT COUNT(*) FROM messages 
            WHERE sender_id = u.id AND receiver_id = ? AND is_read = 0) as unread_count
    FROM users u
    JOIN messages m ON u.id = m.sender_id OR u.id = m.receiver_id
    WHERE (m.sender_id = ? OR m.receiver_id = ?) AND u.id != ?
    ORDER BY last_date DESC
";

$stmt = $pdo->prepare($query);
$stmt->execute([$user_id, $user_id, $user_id, $user_id, $user_id, $user_id, $user_id, $user_id]);
$inbox = $stmt->fetchAll();

include_once 'includes/header.php';
include_once 'includes/navbar.php';
?>

<div class="google-wrapper">
    <?php include_once 'includes/user_sidebar.php'; ?>

    <main class="google-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1"><?php echo $lang['my_messages'] ?? 'My Messages'; ?></h3>
            </div>
        </div>

        <?php if($error): ?> <div class="alert alert-danger rounded-pill fw-bold py-2 px-4 shadow-sm border-0 small"><i class="bi bi-x-circle-fill me-2"></i><?php echo $error; ?></div> <?php endif; ?>
        <?php if($success): ?> <div class="alert alert-success rounded-pill fw-bold py-2 px-4 shadow-sm border-0 small"><i class="bi bi-check-circle-fill me-2"></i><?php echo $success; ?></div> <?php endif; ?>

        <div class="google-card p-0">
            <div class="p-4 border-bottom bg-light rounded-top-4">
                <div class="row align-items-center">
                    <div class="col">
                        <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-envelope me-2"></i>Inbox</h6>
                    </div>
                    <div class="col-auto">
                        <span class="badge bg-primary rounded-pill px-3"><?php echo count($inbox); ?> Chats</span>
                    </div>
                </div>
            </div>
            
            <div class="list-group list-group-flush gap-1 p-2">
                <?php if(count($inbox) > 0): ?>
                    <?php foreach ($inbox as $contact): ?>
                        <div class="list-group-item list-group-item-action border rounded-4 p-3 shadow-sm hover-item position-relative mb-2">
                            <div class="d-flex align-items-center">
                                <?php 
                                    // هنا كان الخطأ، تم تعديل $tech إلى $contact
                                    $img_src = (!empty($contact['profile_image']) && $contact['profile_image'] !== 'default.png') 
                                        ? "assets/images/avatars/" . $contact['profile_image'] 
                                        : "assets/images/logo.png";
                                ?>
                                <img src="<?php echo htmlspecialchars($img_src); ?>" class="rounded-circle object-fit-cover border me-3" style="width: 55px; height: 55px;">
                                
                                <div class="w-100">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <h6 class="fw-bold mb-0 text-dark">
                                            <?php echo htmlspecialchars($contact['first_name'] . ' ' . $contact['last_name']); ?>
                                            <span class="badge bg-light text-muted border rounded-pill small fw-normal ms-1" style="font-size: 0.6rem;"><?php echo ucfirst($contact['role']); ?></span>
                                        </h6>
                                        <small class="text-muted fw-bold" dir="ltr" style="font-size: 11px;">
                                            <?php echo date('M d, H:i', strtotime($contact['last_date'])); ?>
                                        </small>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center">
                                        <p class="text-secondary small mb-0 text-truncate" style="max-width: 300px;">
                                            <?php if ($contact['unread_count'] > 0): ?>
                                                <i class="bi bi-circle-fill text-primary me-1" style="font-size: 7px;"></i> 
                                            <?php endif; ?>
                                            <?php echo htmlspecialchars($contact['last_message'] ?? 'Start chatting now!'); ?>
                                        </p>
                                        
                                        <?php if ($contact['unread_count'] > 0): ?>
                                            <span class="badge bg-danger rounded-pill px-2 py-1" style="font-size: 10px;"><?php echo $contact['unread_count']; ?> New</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <a href="chat.php?user_id=<?php echo $contact['id']; ?>" class="stretched-link"></a>
                            
                            <a href="messages.php?delete_id=<?php echo $contact['id']; ?>" class="btn btn-sm btn-light border rounded-circle text-danger position-absolute" style="top: 10px; right: 10px; z-index: 10; opacity: 0.8;" onclick="return confirm('<?php echo $lang['confirm_delete_convo'] ?? 'Delete this conversation?'; ?>');">
                                <i class="bi bi-trash-fill"></i>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="bi bi-envelope-slash fs-1 text-light-subtle d-block mb-3"></i>
                        <h6 class="fw-bold text-dark"><?php echo $lang['no_messages_found'] ?? 'No messages found'; ?></h6>
                        <p class="text-muted">You haven't interacted with anyone yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<style>
    .hover-item { transition: 0.2s; }
    .hover-item:hover { transform: translateY(-2px); box-shadow: 0 .25rem .75rem rgba(0,0,0,.15)!important; }
</style>

<?php include_once 'includes/footer.php'; ?>