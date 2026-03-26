<?php
require_once '../config/db.php';

// حماية الواجهة: السماح للفنيين فقط
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'technician') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 1. إحصائيات سريعة للوحة التحكم
$stats = [
    'available_jobs' => 0,
    'my_bids' => 0,
    'active_orders' => 0
];

// عدد الطلبات المفتوحة
$stmt = $pdo->query("SELECT COUNT(*) FROM job_requests WHERE status = 'open'");
$stats['available_jobs'] = $stmt->fetchColumn();

// عدد العروض التي قدمها
$stmt = $pdo->prepare("SELECT COUNT(*) FROM bids WHERE technician_id = ?");
$stmt->execute([$user_id]);
$stats['my_bids'] = $stmt->fetchColumn();

// عدد الطلبات قيد التنفيذ (التي فاز بها)
// تم تصحيح الاستعلام ليرتبط بحالة الطلب in-progress
$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM orders o 
    JOIN bids b ON o.bid_id = b.id 
    JOIN job_requests j ON o.job_id = j.id 
    WHERE b.technician_id = ? AND j.status = 'in-progress'
");
$stmt->execute([$user_id]);
$stats['active_orders'] = $stmt->fetchColumn();

// 2. جلب آخر العروض التي قدمها (Recent Proposals)
$stmt = $pdo->prepare("
    SELECT b.id as bid_id, b.price, b.status as bid_status, b.created_at, 
           j.id as job_id, j.title, c.name as category_name
    FROM bids b
    JOIN job_requests j ON b.job_id = j.id
    JOIN categories c ON j.category_id = c.id
    WHERE b.technician_id = ?
    ORDER BY b.created_at DESC
    LIMIT 5
");
$stmt->execute([$user_id]);
$recent_bids = $stmt->fetchAll();

include_once '../includes/header.php';
include_once '../includes/navbar.php';
?>

<style>
    /* تأثيرات حركية وحدود للصناديق متطابقة مع لوحة صاحب المنزل */
    .card-hover {
        transition: all 0.3s ease;
        border: 1px solid #e5e7eb !important;
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
            <h2 class="fw-bold mb-1">Welcome back, <?php echo htmlspecialchars($_SESSION['name']); ?>! 🛠️</h2>
            <p class="text-muted">Here is your work overview and recent proposals.</p>
        </div>
        <a href="browse_jobs.php" class="btn btn-primary fw-bold px-4 py-2 shadow-sm rounded-3">
            <i class="bi bi-search me-2"></i>Find Jobs
        </a>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="card shadow-sm rounded-4 h-100 bg-white card-hover">
                <div class="card-body p-4 d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted fw-bold mb-1 text-uppercase" style="font-size: 12px; letter-spacing: 1px;">Available Jobs</p>
                        <h2 class="fw-bold mb-0 text-primary"><?php echo $stats['available_jobs']; ?></h2>
                        <a href="browse_jobs.php" class="text-primary text-decoration-none small fw-bold mt-2 d-inline-block">Browse now →</a>
                    </div>
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                        <i class="bi bi-briefcase fs-3"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm rounded-4 h-100 bg-white card-hover">
                <div class="card-body p-4 d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted fw-bold mb-1 text-uppercase" style="font-size: 12px; letter-spacing: 1px;">My Proposals</p>
                        <h2 class="fw-bold mb-0 text-warning"><?php echo $stats['my_bids']; ?></h2>
                        <a href="my_bids.php" class="text-warning text-decoration-none small fw-bold mt-2 d-inline-block">View proposals →</a>
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
                        <h2 class="fw-bold mb-0 text-success"><?php echo $stats['active_orders']; ?></h2>
                        <a href="active_orders.php" class="text-success text-decoration-none small fw-bold mt-2 d-inline-block">Track orders →</a>
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
            <h5 class="fw-bold mb-0">Recent Proposals</h5>
            <a href="my_bids.php" class="btn btn-sm btn-outline-secondary rounded-3">View All</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 m-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4 py-3 text-muted small text-uppercase">Job Title</th>
                            <th class="py-3 text-muted small text-uppercase">Category</th>
                            <th class="py-3 text-muted small text-uppercase">Date Submitted</th>
                            <th class="py-3 text-muted small text-uppercase">My Bid</th>
                            <th class="py-3 text-muted small text-uppercase">Status</th>
                            <th class="pe-4 py-3 text-end text-muted small text-uppercase">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($recent_bids) > 0): ?>
                            <?php foreach($recent_bids as $bid): ?>
                                <tr>
                                    <td class="ps-4 py-3 fw-bold text-dark"><?php echo htmlspecialchars($bid['title']); ?></td>
                                    <td class="py-3"><span class="badge bg-light text-secondary border"><?php echo htmlspecialchars($bid['category_name']); ?></span></td>
                                    <td class="py-3 text-muted small"><?php echo date('M d, Y', strtotime($bid['created_at'])); ?></td>
                                    <td class="py-3 fw-bold text-success">$<?php echo number_format($bid['price'], 2); ?></td>
                                    <td class="py-3">
                                        <?php 
                                            $status_class = 'bg-warning text-dark'; // pending
                                            if ($bid['bid_status'] == 'accepted') $status_class = 'bg-success';
                                            if ($bid['bid_status'] == 'rejected') $status_class = 'bg-danger';
                                        ?>
                                        <span class="badge <?php echo $status_class; ?>"><?php echo ucfirst($bid['bid_status']); ?></span>
                                    </td>
                                    <td class="pe-4 py-3 text-end">
                                        <a href="job_details.php?id=<?php echo $bid['job_id']; ?>" class="btn btn-sm btn-outline-primary fw-bold rounded-3">Details</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-file-earmark-x fs-1 d-block mb-3 text-light-subtle"></i>
                                    You haven't submitted any proposals yet.
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