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

<nav class="navbar navbar-expand-lg maintify-nav sticky-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="<?php echo $base_url; ?>index.php">
            <img src="<?php echo $base_url; ?>assets/images/logo.png" alt="Maintify" class="nav-logo-img">
            <h1 class="nav-brand-name">Maint<span>ify</span></h1>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center gap-2">

                <?php if (isset($_SESSION['user_id'])): ?>

                    <?php if ($_SESSION['role'] === 'homeowner'): ?>
                        <li class="nav-item"><a class="nav-link" href="<?php echo $base_url; ?>Homeowner/dashboard.php">Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo $base_url; ?>Homeowner/feed.php">Find Technicians</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo $base_url; ?>Homeowner/my_jobs.php">My Requests</a></li>
                        <li class="nav-item"><a class="nav-link btn-nav-register me-2" href="<?php echo $base_url; ?>Homeowner/post_job.php">+ Post a Job</a></li>
                    <?php elseif ($_SESSION['role'] === 'technician'): ?>
                        <li class="nav-item"><a class="nav-link" href="<?php echo $base_url; ?>Technician/dashboard.php">Home</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo $base_url; ?>Technician/browse_jobs.php">Browse Jobs</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo $base_url; ?>Technician/my_bids.php">My Proposals</a></li>
                    <?php endif; ?>

                    <li class="nav-item ms-lg-2">
                        <a class="nav-link position-relative text-dark" href="<?php echo $base_url; ?>messages.php" title="Messages">
                            <i class="bi bi-chat-dots fs-5"></i>
                            <?php if ($unread_count > 0): ?>
                                <span class="position-absolute top-25 start-75 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem; padding: 0.25em 0.5em;">
                                    <?php echo $unread_count; ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    </li>

                    <li class="nav-item me-lg-2">
                        <a class="nav-link position-relative text-dark" href="#" title="Notifications">
                            <i class="bi bi-bell fs-5"></i>
                        </a>
                    </li>

                    <li class="nav-item dropdown ms-2 border-start ps-3">
                        <a class="nav-link dropdown-toggle fw-bold" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" style="color: #4f46e5 !important;">
                            Hi, <?php echo htmlspecialchars($_SESSION['name']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0" aria-labelledby="userDropdown" style="border-radius: 12px;">
                            <li><a class="dropdown-item py-2" href="<?php echo $base_url; ?>Homeowner/profile.php"><i class="bi bi-person me-2"></i>My Profile</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item py-2 text-danger fw-bold" href="<?php echo $base_url; ?>logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                        </ul>
                    </li>

                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="<?php echo $base_url; ?>index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo $base_url; ?>login.php">Login</a></li>
                    <li class="nav-item"><a class="nav-link btn-nav-register px-4" href="<?php echo $base_url; ?>register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>