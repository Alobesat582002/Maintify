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

<div class="google-wrapper">
    <?php include_once '../includes/user_sidebar.php'; ?>

    <main class="google-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1"><?php echo $lang['welcome_back']; ?>, <?php echo htmlspecialchars($_SESSION['name']); ?>! 🛠️</h3>
                <p class="text-muted small"><?php echo $lang['tech_welcome_sub']; ?></p>
            </div>
            <a href="browse_jobs.php" class="btn btn-primary fw-bold px-4 py-2 shadow-sm rounded-pill">
                <i class="bi bi-search me-2"></i><?php echo $lang['find_jobs']; ?>
            </a>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="google-card p-4 d-flex justify-content-between align-items-center h-100">
                    <div>
                        <p class="text-muted fw-bold mb-1 small text-uppercase"><?php echo $lang['available_jobs']; ?></p>
                        <h2 class="fw-bold mb-0 text-primary"><?php echo $stats['available_jobs']; ?></h2>
                        <a href="browse_jobs.php" class="text-primary text-decoration-none small fw-bold mt-2 d-inline-block"><?php echo $lang['browse_now']; ?></a>
                    </div>
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                        <i class="bi bi-briefcase fs-4"></i>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="google-card p-4 d-flex justify-content-between align-items-center h-100">
                    <div>
                        <p class="text-muted fw-bold mb-1 small text-uppercase"><?php echo $lang['my_proposals']; ?></p>
                        <h2 class="fw-bold mb-0 text-warning"><?php echo $stats['my_bids']; ?></h2>
                        <a href="my_bids.php" class="text-warning text-decoration-none small fw-bold mt-2 d-inline-block"><?php echo $lang['view_proposals']; ?></a>
                    </div>
                    <div class="bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                        <i class="bi bi-file-earmark-text fs-4"></i>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="google-card p-4 d-flex justify-content-between align-items-center h-100">
                    <div>
                        <p class="text-muted fw-bold mb-1 small text-uppercase"><?php echo $lang['active_orders']; ?></p>
                        <h2 class="fw-bold mb-0 text-success"><?php echo $stats['active_orders']; ?></h2>
                        <a href="active_orders.php" class="text-success text-decoration-none small fw-bold mt-2 d-inline-block"><?php echo $lang['track_orders']; ?></a>
                    </div>
                    <div class="bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                        <i class="bi bi-tools fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="google-card p-0">
            <div class="p-4 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0"><?php echo $lang['recent_proposals']; ?></h6>
                <a href="my_bids.php" class="btn btn-sm btn-light rounded-pill px-3 fw-bold border"><?php echo $lang['view_all']; ?></a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 m-0 border-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3 text-muted small border-0"><?php echo $lang['job_title']; ?></th>
                            <th class="py-3 text-muted small border-0"><?php echo $lang['category']; ?></th>
                            <th class="py-3 text-muted small border-0"><?php echo $lang['date_submitted']; ?></th>
                            <th class="py-3 text-muted small border-0"><?php echo $lang['my_bid']; ?></th>
                            <th class="py-3 text-muted small border-0"><?php echo $lang['status']; ?></th>
                            <th class="pe-4 py-3 text-end text-muted small border-0"><?php echo $lang['action']; ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($recent_bids) > 0): ?>
                            <?php foreach($recent_bids as $bid): ?>
                                <tr>
                                    <td class="ps-4 py-3 fw-bold text-dark border-light"><?php echo htmlspecialchars($bid['title']); ?></td>
                                    <td class="py-3 border-light"><span class="badge bg-light text-secondary border rounded-pill px-2"><?php echo htmlspecialchars($bid['category_name']); ?></span></td>
                                    <td class="py-3 text-muted small border-light"><?php echo date('M d, Y', strtotime($bid['created_at'])); ?></td>
                                    <td class="py-3 fw-bold text-success border-light" dir="ltr">$<?php echo number_format($bid['price'], 2); ?></td>
                                    <td class="py-3 border-light">
                                        <?php 
                                            $status_class = 'bg-warning text-dark'; // pending
                                            if ($bid['bid_status'] == 'accepted') $status_class = 'bg-success';
                                            if ($bid['bid_status'] == 'rejected') $status_class = 'bg-danger';
                                        ?>
                                        <span class="badge <?php echo $status_class; ?> rounded-pill px-3"><?php echo ucfirst($bid['bid_status']); ?></span>
                                    </td>
                                    <td class="pe-4 py-3 text-end border-light">
                                        <a href="job_details.php?id=<?php echo $bid['job_id']; ?>" class="btn btn-sm btn-outline-primary fw-bold rounded-pill px-3"><?php echo $lang['details']; ?></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted border-0">
                                    <i class="bi bi-file-earmark-x fs-1 d-block mb-3 text-light-subtle"></i>
                                    <?php echo $lang['no_proposals_yet']; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<?php include_once '../includes/footer.php'; ?>