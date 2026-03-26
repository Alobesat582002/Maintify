<?php
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'homeowner') {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: my_jobs.php");
    exit();
}

$job_id = $_GET['id'];
$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// معالجة عملية "قبول العرض"
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['accept_bid_id'])) {
    $bid_id = $_POST['accept_bid_id'];
    
    try {
        $pdo->beginTransaction();
        
        // 1. قبول العرض المحدد
        $stmt = $pdo->prepare("UPDATE bids SET status = 'accepted' WHERE id = ?");
        $stmt->execute([$bid_id]);
        
        // 2. رفض باقي العروض لنفس الطلب
        $stmt = $pdo->prepare("UPDATE bids SET status = 'rejected' WHERE job_id = ? AND id != ?");
        $stmt->execute([$job_id, $bid_id]);
        
        // 3. تغيير حالة الطلب إلى قيد التنفيذ
        $stmt = $pdo->prepare("UPDATE job_requests SET status = 'in-progress' WHERE id = ?");
        $stmt->execute([$job_id]);
        
        // 4. إنشاء الطلب التنفيذي في جدول orders
        $stmt = $pdo->prepare("INSERT INTO orders (job_id, bid_id) VALUES (?, ?)");
        $stmt->execute([$job_id, $bid_id]);
        
        $pdo->commit();
        $success = "Bid accepted successfully! The job is now in-progress.";
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Failed to accept bid: " . $e->getMessage();
    }
}

// جلب تفاصيل الطلب والتأكد أنه يخص هذا المستخدم
$stmt = $pdo->prepare("SELECT j.*, c.name as category_name FROM job_requests j JOIN categories c ON j.category_id = c.id WHERE j.id = ? AND j.homeowner_id = ?");
$stmt->execute([$job_id, $user_id]);
$job = $stmt->fetch();

if (!$job) {
    die("Job not found or unauthorized access.");
}

// جلب العروض المقدمة من الفنيين لهذا الطلب
$stmt = $pdo->prepare("
    SELECT b.*, u.first_name, u.last_name, u.phone, t.experience_years 
    FROM bids b 
    JOIN users u ON b.technician_id = u.id 
    LEFT JOIN technician_profiles t ON u.id = t.user_id 
    WHERE b.job_id = ? 
    ORDER BY b.created_at DESC
");
$stmt->execute([$job_id]);
$bids = $stmt->fetchAll();

include_once '../includes/header.php';
include_once '../includes/navbar.php';
?>

<div class="container mt-5" style="min-height: 70vh;">
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <h2>Job Details</h2>
        <a href="my_jobs.php" class="btn btn-outline-secondary">← Back to My Jobs</a>
    </div>

    <?php if(!empty($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    <?php if(!empty($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <div class="card shadow-sm mb-5 border-0">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <h3 class="card-title fw-bold text-primary"><?php echo htmlspecialchars($job['title']); ?></h3>
                <span class="badge bg-secondary fs-6"><?php echo htmlspecialchars($job['category_name']); ?></span>
            </div>
            <p class="card-text text-muted" style="white-space: pre-wrap;"><?php echo htmlspecialchars($job['description']); ?></p>
            <hr>
            <div class="d-flex justify-content-between text-muted small">
                <span><strong>Status:</strong> <?php echo ucfirst($job['status']); ?></span>
                <span><strong>Posted on:</strong> <?php echo date('F d, Y h:i A', strtotime($job['created_at'])); ?></span>
            </div>
        </div>
    </div>

    <h4 class="mb-3">Bids Received (<?php echo count($bids); ?>)</h4>
    
    <?php if (count($bids) > 0): ?>
        <div class="row g-4">
            <?php foreach ($bids as $bid): ?>
                <div class="col-md-6">
                    <div class="card shadow-sm h-100 <?php echo $bid['status'] == 'accepted' ? 'border-success border-2' : ''; ?>">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="fw-bold mb-0"><?php echo htmlspecialchars($bid['first_name'] . ' ' . $bid['last_name']); ?></h5>
                                <h4 class="text-success mb-0">$<?php echo htmlspecialchars($bid['price']); ?></h4>
                            </div>
                            <p class="text-muted small mb-2"><strong>Experience:</strong> <?php echo htmlspecialchars($bid['experience_years']); ?> years</p>
                            <p class="card-text">"<?php echo htmlspecialchars($bid['proposal']); ?>"</p>
                            
                            <?php if ($job['status'] == 'open' && $bid['status'] == 'pending'): ?>
                                <form action="view_job.php?id=<?php echo $job['id']; ?>" method="POST" class="mt-3">
                                    <input type="hidden" name="accept_bid_id" value="<?php echo $bid['id']; ?>">
                                    <button type="submit" class="btn btn-success w-100 fw-bold" onclick="return confirm('Are you sure you want to accept this bid and hire this technician?');">Accept Bid</button>
                                </form>
                            <?php elseif ($bid['status'] == 'accepted'): ?>
                                <div class="alert alert-success mt-3 mb-0 text-center py-2 fw-bold">Accepted! Contact: <?php echo htmlspecialchars($bid['phone']); ?></div>
                            <?php elseif ($bid['status'] == 'rejected'): ?>
                                <div class="alert alert-secondary mt-3 mb-0 text-center py-2">Rejected</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-light border text-center text-muted py-4">
            No bids received yet. Technicians will see your job soon.
        </div>
    <?php endif; ?>
</div>

<?php include_once '../includes/footer.php'; ?>