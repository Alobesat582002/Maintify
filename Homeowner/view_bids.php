<?php
require_once '../config/db.php';

// حماية الصفحة
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'homeowner') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$job_id = $_GET['job_id'] ?? null;

if (!$job_id) {
    die("Job ID is required.");
}

$error = '';
$success = '';

// 1. معالجة قبول العرض (Accept Bid)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['accept_bid'])) {
    $bid_id = $_POST['bid_id'];

    try {
        $pdo->beginTransaction();

        // أ. تحديث حالة العرض المقبول
        $stmt = $pdo->prepare("UPDATE bids SET status = 'accepted' WHERE id = ?");
        $stmt->execute([$bid_id]);

        // ب. رفض باقي العروض المقدمة على نفس الطلب
        $stmt = $pdo->prepare("UPDATE bids SET status = 'rejected' WHERE job_id = ? AND id != ?");
        $stmt->execute([$job_id, $bid_id]);

        // ج. تحديث حالة الطلب إلى (قيد التنفيذ)
        $stmt = $pdo->prepare("UPDATE job_requests SET status = 'in-progress' WHERE id = ?");
        $stmt->execute([$job_id]);

        // د. إنشاء سجل في جدول الطلبات التنفيذية (orders)
        $stmt = $pdo->prepare("INSERT INTO orders (job_id, bid_id) VALUES (?, ?)");
        $stmt->execute([$job_id, $bid_id]);

        $pdo->commit();
        $success = "Bid accepted successfully! The job is now in progress.";
        // جلب رقم الفني لإرسال الإشعار
        $tech_stmt = $pdo->prepare("SELECT technician_id FROM bids WHERE id = ?");
        $tech_stmt->execute([$bid_id]);
        $tech_id = $tech_stmt->fetchColumn();

        // توليد إشعار للفني
        $notif_title = "Bid Accepted!";
        $notif_message = "Congratulations! Your bid has been accepted. Check your active orders.";
        $notif_link = "Technician/active_orders.php";
        $notif_stmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, link) VALUES (?, ?, ?, ?)");
        $notif_stmt->execute([$tech_id, $notif_title, $notif_message, $notif_link]);
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Failed to accept bid: " . $e->getMessage();
    }
}


$stmt = $pdo->prepare("SELECT * FROM job_requests WHERE id = ? AND homeowner_id = ?");
$stmt->execute([$job_id, $user_id]);
$job = $stmt->fetch();

if (!$job) {
    die("Job not found or unauthorized access.");
}

$stmt_bids = $pdo->prepare("
    SELECT b.*, u.first_name, u.last_name, u.profile_image,
           (SELECT AVG(rating) FROM reviews WHERE technician_id = u.id) as avg_rating,
           (SELECT COUNT(id) FROM reviews WHERE technician_id = u.id) as reviews_count
    FROM bids b
    JOIN users u ON b.technician_id = u.id
    WHERE b.job_id = ?
    ORDER BY b.price ASC
");
$stmt_bids->execute([$job_id]);
$bids = $stmt_bids->fetchAll();

include_once '../includes/header.php';
include_once '../includes/navbar.php';
?>

<div class="container mt-5" style="min-height: 70vh;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="dashboard.php" class="btn btn-sm btn-outline-secondary mb-2">← Back to Dashboard</a>
            <h2 class="fw-bold mb-0"><?php echo htmlspecialchars($job['title']); ?></h2>
            <p class="text-muted mt-1">Status: 
                <span class="badge <?php echo ($job['status'] == 'open') ? 'bg-primary' : 'bg-warning text-dark'; ?>">
                    <?php echo ucfirst($job['status']); ?>
                </span>
            </p>
        </div>
    </div>

    <?php if($error): ?> <div class="alert alert-danger rounded-3"><?php echo $error; ?></div> <?php endif; ?>
    <?php if($success): ?> <div class="alert alert-success rounded-3 fw-bold"><i class="bi bi-check-circle-fill me-2"></i><?php echo $success; ?></div> <?php endif; ?>

    <h5 class="fw-bold mb-3">Received Bids (<?php echo count($bids); ?>)</h5>

    <div class="row g-4">
        <?php if (count($bids) > 0): ?>
            <?php foreach ($bids as $bid): ?>
                <div class="col-12">
                    <div class="card shadow-sm border-0 rounded-4 <?php echo ($bid['status'] == 'accepted') ? 'border border-success border-2' : ''; ?>">
                        <div class="card-body p-4">
                            <div class="row align-items-center">
                                <div class="col-md-4 d-flex align-items-center mb-3 mb-md-0">
                                    <?php 
                                        $img_src = (!empty($bid['profile_image']) && $bid['profile_image'] !== 'default.png') 
                                            ? "../assets/images/avatars/" . $bid['profile_image'] 
                                            : "../assets/images/logo.png";
                                    ?>
                                    <img src="<?php echo htmlspecialchars($img_src); ?>" class="rounded-circle object-fit-cover shadow-sm border me-3" style="width: 70px; height: 70px;">
                                    <div>
                                        <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($bid['first_name'] . ' ' . $bid['last_name']); ?></h5>
                                        <div class="text-warning small">
                                            <?php $rating = $bid['avg_rating'] ? number_format($bid['avg_rating'], 1) : 'New'; ?>
                                            <i class="bi bi-star-fill"></i> <?php echo $rating; ?> 
                                            <span class="text-muted">(<?php echo $bid['reviews_count']; ?> reviews)</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-5 mb-3 mb-md-0">
                                    <h4 class="text-success fw-bold mb-2">$<?php echo number_format($bid['price'], 2); ?></h4>
                                    <p class="text-muted small mb-0">"<?php echo htmlspecialchars($bid['proposal']); ?>"</p>
                                </div>

                                <div class="col-md-3 text-md-end">
                                    <?php if ($bid['status'] == 'accepted'): ?>
                                        <span class="badge bg-success fs-6 py-2 px-3 w-100"><i class="bi bi-check-circle me-1"></i> Accepted</span>
                                    <?php elseif ($job['status'] == 'open'): ?>
                                        <form action="view_bids.php?job_id=<?php echo $job_id; ?>" method="POST" class="d-grid gap-2">
                                            <input type="hidden" name="bid_id" value="<?php echo $bid['id']; ?>">
                                            <button type="submit" name="accept_bid" class="btn btn-success fw-bold" onclick="return confirm('Are you sure you want to accept this bid? This will reject all other bids.');">Accept Bid</button>
                                        </form>
                                    <?php elseif ($bid['status'] == 'rejected'): ?>
                                        <span class="badge bg-light text-muted border w-100 py-2">Rejected</span>
                                    <?php endif; ?>
                                    
                                    <div class="d-flex justify-content-md-end gap-2 mt-2">
                                        <a href="technician_profile.php?id=<?php echo $bid['technician_id']; ?>" class="btn btn-sm btn-outline-primary">Profile</a>
                                        <a href="../chat.php?user_id=<?php echo $bid['technician_id']; ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-chat-dots"></i> Chat</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <i class="bi bi-hourglass-split fs-1 text-muted d-block mb-3"></i>
                <h4 class="text-muted">No bids yet</h4>
                <p>Waiting for technicians to submit their proposals.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>