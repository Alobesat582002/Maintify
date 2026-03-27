<style>
    body {
        background-color: #f4f7f6; /* لون خلفية مريح للداشبورد */
    }
    .admin-wrapper {
        display: flex;
        min-height: 100vh;
    }
    .admin-sidebar {
        width: 260px;
        background-color: #1e1e2d; 
        color: #fff;
        flex-shrink: 0;
        display: flex;
        flex-direction: column;
    }
    .admin-sidebar .brand {
        padding: 20px;
        font-size: 1.5rem;
        font-weight: bold;
        text-align: center;
        border-bottom: 1px solid rgba(255,255,255,0.1);
        margin-bottom: 20px;
    }
    .admin-sidebar .brand span {
        color: #F36F21; 
    }
    .admin-sidebar a {
        color: #a2a3b7;
        text-decoration: none;
        padding: 12px 25px;
        display: flex;
        align-items: center;
        font-weight: 500;
        transition: 0.3s;
    }
    .admin-sidebar a i {
        margin-right: 15px;
        font-size: 1.2rem;
    }
    .admin-sidebar a:hover, .admin-sidebar a.active {
        color: #fff;
        background-color: #1b1b28;
        border-left: 4px solid #F36F21;
    }
    .admin-content {
        flex-grow: 1;
        width: calc(100% - 260px);
        display: flex;
        flex-direction: column;
    }
    
    .maintify-nav, footer {
        display: none !important;
    }
</style>

<div class="admin-sidebar">
    <div class="brand">
        Maintify <span>Admin</span>
    </div>
    
    <nav class="flex-grow-1">
        <?php $current_page = basename($_SERVER['PHP_SELF']); ?>
        
        <a href="dashboard.php" class="<?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
        <a href="manage_users.php" class="<?php echo $current_page == 'manage_users.php' ? 'active' : ''; ?>">
            <i class="bi bi-people"></i> Manage Users
        </a>
        <a href="manage_categories.php" class="<?php echo $current_page == 'manage_categories.php' ? 'active' : ''; ?>">
            <i class="bi bi-grid"></i> Categories
        </a>
        <a href="manage_jobs.php" class="<?php echo $current_page == 'manage_jobs.php' ? 'active' : ''; ?>">
            <i class="bi bi-tools"></i> Jobs & Orders
        </a>
        <a href="manage_complaints.php" class="<?php echo $current_page == 'manage_complaints.php' ? 'active' : ''; ?>">
            <i class="bi bi-exclamation-triangle"></i> Complaints
        </a>
    </nav>

    <div class="mt-auto border-top" style="border-color: rgba(255,255,255,0.1) !important;">
        <a href="../index.php">
            <i class="bi bi-house"></i> Back to Site
        </a>
        <a href="../logout.php" class="text-danger mb-3">
            <i class="bi bi-box-arrow-right"></i> Logout
        </a>
    </div>
</div>