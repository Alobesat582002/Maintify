<?php
require_once '../config/db.php';

// حماية الواجهة
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'homeowner') {
    header("Location: ../login.php");
    exit();
}

$homeowner_id = $_SESSION['user_id'];

// جلب الطلبات قيد التنفيذ والمنتهية المرتبطة بصاحب المنزل
$sql = "SELECT o.id as order_id, j.id as job_id, j.title, j.status as job_status, 
               b.price, u.id as tech_id, u.first_name, u.last_name, u.phone 
        FROM orders o
        JOIN job_requests j ON o.job_id = j.id
        JOIN bids b ON o.bid_id = b.id
        JOIN users u ON b.technician_id = u.id
        WHERE j.homeowner_id = ?
        ORDER BY o.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$homeowner_id]);
$orders = $stmt->fetchAll();

include_once '../includes/header.php';
include_once '../includes/navbar.php';
?>

<div class="google-wrapper">
    <?php include_once '../includes/user_sidebar.php'; ?>

    <main class="google-content">
        <div class="mb-4">
            <h3 class="fw-bold mb-1"><?php echo $lang['track_active_orders']; ?></h3>
        </div>

        <div class="row g-4">
            <?php if (count($orders) > 0): ?>
                <?php foreach ($orders as $order): ?>
                    <div class="col-md-6">
                        <div class="google-card p-4 h-100 <?php echo $order['job_status'] == 'completed' ? 'bg-light' : ''; ?>" 
                             style="<?php echo $order['job_status'] == 'completed' ? 'opacity: 0.8;' : 'border-inline-start: 5px solid #f59e0b;'; ?>">
                            
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="fw-bold mb-0 text-primary"><?php echo htmlspecialchars($order['title']); ?></h5>
                                <h4 class="text-success fw-bold mb-0" dir="ltr">$<?php echo number_format($order['price'], 2); ?></h4>
                            </div>
                            
                            <div class="bg-white p-3 rounded-4 border mb-3 shadow-sm">
                                <h6 class="fw-bold mb-2 border-bottom pb-2"><?php echo $lang['technician_details']; ?></h6>
                                <p class="mb-1 text-dark"><i class="bi bi-person-badge text-muted me-2"></i> <?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></p>
                                <p class="mb-0 text-dark"><i class="bi bi-telephone text-muted me-2"></i> <span dir="ltr"><?php echo htmlspecialchars($order['phone'] ?? $lang['not_provided']); ?></span></p>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-auto pt-2">
                                <div>
                                    <span class="text-muted small d-block mb-1"><?php echo $lang['status']; ?>:</span>
                                    <?php if ($order['job_status'] == 'in-progress'): ?>
                                        <span class="badge bg-warning text-dark px-3 py-2 rounded-pill"><?php echo $lang['in_progress']; ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-success px-3 py-2 rounded-pill"><?php echo $lang['completed']; ?></span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="d-flex gap-2">
                                    <a href="../chat.php?user_id=<?php echo $order['tech_id']; ?>" class="btn btn-outline-primary rounded-pill fw-bold" title="<?php echo $lang['chat']; ?>">
                                        <i class="bi bi-chat-dots"></i>
                                    </a>
                                    
                                    <?php if ($order['job_status'] == 'completed'): ?>
                                        <a href="rate_technician.php?job_id=<?php echo $order['job_id']; ?>&tech_id=<?php echo $order['tech_id']; ?>" class="btn btn-success rounded-pill fw-bold">
                                            <i class="bi bi-star-fill me-1"></i> <?php echo $lang['rate_technician']; ?>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <div class="google-card p-5 mx-auto" style="max-width: 500px;">
                        <i class="bi bi-clock-history fs-1 text-muted d-block mb-3"></i>
                        <h4 class="fw-bold text-dark"><?php echo $lang['no_active_orders']; ?></h4>
                        <p class="text-muted"><?php echo $lang['track_orders_hint']; ?></p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php include_once '../includes/footer.php'; ?>