<?php
require_once '../config/db.php';

// حماية الصفحة
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'homeowner') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 1. إحصائيات سريعة للوحة التحكم
$stmt = $pdo->prepare("SELECT COUNT(*) FROM job_requests WHERE homeowner_id = ? AND status = 'open'");
$stmt->execute([$user_id]);
$open_jobs = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM job_requests WHERE homeowner_id = ? AND status = 'in-progress'");
$stmt->execute([$user_id]);
$in_progress_jobs = $stmt->fetchColumn();

$stmt = $pdo->prepare("
    SELECT COUNT(b.id) 
    FROM bids b 
    JOIN job_requests j ON b.job_id = j.id 
    WHERE j.homeowner_id = ? AND j.status = 'open'
");
$stmt->execute([$user_id]);
$total_bids_received = $stmt->fetchColumn();

// 2. جلب آخر 5 طلبات أضافها (Recent Activity)
$stmt = $pdo->prepare("
    SELECT j.id, j.title, j.status, j.created_at, c.name as category_name,
           (SELECT COUNT(*) FROM bids WHERE job_id = j.id) as bids_count
    FROM job_requests j
    JOIN categories c ON j.category_id = c.id
    WHERE j.homeowner_id = ?
    ORDER BY j.created_at DESC
    LIMIT 5
");
$stmt->execute([$user_id]);
$recent_jobs = $stmt->fetchAll();

include_once '../includes/header.php';
include_once '../includes/navbar.php';
?>

<style>
    /* تأثيرات حركية وحدود للصناديق */
    .card-hover {
        transition: all 0.3s ease;
        border: 1px solid #e5e7eb !important; /* لون حد خفيف وأنيق */
    }
    .card-hover:hover {
        transform: translateY(-6px);
        box-shadow: 0 12px 24px rgba(0,0,0,0.1) !important;
        border-color: #d1d5db !important;
    }
    .table-hover tbody tr {
        transition: background-color 0.2s ease;
    }
    .table-hover tbody tr:hover {
        background-color: #f8fafc;
    }
</style>

<div class="container mt-5 mb-5" style="min-height: 70vh;">
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <div>
            <h2 class="fw-bold mb-1">Welcome back, <?php echo htmlspecialchars($_SESSION['name']); ?>! 👋</h2>
            <p class="text-muted">Here is an overview of your recent home maintenance activity.</p>
        </div>
        <a href="post_job.php" class="btn btn-primary fw-bold px-4 py-2 shadow-sm rounded-3">
            <i class="bi bi-plus-lg me-2"></i>Post New Job
        </a>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="card shadow-sm rounded-4 h-100 bg-white card-hover">
                <div class="card-body p-4 d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted fw-bold mb-1 text-uppercase" style="font-size: 12px; letter-spacing: 1px;">Open Requests</p>
                        <h2 class="fw-bold mb-0 text-primary"><?php echo $open_jobs; ?></h2>
                        <a href="my_jobs.php" class="text-primary text-decoration-none small fw-bold mt-2 d-inline-block">View all →</a>
                    </div>
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                        <i class="bi bi-folder2-open fs-3"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm rounded-4 h-100 bg-white card-hover">
                <div class="card-body p-4 d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted fw-bold mb-1 text-uppercase" style="font-size: 12px; letter-spacing: 1px;">Proposals Received</p>
                        <h2 class="fw-bold mb-0 text-warning"><?php echo $total_bids_received; ?></h2>
                        <a href="my_jobs.php" class="text-warning text-decoration-none small fw-bold mt-2 d-inline-block">Check bids →</a>
                    </div>
                    <div class="bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                        <i class="bi bi-file-earmark-text fs-3"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm rounded-4 h-100 bg-white card-hover">
                <div class="card-body p-4 d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted fw-bold mb-1 text-uppercase" style="font-size: 12px; letter-spacing: 1px;">Active Orders</p>
                        <h2 class="fw-bold mb-0 text-success"><?php echo $in_progress_jobs; ?></h2>
                        <a href="active_orders.php" class="text-success text-decoration-none small fw-bold mt-2 d-inline-block">Track status →</a>
                    </div>
                    <div class="bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                        <i class="bi bi-tools fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border border-light-subtle rounded-4 bg-white">
        <div class="card-header bg-white border-bottom pt-4 pb-3 px-4 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0">Recent Job Posts</h5>
            <a href="my_jobs.php" class="btn btn-sm btn-outline-secondary rounded-3">View All</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 m-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4 py-3 text-muted small text-uppercase">Job Title</th>
                            <th class="py-3 text-muted small text-uppercase">Category</th>
                            <th class="py-3 text-muted small text-uppercase">Date Posted</th>
                            <th class="py-3 text-muted small text-uppercase text-center">Bids</th>
                            <th class="py-3 text-muted small text-uppercase">Status</th>
                            <th class="pe-4 py-3 text-end text-muted small text-uppercase">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($recent_jobs) > 0): ?>
                            <?php foreach($recent_jobs as $job): ?>
                                <tr>
                                    <td class="ps-4 py-3 fw-bold text-dark"><?php echo htmlspecialchars($job['title']); ?></td>
                                    <td class="py-3"><span class="badge bg-light text-secondary border"><?php echo htmlspecialchars($job['category_name']); ?></span></td>
                                    <td class="py-3 text-muted small"><?php echo date('M d, Y', strtotime($job['created_at'])); ?></td>
                                    <td class="py-3 text-center">
                                        <span class="badge bg-primary rounded-pill px-3"><?php echo $job['bids_count']; ?></span>
                                    </td>
                                    <td class="py-3">
                                        <?php 
                                            $status_class = 'bg-primary';
                                            if ($job['status'] == 'in-progress') $status_class = 'bg-warning text-dark';
                                            if ($job['status'] == 'completed') $status_class = 'bg-success';
                                            if ($job['status'] == 'cancelled') $status_class = 'bg-danger';
                                        ?>
                                        <span class="badge <?php echo $status_class; ?>"><?php echo ucfirst($job['status']); ?></span>
                                    </td>
                                    <td class="pe-4 py-3 text-end">
                                        <a href="view_bids.php?job_id=<?php echo $job['id']; ?>" class="btn btn-sm btn-outline-primary fw-bold rounded-3">Manage</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-3 text-light-subtle"></i>
                                    You haven't posted any jobs yet.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>