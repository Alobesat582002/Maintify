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

<div class="container mt-5" style="min-height: 70vh;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Track Active Orders</h2>
        <a href="dashboard.php" class="btn btn-outline-secondary">← Back to Dashboard</a>
    </div>

    <div class="row g-4">
        <?php if (count($orders) > 0): ?>
            <?php foreach ($orders as $order): ?>
                <div class="col-md-6">
                    <div class="card shadow-sm border-0 h-100 <?php echo $order['job_status'] == 'completed' ? 'bg-light' : 'border-start border-warning border-4'; ?>">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="fw-bold mb-0 text-primary"><?php echo htmlspecialchars($order['title']); ?></h5>
                                <h4 class="text-success mb-0">$<?php echo number_format($order['price'], 2); ?></h4>
                            </div>
                            
                            <div class="bg-white p-3 rounded border mb-3">
                                <h6 class="fw-bold mb-2 border-bottom pb-2">Technician Details</h6>
                                <p class="mb-1"><i class="bi bi-person-badge me-2 text-muted"></i> <?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></p>
                                <p class="mb-0"><i class="bi bi-telephone me-2 text-muted"></i> <?php echo htmlspecialchars($order['phone'] ?? 'Not provided'); ?></p>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-4">
                                <div>
                                    <span class="text-muted small d-block mb-1">Status:</span>
                                    <?php if ($order['job_status'] == 'in-progress'): ?>
                                        <span class="badge bg-warning text-dark px-3 py-2">In Progress</span>
                                    <?php else: ?>
                                        <span class="badge bg-success px-3 py-2">Completed</span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="d-flex gap-2">
                                    <a href="../chat.php?user_id=<?php echo $order['tech_id']; ?>" class="btn btn-outline-primary"><i class="bi bi-chat-dots"></i> Chat</a>
                                    
                                    <?php if ($order['job_status'] == 'completed'): ?>
                                        <a href="rate_technician.php?job_id=<?php echo $order['job_id']; ?>&tech_id=<?php echo $order['tech_id']; ?>" class="btn btn-success fw-bold">Rate Technician</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <i class="bi bi-clock-history fs-1 text-muted d-block mb-3"></i>
                <h4 class="text-muted">No active orders yet</h4>
                <p>When you accept a technician's bid, you can track it here.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>