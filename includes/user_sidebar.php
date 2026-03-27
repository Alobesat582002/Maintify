
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
    /* Google Material Design Style */
    body { background-color: #f8fafc; }
    
    .google-wrapper { 
        display: flex; 
        min-height: calc(100vh - 70px); /* خصم ارتفاع الناف بار */
    }
    
    .google-sidebar { 
        width: 280px; 
        padding: 20px 15px; 
        flex-shrink: 0;
        /* فاصل خفيف يمين أو يسار حسب اللغة */
        border-inline-end: 1px solid #e5e7eb; 
    }
    
    .google-nav-link { 
        display: flex; 
        align-items: center; 
        padding: 12px 24px; 
        color: #3c4043; 
        text-decoration: none; 
        border-radius: 50px; /* شكل الكبسولة */
        font-weight: 500; 
        margin-bottom: 5px; 
        transition: 0.2s; 
    }
    
    .google-nav-link i { 
        margin-inline-end: 16px; 
        font-size: 1.2rem; 
        color: #5f6368;
    }
    
    .google-nav-link:hover { 
        background-color: #f1f3f4; 
    }
    
    .google-nav-link.active { 
        background-color: #e8f0fe; /* أزرق فاتح جوجل */
        color: #1a73e8; 
    }
    
    .google-nav-link.active i {
        color: #1a73e8;
    }
    
    .google-content { 
        flex-grow: 1; 
        padding: 30px; 
        max-width: 1000px;
        margin: 0 auto;
    }
    
    .google-card { 
        background: #fff; 
        border-radius: 24px; 
        border: 1px solid #dadce0; 
        box-shadow: none; 
    }

    @media (max-width: 768px) {
        .google-sidebar { display: none; } /* إخفاء في الشاشات الصغيرة والاعتماد على الناف بار */
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
            <a href="dashboard.php" class="google-nav-link <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
                <i class="bi bi-speedometer2"></i> <?php echo $lang['dashboard']; ?>
            </a>
            <a href="feed.php" class="google-nav-link <?php echo $current_page == 'feed.php' ? 'active' : ''; ?>">
                <i class="bi bi-search"></i> <?php echo $lang['find_techs']; ?>
            </a>
            <a href="my_jobs.php" class="google-nav-link <?php echo $current_page == 'my_jobs.php' ? 'active' : ''; ?>">
                <i class="bi bi-briefcase"></i> <?php echo $lang['requests']; ?>
            </a>
            <a href="active_orders.php" class="google-nav-link <?php echo $current_page == 'active_orders.php' ? 'active' : ''; ?>">
                <i class="bi bi-tools"></i> <?php echo $lang['active_orders']; ?>
            </a>
            
        <?php elseif ($role === 'technician'): ?>
            <a href="dashboard.php" class="google-nav-link <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
                <i class="bi bi-house"></i> <?php echo $lang['home']; ?>
            </a>
            <a href="browse_jobs.php" class="google-nav-link <?php echo $current_page == 'browse_jobs.php' ? 'active' : ''; ?>">
                <i class="bi bi-briefcase"></i> <?php echo $lang['jobs']; ?>
            </a>
            <a href="my_bids.php" class="google-nav-link <?php echo $current_page == 'my_bids.php' ? 'active' : ''; ?>">
                <i class="bi bi-file-earmark"></i> <?php echo $lang['proposals']; ?>
            </a>
            <a href="active_orders.php" class="google-nav-link <?php echo $current_page == 'active_orders.php' ? 'active' : ''; ?>">
                <i class="bi bi-tools"></i> <?php echo $lang['active_orders']; ?>
            </a>
        <?php endif; ?>

        <hr class="my-3 text-muted">
        
        <a href="../settings.php" class="google-nav-link <?php echo $current_page == 'settings.php' ? 'active' : ''; ?>">
            <i class="bi bi-gear"></i> <?php echo $lang['settings']; ?>
        </a>
    </nav>
</aside>