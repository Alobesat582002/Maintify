<?php
// العودة مجلد للخلف للوصول إلى الإعدادات
require_once '../config/db.php';

// حماية الصفحة: التأكد من أن المستخدم مسجل الدخول وصلاحيته صاحب منزل
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'homeowner') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// إحضار إحصائيات سريعة للوحة التحكم
// 1. الطلبات المفتوحة (التي تنتظر عروض)
$stmt = $pdo->prepare("SELECT COUNT(*) FROM job_requests WHERE homeowner_id = ? AND status = 'open'");
$stmt->execute([$user_id]);
$open_jobs = $stmt->fetchColumn();

// 2. الطلبات قيد التنفيذ (التي تم قبول عرض لها)
$stmt = $pdo->prepare("SELECT COUNT(*) FROM job_requests WHERE homeowner_id = ? AND status = 'in-progress'");
$stmt->execute([$user_id]);
$in_progress_jobs = $stmt->fetchColumn();

include_once '../includes/header.php';
include_once '../includes/navbar.php';
?>
<link rel="stylesheet" href="/Maintify/assets/css/style.css">

<div class="container mt-5" style="min-height: 60vh;">
    <h2>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>!</h2>
    <p class="text-muted">Manage your home maintenance requests from your dashboard.</p>

    <div class="row mt-4 g-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white shadow-sm h-100">
                <div class="card-body d-flex flex-column">
                    <h5>Open Requests</h5>
                    <h2 class="display-4"><?php echo $open_jobs; ?></h2>
                    <a href="my_jobs.php" class="text-white text-decoration-none mt-auto">View Details &rarr;</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card bg-warning text-dark shadow-sm h-100">
                <div class="card-body d-flex flex-column">
                    <h5>In-Progress Jobs</h5>
                    <h2 class="display-4"><?php echo $in_progress_jobs; ?></h2>
                    <a href="active_orders.php" class="text-dark text-decoration-none mt-auto">Track Orders &rarr;</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card bg-success text-white shadow-sm h-100">
                <div class="card-body d-flex flex-column align-items-start">
                    <h5>Need Maintenance?</h5>
                    <p>Post a new job and receive bids from qualified technicians.</p>
                    <a href="post_job.php" class="btn btn-light mt-auto">Post a Job</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>