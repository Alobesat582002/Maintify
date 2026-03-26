<?php
require_once '../config/db.php';

// حماية الواجهة
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'technician') {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: browse_jobs.php");
    exit();
}

$job_id = $_GET['id'];
$technician_id = $_SESSION['user_id'];
$error = '';
$success = '';

// 1. معالجة تقديم العرض (Bid)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_bid'])) {
    $price = $_POST['price'];
    $proposal = trim($_POST['proposal']);

    // التأكد أن الفني لم يقدم عرضاً مسبقاً على هذا الطلب
    $check_stmt = $pdo->prepare("SELECT id FROM bids WHERE job_id = ? AND technician_id = ?");
    $check_stmt->execute([$job_id, $technician_id]);
    
    if ($check_stmt->rowCount() > 0) {
        $error = "You have already submitted a bid for this job.";
    } elseif (empty($price) || empty($proposal)) {
        $error = "Please provide both a price and a proposal.";
    } else {
        $sql = "INSERT INTO bids (job_id, technician_id, price, proposal) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([$job_id, $technician_id, $price, $proposal])) {
            $success = "Your bid has been submitted successfully!";
        } else {
            $error = "Something went wrong. Please try again.";
        }
    }
}

// 2. جلب تفاصيل الطلب وبيانات صاحب المنزل
$stmt = $pdo->prepare("
    SELECT j.*, c.name as category_name, u.id as owner_id, u.first_name, u.last_name, u.city, u.address 
    FROM job_requests j 
    JOIN categories c ON j.category_id = c.id 
    JOIN users u ON j.homeowner_id = u.id 
    WHERE j.id = ?
");
$stmt->execute([$job_id]);
$job = $stmt->fetch();

if (!$job) {
    die("Job not found.");
}

// 3. فحص ما إذا كان الفني قد قدم عرضاً سابقاً لعرض رسالة تنبيه
$stmt_bid_check = $pdo->prepare("SELECT * FROM bids WHERE job_id = ? AND technician_id = ?");
$stmt_bid_check->execute([$job_id, $technician_id]);
$existing_bid = $stmt_bid_check->fetch();

include_once '../includes/header.php';
include_once '../includes/navbar.php';
?>

<div class="container mt-5" style="min-height: 70vh;">
    <div class="row">
        <div class="col-lg-8">
            <div class="mb-4">
                <a href="browse_jobs.php" class="btn btn-sm btn-outline-secondary mb-3">← Back to Browse</a>
                <h2 class="fw-bold"><?php echo htmlspecialchars($job['title']); ?></h2>
                <span class="badge bg-primary px-3 py-2"><?php echo htmlspecialchars($job['category_name']); ?></span>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3">Description</h5>
                    <p class="text-muted fs-5" style="white-space: pre-wrap;"><?php echo htmlspecialchars($job['description']); ?></p>
                    <hr class="my-4">
                    <div class="row text-muted small">
                        <div class="col-md-4"><i class="bi bi-geo-alt me-2"></i>Location: <?php echo htmlspecialchars($job['city']); ?></div>
                        <div class="col-md-4"><i class="bi bi-calendar-event me-2"></i>Posted: <?php echo date('M d, Y', strtotime($job['created_at'])); ?></div>
                        <div class="col-md-4"><i class="bi bi-info-circle me-2"></i>Status: <?php echo ucfirst($job['status']); ?></div>
                    </div>
                </div>
            </div>

            <?php if ($job['status'] == 'open'): ?>
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3">Send Your Proposal</h5>
                        
                        <?php if($error): ?> <div class="alert alert-danger"><?php echo $error; ?></div> <?php endif; ?>
                        <?php if($success): ?> <div class="alert alert-success"><?php echo $success; ?></div> <?php endif; ?>

                        <?php if (!$existing_bid): ?>
                            <form action="job_details.php?id=<?php echo $job['id']; ?>" method="POST">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Your Price ($)</label>
                                    <input type="number" name="price" class="form-control" placeholder="Enter your total cost" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Your Proposal / Comment</label>
                                    <textarea name="proposal" class="form-control" rows="4" placeholder="Explain how you will fix the problem and when you can start..." required></textarea>
                                </div>
                                <button type="submit" name="submit_bid" class="btn btn-primary w-100 py-2 fw-bold">Submit Bid</button>
                            </form>
                        <?php else: ?>
                            <div class="alert alert-info mb-0">
                                <i class="bi bi-check-circle-fill me-2"></i> You have already submitted a bid for $<?php echo $existing_bid['price']; ?>.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm border-0 sticky-top" style="top: 100px;">
                <div class="card-body p-4 text-center">
                    <div class="mb-3">
                        <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                            <i class="bi bi-person text-primary fs-1"></i>
                        </div>
                    </div>
                    <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($job['first_name'] . ' ' . $job['last_name']); ?></h5>
                    <p class="text-muted small mb-3">Homeowner</p>
                    
                    <div class="d-grid gap-2">
                        <a href="../chat.php?user_id=<?php echo $job['owner_id']; ?>" class="btn btn-outline-primary fw-bold py-2">
                            <i class="bi bi-chat-dots me-2"></i> Chat with Owner
                        </a>
                        <p class="text-muted x-small mt-2">Ask for more details or photos before bidding.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>