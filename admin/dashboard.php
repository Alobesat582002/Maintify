<?php
require_once '../config/db.php';

// حماية صارمة
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// إحصائيات متقدمة تفصيلية
$tech_count = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'technician'")->fetchColumn();
$home_count = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'homeowner'")->fetchColumn();

$open_jobs = $pdo->query("SELECT COUNT(*) FROM job_requests WHERE status = 'open'")->fetchColumn();
$completed_jobs = $pdo->query("SELECT COUNT(*) FROM job_requests WHERE status = 'completed'")->fetchColumn();

$pending_complaints = $pdo->query("SELECT COUNT(*) FROM complaints WHERE status = 'pending'")->fetchColumn();
$categories_count = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();

// جلب أحدث 5 طلبات للجدول السريع
$recent_jobs = $pdo->query("
    SELECT j.title, j.status, j.created_at, u.first_name, u.last_name 
    FROM job_requests j 
    JOIN users u ON j.homeowner_id = u.id 
    ORDER BY j.created_at DESC LIMIT 5
")->fetchAll();

include_once '../includes/header.php';
// استدعاء الـ navbar العادي هنا (سيتم إخفاؤه تلقائياً بواسطة CSS السايدبار)
include_once '../includes/navbar.php'; 
?>

<div class="admin-wrapper">
    
    <?php include_once 'includes/sidebar.php'; ?>

    <div class="admin-content p-4 p-md-5 w-100">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold">Dashboard Overview</h2>
            <div class="text-muted fw-bold"><?php echo date('l, M d, Y'); ?></div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-md-3">
                <div class="card shadow-sm border-0 bg-white h-100 rounded-4 border-start border-4 border-primary">
                    <div class="card-body p-4">
                        <p class="text-muted mb-1 fw-bold">Total Users</p>
                        <h3 class="fw-bold mb-3"><?php echo ($tech_count + $home_count); ?></h3>
                        <div class="small text-muted">
                            <span class="text-primary fw-bold"><?php echo $tech_count; ?></span> Techs | 
                            <span class="text-info fw-bold"><?php echo $home_count; ?></span> Homeowners
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm border-0 bg-white h-100 rounded-4 border-start border-4 border-success">
                    <div class="card-body p-4">
                        <p class="text-muted mb-1 fw-bold">Jobs Overview</p>
                        <h3 class="fw-bold mb-3"><?php echo ($open_jobs + $completed_jobs); ?></h3>
                        <div class="small text-muted">
                            <span class="text-success fw-bold"><?php echo $open_jobs; ?></span> Open | 
                            <span class="text-secondary fw-bold"><?php echo $completed_jobs; ?></span> Completed
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm border-0 bg-white h-100 rounded-4 border-start border-4 border-danger">
                    <div class="card-body p-4">
                        <p class="text-muted mb-1 fw-bold">Pending Complaints</p>
                        <h3 class="fw-bold mb-3 text-danger"><?php echo $pending_complaints; ?></h3>
                        <a href="manage_complaints.php" class="text-decoration-none small fw-bold text-danger">Review Now →</a>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm border-0 bg-white h-100 rounded-4 border-start border-4 border-warning">
                    <div class="card-body p-4">
                        <p class="text-muted mb-1 fw-bold">Active Categories</p>
                        <h3 class="fw-bold mb-3 text-warning"><?php echo $categories_count; ?></h3>
                        <a href="manage_categories.php" class="text-decoration-none small fw-bold text-warning">Manage →</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                <h5 class="fw-bold mb-0">Recent Job Requests</h5>
            </div>
            <div class="card-body p-0 mt-3">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 py-3">Job Title</th>
                                <th class="py-3">Homeowner</th>
                                <th class="py-3">Status</th>
                                <th class="pe-4 py-3 text-end">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_jobs as $job): ?>
                                <tr>
                                    <td class="ps-4 fw-bold text-dark"><?php echo htmlspecialchars($job['title']); ?></td>
                                    <td><?php echo htmlspecialchars($job['first_name'] . ' ' . $job['last_name']); ?></td>
                                    <td>
                                        <?php 
                                            $badgeClass = 'bg-secondary';
                                            if ($job['status'] == 'open') $badgeClass = 'bg-primary';
                                            if ($job['status'] == 'in_progress') $badgeClass = 'bg-warning text-dark';
                                            if ($job['status'] == 'completed') $badgeClass = 'bg-success';
                                            if ($job['status'] == 'cancelled') $badgeClass = 'bg-danger';
                                        ?>
                                        <span class="badge <?php echo $badgeClass; ?> px-2 py-1">
                                            <?php echo ucfirst(str_replace('_', ' ', $job['status'])); ?>
                                        </span>
                                    </td>
                                    <td class="pe-4 text-end text-muted small"><?php echo date('M d, Y', strtotime($job['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($recent_jobs)): ?>
                                <tr><td colspan="4" class="text-center py-4 text-muted">No recent jobs found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<?php include_once '../includes/footer.php'; ?>