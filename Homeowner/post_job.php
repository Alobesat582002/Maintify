<?php
require_once '../config/db.php';

// حماية الواجهة: السماح لأصحاب المنازل فقط
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'homeowner') {
    header("Location: ../login.php");
    exit();
}

$error = '';
$success = '';

// معالجة البيانات عند الإرسال
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $category_id = $_POST['category_id'];
    $description = trim($_POST['description']);
    $homeowner_id = $_SESSION['user_id'];

    if (empty($title) || empty($category_id) || empty($description)) {
        $error = "Please fill all required fields.";
    } else {
        try {
            $pdo->beginTransaction();

            // 1. إدخال الطلب في قاعدة البيانات
            $sql = "INSERT INTO job_requests (homeowner_id, category_id, title, description) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            
            if ($stmt->execute([$homeowner_id, $category_id, $title, $description])) {
                $new_job_id = $pdo->lastInsertId();

                // 2. جلب كل الفنيين المهتمين بهذا القسم (Smart Notification)
                $techs_stmt = $pdo->prepare("
                    SELECT u.id 
                    FROM users u 
                    JOIN user_interests ui ON u.id = ui.user_id 
                    WHERE u.role = 'technician' AND ui.category_id = ?
                ");
                $techs_stmt->execute([$category_id]);
                $interested_techs = $techs_stmt->fetchAll(PDO::FETCH_COLUMN);

                // 3. إرسال الإشعار لكل فني مهتم
                if (!empty($interested_techs)) {
                    $notif_title = "New Job Alert! 🛠️";
                    $notif_message = "A new job matching your skills has been posted: " . $title;
                    $notif_link = "Technician/job_details.php?id=" . $new_job_id;

                    $notif_insert = $pdo->prepare("INSERT INTO notifications (user_id, title, message, link) VALUES (?, ?, ?, ?)");
                    foreach ($interested_techs as $t_id) {
                        $notif_insert->execute([$t_id, $notif_title, $notif_message, $notif_link]);
                    }
                }

                $pdo->commit();
                $success = "Job posted successfully! Interested technicians have been notified.";
                $title = $description = ''; // تنظيف الحقول
            } else {
                $pdo->rollBack();
                $error = "Something went wrong. Please try again.";
            }
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "System Error: " . $e->getMessage();
        }
    }
}

// جلب الأقسام من قاعدة البيانات
$stmt = $pdo->query("SELECT id, name, description FROM categories ORDER BY id ASC");
$categories = $stmt->fetchAll();

include_once '../includes/header.php';
include_once '../includes/navbar.php';
?>

<div class="container mt-5" style="min-height: 70vh;">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold">Post a New Maintenance Job</h2>
                <a href="dashboard.php" class="btn btn-outline-secondary">← Back to Dashboard</a>
            </div>

            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body p-4">
                    <?php if(!empty($error)): ?>
                        <div class="alert alert-danger rounded-3"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <?php if(!empty($success)): ?>
                        <div class="alert alert-success rounded-3 fw-bold"><?php echo $success; ?></div>
                    <?php endif; ?>

                    <form action="post_job.php" method="POST" onsubmit="return confirm('Are you sure you want to post this job? This will notify relevant technicians.');">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Job Title *</label>
                            <input type="text" name="title" class="form-control rounded-3" placeholder="e.g., Leaking pipe in the kitchen" value="<?php echo htmlspecialchars($title ?? ''); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Category *</label>
                            <select name="category_id" class="form-select rounded-3" required>
                                <option value="">Select the service category...</option>
                                <?php foreach($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>">
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Description *</label>
                            <textarea name="description" class="form-control rounded-3" rows="5" placeholder="Describe the problem in detail..." required><?php echo htmlspecialchars($description ?? ''); ?></textarea>
                            <div class="form-text text-muted">The more details you provide, the better bids you will receive.</div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold rounded-3 shadow-sm">Post Job Now</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>