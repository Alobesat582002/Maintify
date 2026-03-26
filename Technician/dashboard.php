<?php
require_once '../config/db.php';

// حماية الواجهة: السماح للفنيين فقط
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'technician') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// إحصائيات سريعة للوحة التحكم
$stats = [
    'available_jobs' => 0,
    'my_bids' => 0,
    'active_orders' => 0
];

// 1. عدد الطلبات المفتوحة (التي يمكنه التقديم عليها)
$stmt = $pdo->query("SELECT COUNT(*) FROM job_requests WHERE status = 'open'");
$stats['available_jobs'] = $stmt->fetchColumn();

// 2. عدد العروض التي قدمها
$stmt = $pdo->prepare("SELECT COUNT(*) FROM bids WHERE technician_id = ?");
$stmt->execute([$user_id]);
$stats['my_bids'] = $stmt->fetchColumn();

// 3. عدد الطلبات قيد التنفيذ (التي فاز بها)
$stmt = $pdo->prepare("SELECT COUNT(*) FROM orders o JOIN bids b ON o.bid_id = b.id WHERE b.technician_id = ? AND o.status = 'in_progress'");
$stmt->execute([$user_id]);
$stats['active_orders'] = $stmt->fetchColumn();

include_once '../includes/header.php';
include_once '../includes/navbar.php';
?>

<div class="container mt-5" style="min-height: 70vh;">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="fw-bold">Technician Dashboard</h2>
            <p class="text-muted">Welcome back, <?php echo htmlspecialchars($_SESSION['name']); ?>. Here is your work overview.</p>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white h-100 card-hover">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <h5 class="card-title fw-bold mb-1">Available Jobs</h5>
                        <h1 class="display-4 fw-bold mb-0"><?php echo $stats['available_jobs']; ?></h1>
                    </div>
                    <a href="browse_jobs.php" class="text-white text-decoration-none mt-4 d-inline-block fw-bold">Browse Jobs →</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card bg-warning text-dark h-100 card-hover">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <h5 class="card-title fw-bold mb-1">My Proposals</h5>
                        <h1 class="display-4 fw-bold mb-0"><?php echo $stats['my_bids']; ?></h1>
                    </div>
                    <a href="my_bids.php" class="text-dark text-decoration-none mt-4 d-inline-block fw-bold">View Proposals →</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card bg-success text-white h-100 card-hover">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <h5 class="card-title fw-bold mb-1">Active Orders</h5>
                        <h1 class="display-4 fw-bold mb-0"><?php echo $stats['active_orders']; ?></h1>
                    </div>
                    <a href="active_orders.php" class="text-white text-decoration-none mt-4 d-inline-block fw-bold">Track Orders →</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>