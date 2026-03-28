<?php
$base_url = '/Maintify/';

// فحص ذكي: هل نحن في الصفحة الرئيسية؟
$current_page = basename($_SERVER['PHP_SELF']);
$is_index = ($current_page == 'index.php');

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

<nav class="navbar navbar-expand-lg maintify-nav <?php echo $is_index ? 'nav-dark fixed-top' : 'bg-white border-bottom shadow-sm'; ?>">
    <div class="container-fluid px-4">

        <a class="navbar-brand d-flex align-items-center brand-hover-glow" href="<?php echo $base_url; ?>index.php" style="text-decoration: none;">
            <img src="<?php echo $base_url; ?>assets/images/logo.png" alt="Maintify" class="nav-logo-img me-2" style="width: 35px;">

            <span class="nav-brand-name fw-bold fs-4" style="font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
                <span style="color: #F36F21; transition: text-shadow 0.3s ease;">Maint</span><span style="color: #3B82F6; transition: text-shadow 0.3s ease;">ify</span>
            </span>
        </a>

        <button class="navbar-toggler border-0 <?php echo $is_index ? 'navbar-dark' : ''; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center gap-3">

                <li class="nav-item icon-btn">
                    <button id="theme-toggle" class="nav-link border-0 bg-transparent w-100 h-100 <?php echo $is_index ? 'text-white' : 'text-dark'; ?>" title="Toggle Theme">
                        <i class="bi bi-moon-fill fs-5" id="theme-icon"></i>
                    </button>
                </li>

                <?php if (isset($_SESSION['user_id'])): ?>

                    <li class="nav-item icon-btn">
                        <a href="<?php echo $base_url; ?>messages.php" class="nav-link position-relative <?php echo $is_index ? 'text-white' : 'text-dark'; ?> fs-5" title="Messages">
                            <i class="bi bi-chat-dots"></i>
                            <?php if ($unread_count > 0): ?>
                                <span class="position-absolute top-25 start-75 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
                                    <?php echo $unread_count; ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    </li>

                    <li class="nav-item dropdown icon-btn">
                        <a class="nav-link position-relative <?php echo $is_index ? 'text-white' : 'text-dark'; ?> fs-5" href="#" id="notifDropdown" role="button" data-bs-toggle="dropdown" title="<?php echo $lang['notifications'] ?? 'Notifications'; ?>">
                            <i class="bi bi-bell"></i>
                            <?php if ($unread_notif_count > 0): ?>
                                <span class="position-absolute top-25 start-75 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
                                    <?php echo $unread_notif_count; ?>
                                </span>
                            <?php endif; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end notif-dropdown" aria-labelledby="notifDropdown">
                            <li class="notif-header d-flex justify-content-between align-items-center">
                                <span class="fw-bold"><?php echo $lang['notifications'] ?? 'Notifications'; ?></span>
                                <?php if ($unread_notif_count > 0): ?>
                                    <span class="badge bg-primary rounded-pill"><?php echo $unread_notif_count; ?> <?php echo $lang['new'] ?? 'New'; ?></span>
                                <?php endif; ?>
                            </li>

                            <?php if (count($recent_notifications) > 0): ?>
                                <?php foreach ($recent_notifications as $notif): ?>
                                    <li>
                                        <a class="notif-item <?php echo $notif['is_read'] ? '' : 'unread'; ?>" 
                                           href="<?php echo $base_url; ?>read_notification.php?id=<?php echo $notif['id']; ?>">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <span class="notif-title">
                                                    <?php if (!$notif['is_read']): ?>
                                                        <i class="bi bi-circle-fill text-primary me-1" style="font-size: 8px;"></i>
                                                    <?php endif; ?>
                                                    <?php echo htmlspecialchars($notif['title']); ?>
                                                </span>
                                                <span class="notif-time">
                                                    <?php echo date('M d, H:i', strtotime($notif['created_at'])); ?>
                                                </span>
                                            </div>
                                            <div class="notif-body text-wrap">
                                                <?php echo htmlspecialchars($notif['message']); ?>
                                            </div>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li class="notif-empty">
                                    <i class="bi bi-bell-slash fs-2 d-block mb-2"></i>
                                    <span><?php echo $lang['no_notifications'] ?? 'No notifications yet.'; ?></span>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </li>

                    <div class="vr d-none d-lg-block mx-1 <?php echo $is_index ? 'text-white' : ''; ?>"></div>

                    <li class="nav-item dropdown user-menu">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" style="padding: 5px 10px; background: <?php echo $is_index ? 'rgba(255,255,255,0.1)' : '#f8fafc'; ?>; border-radius: 50px; border: 1px solid <?php echo $is_index ? 'rgba(255,255,255,0.2)' : '#e5e7eb'; ?>;">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 14px;">
                                <i class="bi bi-person-fill"></i>
                            </div>
                            <span class="fw-bold fs-6 <?php echo $is_index ? 'text-white' : 'text-dark'; ?>"><?php echo htmlspecialchars($_SESSION['name']); ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-4 mt-2" aria-labelledby="userDropdown">
                            <li>
                                <?php
                                $dash_link = $base_url . ($_SESSION['role'] === 'technician' ? 'Technician/dashboard.php' : 'Homeowner/dashboard.php');
                                ?>
                                <a class="dropdown-item py-2 fw-medium" href="<?php echo $dash_link; ?>">
                                    <i class="bi bi-grid me-2 text-muted"></i> Dashboard
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item py-2 fw-medium" href="<?php echo $base_url; ?><?php echo ($_SESSION['role'] === 'technician') ? 'Technician' : 'Homeowner'; ?>/profile.php">
                                    <i class="bi bi-person me-2 text-muted"></i> <?php echo $lang['profile'] ?? 'Profile'; ?>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item py-2 fw-medium" href="<?php echo $base_url; ?>settings.php">
                                    <i class="bi bi-gear me-2 text-muted"></i> <?php echo $lang['settings'] ?? 'Settings'; ?>
                                </a>
                            </li>
                            <li>
                                <hr class="dropdown-divider my-1">
                            </li>
                            <li>
                                <a class="dropdown-item py-2 text-danger fw-bold" href="<?php echo $base_url; ?>logout.php">
                                    <i class="bi bi-box-arrow-right me-2"></i> <?php echo $lang['logout'] ?? 'Logout'; ?>
                                </a>
                            </li>
                        </ul>
                    </li>

                <?php else: ?>
                    <li class="nav-item"><a class="nav-link fw-bold <?php echo $is_index ? 'text-white' : 'text-dark'; ?>" href="<?php echo $base_url; ?>index.php"><?php echo $lang['home'] ?? 'Home'; ?></a></li>
                    <li class="nav-item"><a class="nav-link fw-bold <?php echo $is_index ? 'text-white' : 'text-dark'; ?>" href="<?php echo $base_url; ?>login.php"><?php echo $lang['login'] ?? 'Login'; ?></a></li>
                    <li class="nav-item ms-2">
                        <a class="btn rounded-pill px-4 fw-bold <?php echo $is_index ? 'btn-register-dark' : 'btn-primary'; ?>" href="<?php echo $base_url; ?>register.php">
                            <?php echo $lang['register'] ?? 'Register'; ?>
                        </a>
                    </li>
                <?php endif; ?>

            </ul>
        </div>
    </div>
</nav>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // تفعيل الـ Dropdowns
        var dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
        dropdownElementList.map(el => new bootstrap.Dropdown(el));

        // منطق الدارك مود
        const themeToggleBtn = document.getElementById('theme-toggle');
        const themeIcon = document.getElementById('theme-icon');
        const htmlEl = document.documentElement;

        // التحقق من وجود الزر قبل إضافة الحدث لتجنب الأخطاء
        if (themeToggleBtn && themeIcon) {
            // تحديث الأيقونة عند التحميل حسب الثيم الحالي
            if (htmlEl.getAttribute('data-bs-theme') === 'dark') {
                themeIcon.classList.replace('bi-moon-fill', 'bi-sun-fill');
            }

            themeToggleBtn.addEventListener('click', () => {
                const currentTheme = htmlEl.getAttribute('data-bs-theme');
                const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

                htmlEl.setAttribute('data-bs-theme', newTheme);
                localStorage.setItem('theme', newTheme);

                // تغيير الأيقونة
                if (newTheme === 'dark') {
                    themeIcon.classList.replace('bi-moon-fill', 'bi-sun-fill');
                } else {
                    themeIcon.classList.replace('bi-sun-fill', 'bi-moon-fill');
                }
            });
        }
    });
</script>