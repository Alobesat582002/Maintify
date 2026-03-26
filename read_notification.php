<?php
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$notif_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT link FROM notifications WHERE id = ? AND user_id = ?");
$stmt->execute([$notif_id, $user_id]);
$notification = $stmt->fetch();

if ($notification) {
    $update_stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?");
    $update_stmt->execute([$notif_id]);

    $redirect_link = !empty($notification['link']) ? $notification['link'] : 'index.php';
    header("Location: " . $redirect_link);
    exit();
} else {
    // إذا كان الإشعار غير موجود أو حاول المستخدم فتح إشعار لشخص آخر
    header("Location: index.php");
    exit();
}
