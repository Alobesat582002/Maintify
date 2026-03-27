<?php
require_once '../config/db.php';

// حماية الواجهة: لصاحب المنزل فقط
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'homeowner') {
    header("Location: ../login.php");
    exit();
}

$homeowner_id = $_SESSION['user_id'];
$job_id = $_GET['job_id'] ?? null;
$tech_id = $_GET['tech_id'] ?? null;

if (!$job_id || !$tech_id) {
    die("Invalid request parameters.");
}

$error = '';
$success = '';

// جلب بيانات الفني لعرض اسمه
$stmt_tech = $pdo->prepare("SELECT first_name, last_name FROM users WHERE id = ?");
$stmt_tech->execute([$tech_id]);
$technician = $stmt_tech->fetch();

if (!$technician) {
    die("Technician not found.");
}

// معالجة إرسال التقييم
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_review'])) {
    $rating = (int)$_POST['rating'];
    $comment = trim($_POST['comment']);

    // التحقق مما إذا كان قد تم التقييم مسبقاً لمنع التكرار
    $check_stmt = $pdo->prepare("SELECT id FROM reviews WHERE job_id = ?");
    $check_stmt->execute([$job_id]);

    if ($check_stmt->rowCount() > 0) {
        $error = "You have already reviewed this job.";
    } elseif ($rating < 1 || $rating > 5) {
        $error = "Please select a valid rating between 1 and 5.";
    } elseif (empty($comment)) {
        $error = "Please leave a comment for the technician.";
    } else {
        // إدخال التقييم في قاعدة البيانات
        $sql = "INSERT INTO reviews (job_id, homeowner_id, technician_id, rating, comment) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([$job_id, $homeowner_id, $tech_id, $rating, $comment])) {
            $success = "Thank you! Your review has been submitted successfully.";
        } else {
            $error = "Failed to submit review. Please try again.";
        }
    }
}

// فحص وجود تقييم مسبق لعرض رسالة بدلاً من الفورم
$stmt_check_existing = $pdo->prepare("SELECT rating, comment FROM reviews WHERE job_id = ?");
$stmt_check_existing->execute([$job_id]);
$existing_review = $stmt_check_existing->fetch();

include_once '../includes/header.php';
include_once '../includes/navbar.php';
?>

<div class="google-wrapper">
    <?php include_once '../includes/user_sidebar.php'; ?>

    <main class="google-content">
        <div class="mb-4">
            <h3 class="fw-bold mb-2"><?php echo $lang['rate_technician']; ?></h3>
            <p class="text-muted">
                <?php echo $lang['how_was_experience']; ?> <span class="fw-bold text-primary"><?php echo htmlspecialchars($technician['first_name'] . ' ' . $technician['last_name']); ?></span>?
            </p>
        </div>

        <div class="google-card p-4 mb-5 mx-auto" style="max-width: 600px;">
            <?php if($error): ?> <div class="alert alert-danger rounded-4"><?php echo $error; ?></div> <?php endif; ?>
            <?php if($success): ?> <div class="alert alert-success rounded-4 fw-bold"><i class="bi bi-check-circle-fill me-2"></i><?php echo $success; ?></div> <?php endif; ?>

            <?php if (!$existing_review && !$success): ?>
                <form action="rate_technician.php?job_id=<?php echo $job_id; ?>&tech_id=<?php echo $tech_id; ?>" method="POST">
                    
                    <div class="mb-4 text-center">
                        <label class="form-label fw-bold d-block mb-3"><?php echo $lang['select_rating']; ?></label>
                        <div class="d-flex justify-content-center gap-3" dir="ltr">
                            <?php for($i=1; $i<=5; $i++): ?>
                                <div>
                                    <input type="radio" class="btn-check" name="rating" id="star<?php echo $i; ?>" value="<?php echo $i; ?>" required>
                                    <label class="btn btn-outline-warning fs-4 py-1 px-3 rounded-circle" for="star<?php echo $i; ?>">
                                        <?php echo $i; ?> <i class="bi bi-star-fill"></i>
                                    </label>
                                </div>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold"><?php echo $lang['your_review']; ?></label>
                        <textarea name="comment" class="form-control rounded-4 p-3" rows="4" placeholder="<?php echo $lang['review_placeholder']; ?>" required></textarea>
                    </div>

                    <button type="submit" name="submit_review" class="btn btn-primary w-100 py-2 fw-bold rounded-pill"><?php echo $lang['submit_review']; ?></button>
                </form>
            <?php elseif ($existing_review): ?>
                <div class="text-center py-4">
                    <h5 class="fw-bold mb-3"><?php echo $lang['your_review']; ?></h5>
                    <div class="text-warning fs-3 mb-2">
                        <?php 
                        for ($i=1; $i<=5; $i++) {
                            echo ($i <= $existing_review['rating']) ? '<i class="bi bi-star-fill"></i> ' : '<i class="bi bi-star text-light-subtle"></i> ';
                        }
                        ?>
                    </div>
                    <p class="text-muted fst-italic fs-5 mt-3">"<?php echo htmlspecialchars($existing_review['comment']); ?>"</p>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php include_once '../includes/footer.php'; ?>