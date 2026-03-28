
<aside class="google-sidebar">
    <nav>
        <?php
        $current_page = basename($_SERVER['PHP_SELF']);
        $role = $_SESSION['role'] ?? '';

        // المتغيرات لضبط المسار بشكل ذكي
        $base_url = '/Maintify/'; // المسار الرئيسي للمشروع
        $role_path = $base_url . ($role === 'technician' ? 'Technician/' : 'Homeowner/');
        ?>

        <?php if ($role === 'homeowner'): ?>

            <a href="<?= $role_path ?>dashboard.php" class="google-nav-link <?= $current_page == 'dashboard.php' ? 'active' : '' ?>">
                <span class="nav-icon-wrap icon-blue"><i class="bi bi-speedometer2"></i></span>
                <?= $lang['dashboard'] ?>
            </a>
            <a href="<?= $role_path ?>feed.php" class="google-nav-link <?= $current_page == 'feed.php' ? 'active' : '' ?>">
                <span class="nav-icon-wrap icon-teal"><i class="bi bi-search"></i></span>
                <?= $lang['find_techs'] ?>
            </a>
            <a href="<?= $role_path ?>my_jobs.php" class="google-nav-link <?= $current_page == 'my_jobs.php' ? 'active' : '' ?>">
                <span class="nav-icon-wrap icon-orange"><i class="bi bi-briefcase"></i></span>
                <?= $lang['requests'] ?>
            </a>
            <a href="<?= $role_path ?>active_orders.php" class="google-nav-link <?= $current_page == 'active_orders.php' ? 'active' : '' ?>">
                <span class="nav-icon-wrap icon-purple"><i class="bi bi-tools"></i></span>
                <?= $lang['active_orders'] ?>
            </a>

            <a href="<?= $role_path ?>complaints.php" class="google-nav-link <?= $current_page == 'complaints.php' ? 'active' : '' ?>">
                <span class="nav-icon-wrap icon-red"><i class="bi bi-headset"></i></span>
                <?= $lang['complaints_suggestions'] ?? 'Complaints & Suggestions' ?>
            </a>

        <?php elseif ($role === 'technician'): ?>

            <a href="<?= $role_path ?>dashboard.php" class="google-nav-link <?= $current_page == 'dashboard.php' ? 'active' : '' ?>">
                <span class="nav-icon-wrap icon-blue"><i class="bi bi-house"></i></span>
                <?= $lang['home'] ?>
            </a>
            <a href="<?= $role_path ?>browse_jobs.php" class="google-nav-link <?= $current_page == 'browse_jobs.php' ? 'active' : '' ?>">
                <span class="nav-icon-wrap icon-orange"><i class="bi bi-briefcase"></i></span>
                <?= $lang['jobs'] ?>
            </a>
            <a href="<?= $role_path ?>my_bids.php" class="google-nav-link <?= $current_page == 'my_bids.php' ? 'active' : '' ?>">
                <span class="nav-icon-wrap icon-teal"><i class="bi bi-file-earmark"></i></span>
                <?= $lang['proposals'] ?>
            </a>
            <a href="<?= $role_path ?>active_orders.php" class="google-nav-link <?= $current_page == 'active_orders.php' ? 'active' : '' ?>">
                <span class="nav-icon-wrap icon-purple"><i class="bi bi-tools"></i></span>
                <?= $lang['active_orders'] ?>
            </a>
            <a href="<?= $role_path ?>complaints.php" class="google-nav-link <?= $current_page == 'complaints.php' ? 'active' : '' ?>">
                <span class="nav-icon-wrap icon-red"><i class="bi bi-headset"></i></span>
                <?= $lang['complaints_suggestions'] ?? 'Complaints & Suggestions' ?>
            </a>

        <?php endif; ?>

        <hr class="my-3" style="border-color:#e5e7eb">

        <a href="<?= $base_url ?>settings.php" class="google-nav-link <?= $current_page == 'settings.php' ? 'active' : '' ?>">
            <span class="nav-icon-wrap icon-gray"><i class="bi bi-gear"></i></span>
            <?= $lang['settings'] ?>
        </a>

    </nav>
</aside>