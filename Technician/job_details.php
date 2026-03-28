<?php
require_once '../config/db.php';

// 1. حماية الواجهة
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

// 2. جلب تفاصيل الطلب
$stmt = $pdo->prepare("
    SELECT j.*, c.name as category_name, u.id as owner_id, u.first_name, u.last_name, u.city, u.address, u.profile_image 
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

// 3. معالجة تقديم العرض (Bid)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_bid'])) {
    $price = $_POST['price'];
    $proposal = trim($_POST['proposal']);

    // التأكد أن الفني لم يقدم عرضاً مسبقاً
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

            // توليد إشعار ذكي لصاحب المنزل
            // نجلب لغة صاحب المنزل لو أمكن، وإلا نرسل الإشعار بصيغة مزدوجة كما اتفقنا سابقاً
            $notif_title = "New Bid! | عرض جديد 🔔";
            $notif_message = "A technician placed a bid of $" . $price . " on: " . $job['title'];
            $notif_link = "Homeowner/view_bids.php?job_id=" . $job_id;
            
            $notif_stmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, link) VALUES (?, ?, ?, ?)");
            $notif_stmt->execute([$job['owner_id'], $notif_title, $notif_message, $notif_link]);
        } else {
            $error = "Something went wrong. Please try again.";
        }
    }
}

// 4. فحص ما إذا كان الفني قد قدم عرضاً سابقاً
$stmt_bid_check = $pdo->prepare("SELECT * FROM bids WHERE job_id = ? AND technician_id = ?");
$stmt_bid_check->execute([$job_id, $technician_id]);
$existing_bid = $stmt_bid_check->fetch();

include_once '../includes/header.php';
include_once '../includes/navbar.php';
?>

<div class="google-wrapper">
    <?php include_once '../includes/user_sidebar.php'; ?>

    <main class="google-content">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="mb-4">
                    <a href="browse_jobs.php" class="btn btn-sm btn-light border rounded-pill mb-3 fw-bold text-muted px-3">
                        <i class="bi bi-arrow-return-left me-1"></i> <?php echo $lang['back_to_browse']; ?>
                    </a>
                    <h2 class="fw-bold mb-2"><?php echo htmlspecialchars($job['title']); ?></h2>
                    <span class="badge bg-primary rounded-pill px-3 py-2"><?php echo htmlspecialchars($job['category_name']); ?></span>
                </div>

                <div class="google-card p-4 mb-4">
                    <h5 class="fw-bold mb-3"><?php echo $lang['job_description']; ?></h5>
                    <p class="text-muted fs-6" style="white-space: pre-wrap; line-height: 1.7;"><?php echo htmlspecialchars($job['description']); ?></p>
                    <hr class="my-4 text-muted">
                    <div class="row text-muted small g-3">
                        <div class="col-md-4 d-flex align-items-center"><i class="bi bi-geo-alt-fill text-danger me-2 fs-5"></i> <?php echo $lang['location_label']; ?> <span class="fw-bold text-dark ms-1"><?php echo htmlspecialchars($job['city']); ?></span></div>
                        <div class="col-md-4 d-flex align-items-center"><i class="bi bi-calendar-event-fill text-primary me-2 fs-5"></i> <?php echo $lang['posted_label']; ?> <span class="fw-bold text-dark ms-1" dir="ltr"><?php echo date('M d, Y', strtotime($job['created_at'])); ?></span></div>
                        <div class="col-md-4 d-flex align-items-center"><i class="bi bi-info-circle-fill text-info me-2 fs-5"></i> <?php echo $lang['status_label']; ?> <span class="fw-bold text-dark ms-1"><?php echo ucfirst($job['status']); ?></span></div>
                    </div>
                </div>

                <?php if ($job['status'] == 'open'): ?>
                    <div class="google-card p-4">
                        <h5 class="fw-bold mb-4"><?php echo $lang['send_proposal']; ?></h5>
                        
                        <?php if($error): ?> <div class="alert alert-danger rounded-4 py-2"><?php echo $error; ?></div> <?php endif; ?>
                        <?php if($success): ?> <div class="alert alert-success rounded-4 py-2 fw-bold"><i class="bi bi-check-circle-fill me-2"></i><?php echo $success; ?></div> <?php endif; ?>

                        <?php if (!$existing_bid): ?>
                            <form action="job_details.php?id=<?php echo $job['id']; ?>" method="POST">
                                <div class="mb-4">
                                    <label class="form-label fw-bold text-dark"><?php echo $lang['your_price']; ?></label>
                                    <input type="number" name="price" class="form-control rounded-4 bg-light border-0 py-2" placeholder="<?php echo $lang['price_placeholder']; ?>" required>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label fw-bold text-dark"><?php echo $lang['your_proposal_comment']; ?></label>
                                    <textarea name="proposal" class="form-control rounded-4 bg-light border-0 py-2" rows="5" placeholder="<?php echo $lang['proposal_placeholder']; ?>" required></textarea>
                                </div>
                                <button type="submit" name="submit_bid" class="btn btn-primary w-100 py-3 fw-bold rounded-pill"><?php echo $lang['submit_bid_btn']; ?></button>
                            </form>
                        <?php else: ?>
                            <div class="alert alert-info border-0 rounded-4 mb-0 fw-bold d-flex align-items-center">
                                <i class="bi bi-check-circle-fill fs-4 me-3"></i> 
                                <?php echo $lang['already_bid_msg']; ?> <?php echo $existing_bid['price']; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="col-lg-4">
                <div class="google-card p-4 text-center sticky-top" style="top: 20px; z-index: 1;">
                    <div class="mb-3">
                        <?php 
                            $img_src = (!empty($job['profile_image']) && $job['profile_image'] !== 'default.png') 
                                ? "../assets/images/avatars/" . $job['profile_image'] 
                                : "../assets/images/logo.png";
                        ?>
                        <img src="<?php echo htmlspecialchars($img_src); ?>" alt="Owner Profile" class="rounded-circle object-fit-cover shadow-sm border" style="width: 90px; height: 90px;">
                    </div>
                    <h5 class="fw-bold mb-1 text-dark"><?php echo htmlspecialchars($job['first_name'] . ' ' . $job['last_name']); ?></h5>
                    <p class="text-muted small mb-4 bg-light d-inline-block px-3 py-1 rounded-pill"><?php echo $lang['homeowner_role']; ?></p>
                    
                    <div class="d-grid gap-2">
                        <a href="../chat.php?user_id=<?php echo $job['owner_id']; ?>" class="btn btn-outline-primary fw-bold py-2 rounded-pill">
                            <i class="bi bi-chat-dots me-2"></i> <?php echo $lang['chat_with_owner']; ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php include_once '../includes/footer.php'; ?>