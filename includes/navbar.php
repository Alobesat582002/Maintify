<?php
$base_url = '/Maintify/';

// حساب عدد الرسائل غير المقروءة للمستخدم الحالي
$unread_count = 0;
if (isset($_SESSION['user_id']) && isset($pdo)) {
    $stmt_unread = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND is_read = 0");
    $stmt_unread->execute([$_SESSION['user_id']]);
    $unread_count = $stmt_unread->fetchColumn();
}
?>

<nav class="navbar navbar-expand-lg maintify-nav">
    <div class="container-fluid">
        
        <!-- BRAND LOGO & NAME -->
        <a class="navbar-brand" href="<?php echo $base_url; ?>index.php">
            <img src="<?php echo $base_url; ?>assets/images/logo.png" alt="Maintify" class="nav-logo-img">
            <span class="nav-brand-name">Maint<span>ify</span></span>
        </a>

        <!-- MOBILE TOGGLE BUTTON -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- NAVBAR MENU -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">

                <?php if (isset($_SESSION['user_id'])): ?>

                    <!-- HOMEOWNER MENU -->
                    <?php if ($_SESSION['role'] === 'homeowner'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $base_url; ?>Homeowner/dashboard.php">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $base_url; ?>Homeowner/feed.php">
                                <i class="bi bi-search"></i> Find Techs
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $base_url; ?>Homeowner/my_jobs.php">
                                <i class="bi bi-briefcase"></i> Requests
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link post-btn" href="<?php echo $base_url; ?>Homeowner/post_job.php">
                                <i class="bi bi-plus-circle"></i> Post Job
                            </a>
                        </li>

                    <!-- TECHNICIAN MENU -->
                    <?php elseif ($_SESSION['role'] === 'technician'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $base_url; ?>Technician/dashboard.php">
                                <i class="bi bi-house"></i> Home
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $base_url; ?>Technician/browse_jobs.php">
                                <i class="bi bi-briefcase"></i> Jobs
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $base_url; ?>Technician/my_bids.php">
                                <i class="bi bi-file-earmark"></i> Proposals
                            </a>
                        </li>
                    <?php endif; ?>

                    <!-- MESSAGES ICON -->
                    <li class="nav-item icon-btn">
                        <a href="<?php echo $base_url; ?>messages.php" title="Messages">
                            <i class="bi bi-chat-dots"></i>
                            <?php if ($unread_count > 0): ?>
                                <span class="badge"><?php echo $unread_count; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>

                    <!-- NOTIFICATIONS ICON -->
                    <li class="nav-item icon-btn">
                        <button type="button" title="Notifications" data-bs-toggle="offcanvas" data-bs-target="#notificationsOffcanvas">
                            <i class="bi bi-bell"></i>
                        </button>
                    </li>

                    <!-- USER DROPDOWN MENU -->
                    <li class="nav-item user-menu">
                        <a class="dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle"></i>
                            <span><?php echo htmlspecialchars($_SESSION['name']); ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li>
                                <a class="dropdown-item" href="<?php echo $base_url; ?>Homeowner/profile.php">
                                    <i class="bi bi-person"></i> Profile
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="<?php echo $base_url; ?>settings.php">
                                    <i class="bi bi-gear"></i> Settings
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="<?php echo $base_url; ?>logout.php">
                                    <i class="bi bi-box-arrow-right"></i> Logout
                                </a>
                            </li>
                        </ul>
                    </li>

                <!-- NOT LOGGED IN -->
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $base_url; ?>index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $base_url; ?>login.php">Login</a>
                    </li>
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

<!-- SCROLL EFFECT SCRIPT -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const navbar = document.querySelector('.maintify-nav');
    
    window.addEventListener('scroll', function() {
        if (window.scrollY > 10) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });

    // Close mobile menu when link is clicked
    const navLinks = document.querySelectorAll('.navbar-nav a.nav-link');
    const navbarToggler = document.querySelector('.navbar-toggler');
    const navbarCollapse = document.querySelector('.navbar-collapse');

    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (navbarCollapse.classList.contains('show')) {
                navbarToggler.click();
            }
        });
    });
});
</script>