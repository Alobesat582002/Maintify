<style>
.google-wrapper { 
    display: flex; 
    min-height: calc(100vh - 70px);
}

.google-sidebar { 
    width: 260px; 
    padding: 8px 12px; 
    flex-shrink: 0;
    border-inline-end: 1px solid #e5e7eb; 
}

/* العنوان الصغير فوق المجموعة */
.google-nav-section {
    font-size: 0.78rem;
    font-weight: 600;
    color: #5f6368;
    padding: 12px 16px 4px;
    letter-spacing: 0.03em;
}

.google-nav-link { 
    display: flex; 
    align-items: center; 
    padding: 10px 16px; 
    color: #3c4043; 
    text-decoration: none; 
    border-radius: 50px;
    font-size: 0.9rem;
    font-weight: 500; 
    margin-bottom: 2px; 
    transition: background 0.15s; 
    gap: 14px;
}

.google-nav-link:hover { background: #f1f3f4; color: #3c4043; }

.google-nav-link.active { 
    background: #e8f0fe; 
    color: #1a73e8;
    font-weight: 600;
}

.google-nav-link.active .nav-icon-wrap { opacity: 1; }

/* الأيقونة الملونة بخلفية دائرية */
.nav-icon-wrap {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    flex-shrink: 0;
}

/* ألوان كل أيقونة */
.icon-blue   { background: #e8f0fe; color: #1a73e8; }
.icon-teal   { background: #e6f4ea; color: #1e8e3e; }
.icon-orange { background: #fce8e6; color: #d93025; }
.icon-purple { background: #f3e8fd; color: #9334e6; }
.icon-gray   { background: #f1f3f4; color: #5f6368; }
.icon-red    { background: #fce8e6; color: #d93025; }

.google-nav-link.active .nav-icon-wrap {
    background: #c5d8ff !important;
    color: #1a73e8 !important;
}

.google-content { 
    flex-grow: 1; 
    padding: 30px; 
    max-width: 1000px;
    margin: 0 auto;
}

@media (max-width: 768px) {
    .google-sidebar { display: none; }
    .google-content { padding: 15px; }
}
</style>

<aside class="google-sidebar">
    <nav>
        <?php 
        $current_page = basename($_SERVER['PHP_SELF']); 
        $role = $_SESSION['role'] ?? '';
        ?>

        <?php if ($role === 'homeowner'): ?>

            <a href="dashboard.php" class="google-nav-link <?= $current_page == 'dashboard.php' ? 'active' : '' ?>">
                <span class="nav-icon-wrap icon-blue"><i class="bi bi-speedometer2"></i></span>
                <?= $lang['dashboard'] ?>
            </a>
            <a href="feed.php" class="google-nav-link <?= $current_page == 'feed.php' ? 'active' : '' ?>">
                <span class="nav-icon-wrap icon-teal"><i class="bi bi-search"></i></span>
                <?= $lang['find_techs'] ?>
            </a>
            <a href="my_jobs.php" class="google-nav-link <?= $current_page == 'my_jobs.php' ? 'active' : '' ?>">
                <span class="nav-icon-wrap icon-orange"><i class="bi bi-briefcase"></i></span>
                <?= $lang['requests'] ?>
            </a>
            <a href="active_orders.php" class="google-nav-link <?= $current_page == 'active_orders.php' ? 'active' : '' ?>">
                <span class="nav-icon-wrap icon-purple"><i class="bi bi-tools"></i></span>
                <?= $lang['active_orders'] ?>
            </a>

        <?php elseif ($role === 'technician'): ?>

            <a href="dashboard.php" class="google-nav-link <?= $current_page == 'dashboard.php' ? 'active' : '' ?>">
                <span class="nav-icon-wrap icon-blue"><i class="bi bi-house"></i></span>
                <?= $lang['home'] ?>
            </a>
            <a href="browse_jobs.php" class="google-nav-link <?= $current_page == 'browse_jobs.php' ? 'active' : '' ?>">
                <span class="nav-icon-wrap icon-orange"><i class="bi bi-briefcase"></i></span>
                <?= $lang['jobs'] ?>
            </a>
            <a href="my_bids.php" class="google-nav-link <?= $current_page == 'my_bids.php' ? 'active' : '' ?>">
                <span class="nav-icon-wrap icon-teal"><i class="bi bi-file-earmark"></i></span>
                <?= $lang['proposals'] ?>
            </a>
            <a href="active_orders.php" class="google-nav-link <?= $current_page == 'active_orders.php' ? 'active' : '' ?>">
                <span class="nav-icon-wrap icon-purple"><i class="bi bi-tools"></i></span>
                <?= $lang['active_orders'] ?>
            </a>

        <?php endif; ?>

        <hr class="my-3" style="border-color:#e5e7eb">

        <a href="../settings.php" class="google-nav-link <?= $current_page == 'settings.php' ? 'active' : '' ?>">
            <span class="nav-icon-wrap icon-gray"><i class="bi bi-gear"></i></span>
            <?= $lang['settings'] ?>
        </a>

    </nav>
</aside>