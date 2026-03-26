<?php
require_once 'config/db.php';
// بدء الجلسة إذا لم تكن مبدوءة (لأن هذا الملف يعمل عبر AJAX)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$current_user = $_SESSION['user_id'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// إرسال رسالة جديدة
if ($action === 'send') {
    $receiver_id = $_POST['receiver_id'] ?? 0;
    $message = trim($_POST['message'] ?? '');

    if (!empty($receiver_id) && !empty($message)) {
        $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
        if ($stmt->execute([$current_user, $receiver_id, $message])) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error']);
        }
    }
    exit();
}

// جلب الرسائل
if ($action === 'fetch') {
    $other_user = $_GET['other_user'] ?? 0;
    
    // تحديث حالة الرسائل الواردة إلى "مقروءة"
    $stmt_read = $pdo->prepare("UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ?");
    $stmt_read->execute([$other_user, $current_user]);

    // جلب المحادثة بين الطرفين مرتبة حسب الوقت
    $stmt = $pdo->prepare("
        SELECT * FROM messages 
        WHERE (sender_id = ? AND receiver_id = ?) 
           OR (sender_id = ? AND receiver_id = ?) 
        ORDER BY created_at ASC
    ");
    $stmt->execute([$current_user, $other_user, $other_user, $current_user]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // إرجاع البيانات بصيغة JSON ليفهمها الجافاسكربت
    header('Content-Type: application/json');
    echo json_encode($messages);
    exit();
}
?>