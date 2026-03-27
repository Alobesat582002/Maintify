<?php
$base_url = '/Maintify/';

$unread_count = 0;
$unread_notif_count = 0;
$recent_notifications = [];

if (isset($_SESSION['user_id']) && isset($pdo)) {
    $stmt_unread = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND is_read = 0");
    $stmt_unread->execute([$_SESSION['user_id']]);
    $unread_count = $stmt_unread->fetchColumn();

    $stmt_notif = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt_notif->execute([$_SESSION['user_id']]);
    $unread_notif_count = $stmt_notif->fetchColumn();

    $stmt_notifs = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
    $stmt_notifs->execute([$_SESSION['user_id']]);
    $recent_notifications = $stmt_notifs->fetchAll();
}
?>

<nav class="navbar navbar-expand-lg maintify-nav bg-white border-bottom shadow-sm">
    <div class="container-fluid px-4">

        <a class="navbar-brand" href="<?php echo $base_url; ?>index.php">
            <img src="<?php echo $base_url; ?>assets/images/logo.png" alt="Maintify" class="nav-logo-img me-2" style="width: 35px;">
            <span class="nav-brand-name fw-bold fs-4 text-dark">Maint<span class="text-primary">ify</span></span>
        </a>

        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center gap-3">

                <?php if (isset($_SESSION['user_id'])): ?>
                    
                    <li class="nav-item icon-btn">
                        <a href="<?php echo $base_url; ?>messages.php" class="nav-link position-relative text-dark fs-5" title="Messages">
                            <i class="bi bi-chat-dots"></i>
                            <?php if ($unread_count > 0): ?>
                                <span class="position-absolute top-25 start-75 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
                                    <?php echo $unread_count; ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    </li>

                    <li class="nav-item dropdown icon-btn">
                        <a class="nav-link position-relative text-dark fs-5" href="#" id="notifDropdown" role="button" data-bs-toggle="dropdown" title="Notifications">
                            <i class="bi bi-bell"></i>
                            <?php if ($unread_notif_count > 0): ?>
                                <span class="position-absolute top-25 start-75 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
                                    <?php echo $unread_notif_count; ?>
                                </span>
                            <?php endif; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-4" aria-labelledby="notifDropdown" style="width: 320px;">
                            <li class="px-3 py-3 d-flex justify-content-between align-items-center border-bottom">
                                <span class="fw-bold">Notifications</span>
                                <?php if ($unread_notif_count > 0): ?>
                                    <span class="badge bg-primary rounded-pill"><?php echo $unread_notif_count; ?> New</span>
                                <?php endif; ?>
                            </li>

                            <?php if (count($recent_notifications) > 0): ?>
                                <?php foreach ($recent_notifications as $notif): ?>
                                    <li>
                                        <a class="dropdown-item py-3 border-bottom <?php echo $notif['is_read'] ? 'opacity-75' : 'bg-light'; ?>" 
                                           href="<?php echo $base_url; ?>read_notification.php?id=<?php echo $notif['id']; ?>">
                                            <div class="d-flex justify-content-between align-items-start mb-1">
                                                <span class="text-primary fw-bold" style="font-size: 13px;">
                                                    <i class="bi bi-circle-fill <?php echo $notif['is_read'] ? 'text-muted' : 'text-primary'; ?> me-1" style="font-size: 8px;"></i>
                                                    <?php echo htmlspecialchars($notif['title']); ?>
                                                </span>
                                                <small class="text-muted" style="font-size: 10px;">
                                                    <?php echo date('M d, H:i', strtotime($notif['created_at'])); ?>
                                                </small>
                                            </div>
                                            <div class="text-dark ps-3 text-wrap" style="font-size: 13px; line-height: 1.5;">
                                                <?php echo htmlspecialchars($notif['message']); ?>
                                            </div>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li class="p-4 text-center text-muted">
                                    <i class="bi bi-bell-slash fs-2 d-block mb-2 text-light-subtle"></i>
                                    <small>No notifications yet.</small>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </li>

                    <div class="vr d-none d-lg-block mx-1"></div>

                    <li class="nav-item dropdown user-menu">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" style="padding: 5px 10px; background: #f8fafc; border-radius: 50px; border: 1px solid #e5e7eb;">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 14px;">
                                <i class="bi bi-person-fill"></i>
                            </div>
                            <span class="fw-bold text-dark fs-6"><?php echo htmlspecialchars($_SESSION['name']); ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-4 mt-2" aria-labelledby="userDropdown">
                            <li>
                                <a class="dropdown-item py-2 fw-medium" href="<?php echo $base_url; ?><?php echo ($_SESSION['role'] === 'technician') ? 'Technician' : 'Homeowner'; ?>/profile.php">
                                    <i class="bi bi-person me-2 text-muted"></i> <?php echo $lang['profile']; ?>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item py-2 fw-medium" href="<?php echo $base_url; ?>settings.php">
                                    <i class="bi bi-gear me-2 text-muted"></i> <?php echo $lang['settings']; ?>
                                </a>
                            </li>
                            <li><hr class="dropdown-divider my-1"></li>
                            <li>
                                <a class="dropdown-item py-2 text-danger fw-bold" href="<?php echo $base_url; ?>logout.php">
                                    <i class="bi bi-box-arrow-right me-2"></i> <?php echo $lang['logout']; ?>
                                </a>
                            </li>
                        </ul>
                    </li>

                <?php else: ?>
                    <li class="nav-item"><a class="nav-link fw-bold text-dark" href="<?php echo $base_url; ?>index.php"><?php echo $lang['home']; ?></a></li>
                    <li class="nav-item"><a class="nav-link fw-bold text-dark" href="<?php echo $base_url; ?>login.php"><?php echo $lang['login']; ?></a></li>
                    <li class="nav-item ms-2">
                        <a class="btn btn-primary rounded-pill px-4 fw-bold" href="<?php echo $base_url; ?>register.php">
                            <?php echo $lang['register']; ?>
                        </a>
                    </li>
                <?php endif; ?>

            </ul>
        </div>
    </div>
</nav>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
    dropdownElementList.map(el => new bootstrap.Dropdown(el));
});
</script>