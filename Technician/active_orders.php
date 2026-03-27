<?php
require_once '../config/db.php';

// حماية الواجهة
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'technician') {
    header("Location: ../login.php");
    exit();
}

$technician_id = $_SESSION['user_id'];
$success = '';

// معالجة تحديث حالة الطلب إلى مكتمل
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['complete_job_id'])) {
    $job_id = $_POST['complete_job_id'];
    
    $stmt = $pdo->prepare("UPDATE job_requests SET status = 'completed' WHERE id = ?");
    if ($stmt->execute([$job_id])) {
        // نترك رسالة النجاح ثابتة أو يمكن ترجمتها لاحقاً إن أردت
        $success = "Job marked as completed successfully!";
    }
}

// جلب الطلبات النشطة للفني
$sql = "SELECT o.id as order_id, j.id as job_id, j.title, j.description, j.status as job_status, 
               b.price, u.id as owner_id, u.first_name, u.last_name, u.phone, u.city, u.address, o.created_at
        FROM orders o
        JOIN job_requests j ON o.job_id = j.id
        JOIN bids b ON o.bid_id = b.id
        JOIN users u ON j.homeowner_id = u.id
        WHERE b.technician_id = ?
        ORDER BY o.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$technician_id]);
$orders = $stmt->fetchAll();

include_once '../includes/header.php';
include_once '../includes/navbar.php';
?>

<div class="google-wrapper">
    <?php include_once '../includes/user_sidebar.php'; ?>

    <main class="google-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold mb-1"><?php echo $lang['my_active_orders']; ?></h3>
        </div>

        <?php if(!empty($success)): ?>
            <div class="alert alert-success rounded-pill fw-bold px-4 shadow-sm border-0"><i class="bi bi-check-circle-fill me-2"></i><?php echo $success; ?></div>
        <?php endif; ?>

        <div class="row g-4">
            <?php if (count($orders) > 0): ?>
                <?php foreach ($orders as $order): ?>
                    <div class="col-md-6">
                        <div class="google-card p-4 h-100 <?php echo $order['job_status'] == 'completed' ? 'bg-light' : ''; ?>" 
                             style="<?php echo $order['job_status'] == 'completed' ? 'opacity: 0.8;' : 'border-inline-start: 5px solid #10b981;'; ?>">
                            
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="fw-bold mb-0 text-primary"><?php echo htmlspecialchars($order['title']); ?></h5>
                                <h4 class="text-success fw-bold mb-0" dir="ltr">$<?php echo number_format($order['price'], 2); ?></h4>
                            </div>
                            
                            <div class="bg-white p-3 rounded-4 border mb-3 shadow-sm">
                                <h6 class="fw-bold mb-2 border-bottom pb-2"><?php echo $lang['customer_details']; ?></h6>
                                <p class="mb-1 text-dark"><i class="bi bi-person text-muted me-2"></i> <?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></p>
                                <p class="mb-1 text-dark"><i class="bi bi-telephone text-muted me-2"></i> <span dir="ltr"><?php echo htmlspecialchars($order['phone'] ?? $lang['not_provided']); ?></span></p>
                                <p class="mb-0 text-dark"><i class="bi bi-geo-alt text-muted me-2"></i> <?php echo htmlspecialchars($order['address'] . ', ' . $order['city']); ?></p>
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
                                    <a href="../chat.php?user_id=<?php echo $order['owner_id']; ?>" class="btn btn-outline-primary rounded-pill fw-bold" title="<?php echo $lang['message_now']; ?>">
                                        <i class="bi bi-chat-dots"></i>
                                    </a>
                                    
                                    <?php if ($order['job_status'] == 'in-progress'): ?>
                                        <form action="active_orders.php" method="POST" class="m-0">
                                            <input type="hidden" name="complete_job_id" value="<?php echo $order['job_id']; ?>">
                                            <button type="submit" class="btn btn-success rounded-pill fw-bold" onclick="return confirm('<?php echo $lang['confirm_completed']; ?>');">
                                                <?php echo $lang['mark_completed']; ?>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <i class="bi bi-tools fs-1 text-light-subtle d-block mb-3"></i>
                    <h4 class="fw-bold text-muted"><?php echo $lang['no_active_orders']; ?></h4>
                    <p class="text-muted mb-4"><?php echo $lang['submit_bids_hint']; ?></p>
                    <a href="browse_jobs.php" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm"><?php echo $lang['find_jobs']; ?></a>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php include_once '../includes/footer.php'; ?>