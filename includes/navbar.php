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

<nav class="navbar navbar-expand-lg maintify-nav">
    <div class="container-fluid">

        <a class="navbar-brand" href="<?php echo $base_url; ?>index.php">
            <img src="<?php echo $base_url; ?>assets/images/logo.png" alt="Maintify" class="nav-logo-img me-2">
            <span class="nav-brand-name">Maint<span>ify</span></span>
        </a>

        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center gap-2">

                <?php if (isset($_SESSION['user_id'])): ?>

                    <?php if ($_SESSION['role'] === 'homeowner'): ?>
                        <li class="nav-item">
                            <a class="nav-link fw-bold" href="<?php echo $base_url; ?>Homeowner/dashboard.php">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fw-bold" href="<?php echo $base_url; ?>Homeowner/feed.php">
                                <i class="bi bi-search"></i> Find Techs
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fw-bold" href="<?php echo $base_url; ?>Homeowner/my_jobs.php">
                                <i class="bi bi-briefcase"></i> Requests
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link post-btn" href="<?php echo $base_url; ?>Homeowner/post_job.php">
                                <i class="bi bi-plus-circle"></i> Post Job
                            </a>
                        </li>

                    <?php elseif ($_SESSION['role'] === 'technician'): ?>
                        <li class="nav-item">
                            <a class="nav-link fw-bold" href="<?php echo $base_url; ?>Technician/dashboard.php">
                                <i class="bi bi-house"></i> Home
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fw-bold" href="<?php echo $base_url; ?>Technician/browse_jobs.php">
                                <i class="bi bi-briefcase"></i> Jobs
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fw-bold" href="<?php echo $base_url; ?>Technician/my_bids.php">
                                <i class="bi bi-file-earmark"></i> Proposals
                            </a>
                        </li>
                    <?php endif; ?>

                    <div class="vr d-none d-lg-block mx-2"></div>

                    <!-- Messages -->
                    <li class="nav-item icon-btn">
                        <a href="<?php echo $base_url; ?>messages.php" class="nav-link position-relative" title="Messages">
                            <i class="bi bi-chat-dots"></i>
                            <?php if ($unread_count > 0): ?>
                                <span class="position-absolute top-0 start-100 translate-middle badge">
                                    <?php echo $unread_count; ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    </li>

                    <!-- Notifications -->
                    <li class="nav-item dropdown icon-btn">
                        <a class="nav-link position-relative" href="#" id="notifDropdown" role="button" data-bs-toggle="dropdown" title="Notifications">
                            <i class="bi bi-bell"></i>
                            <?php if ($unread_notif_count > 0): ?>
                                <span class="position-absolute top-0 start-100 translate-middle badge">
                                    <?php echo $unread_notif_count; ?>
                                </span>
                            <?php endif; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notifDropdown">
                            <li class="px-3 py-3 d-flex justify-content-between align-items-center">
                                <span>Notifications</span>
                                <?php if ($unread_notif_count > 0): ?>
                                    <span class="badge bg-primary"><?php echo $unread_notif_count; ?> New</span>
                                <?php endif; ?>
                            </li>

                            <?php if (count($recent_notifications) > 0): ?>
                                <?php foreach ($recent_notifications as $notif): ?>
                                    <li>
                                        <a class="dropdown-item <?php echo $notif['is_read'] ? 'opacity-75' : 'bg-primary bg-opacity-10'; ?>" 
                                           href="<?php echo $base_url; ?>read_notification.php?id=<?php echo $notif['id']; ?>">
                                            <div class="d-flex justify-content-between align-items-start mb-1">
                                                <span class="text-primary fw-bold" style="font-size: 13px;">
                                                    <i class="bi bi-dot <?php echo $notif['is_read'] ? 'text-muted' : 'text-primary'; ?>"></i>
                                                    <?php echo htmlspecialchars($notif['title']); ?>
                                                </span>
                                                <small class="text-muted" style="font-size: 10px;">
                                                    <?php echo date('M d, H:i', strtotime($notif['created_at'])); ?>
                                                </small>
                                            </div>
                                            <div class="text-dark ps-3" style="font-size: 13px; line-height: 1.5;">
                                                <?php echo htmlspecialchars($notif['message']); ?>
                                            </div>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li class="p-4 text-center text-muted">
                                    <i class="bi bi-bell-slash fs-2 d-block mb-2"></i>
                                    <small>No notifications yet.</small>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </li>

                    <div class="vr d-none d-lg-block mx-2"></div>

                    <!-- User Menu -->
                    <li class="nav-item dropdown user-menu">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <div class="bg-light text-primary rounded-circle d-flex align-items-center justify-content-center border" style="width: 35px; height: 35px;">
                                <i class="bi bi-person-fill"></i>
                            </div>
                            <span class="fw-bold"><?php echo htmlspecialchars($_SESSION['name']); ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li>
                                <a class="dropdown-item py-2" href="<?php echo $base_url; ?><?php echo ($_SESSION['role'] === 'technician') ? 'Technician' : 'Homeowner'; ?>/profile.php">
                                    <i class="bi bi-person me-2"></i> Profile
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item py-2" href="<?php echo $base_url; ?>settings.php">
                                    <i class="bi bi-gear me-2"></i> Settings
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item py-2 text-danger fw-bold" href="<?php echo $base_url; ?>logout.php">
                                    <i class="bi bi-box-arrow-right me-2"></i> Logout
                                </a>
                            </li>
                        </ul>
                    </li>

                <?php else: ?>
                    <li class="nav-item"><a class="nav-link fw-bold" href="<?php echo $base_url; ?>index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link fw-bold" href="<?php echo $base_url; ?>login.php">Login</a></li>
                    <li class="nav-item">
                        <a class="nav-link post-btn" href="<?php echo $base_url; ?>register.php">
                            <i class="bi bi-person-plus"></i> Register
                        </a>
                    </li>
                <?php endif; ?>

            </ul>
        </div>
    </div>
</nav>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const navbar = document.querySelector('.maintify-nav');
    window.addEventListener('scroll', () => {
        navbar.classList.toggle('scrolled', window.scrollY > 10);
    });

    var dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
    dropdownElementList.map(el => new bootstrap.Dropdown(el));
});
</script>