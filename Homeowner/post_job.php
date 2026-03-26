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
        // إدخال الطلب في قاعدة البيانات (حالة الطلب الافتراضية 'open')
        $sql = "INSERT INTO job_requests (homeowner_id, category_id, title, description) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$homeowner_id, $category_id, $title, $description])) {
            $success = "Job posted successfully! Technicians will start bidding soon.";
            // تفريغ المتغيرات بعد النجاح لتنظيف الفورم
            $title = $description = '';
        } else {
            $error = "Something went wrong. Please try again.";
        }
    }
}

// جلب الأقسام من قاعدة البيانات لعرضها في القائمة المنسدلة
$stmt = $pdo->query("SELECT id, name, description FROM categories ORDER BY id ASC");
$categories = $stmt->fetchAll();

include_once '../includes/header.php';
include_once '../includes/navbar.php';
?>

<div class="container mt-5" style="min-height: 70vh;">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Post a New Maintenance Job</h2>
                <a href="dashboard.php" class="btn btn-outline-secondary">← Back to Dashboard</a>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <?php if(!empty($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <?php if(!empty($success)): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>

                    <form action="post_job.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Job Title *</label>
                            <input type="text" name="title" class="form-control" placeholder="e.g., Leaking pipe in the kitchen" value="<?php echo htmlspecialchars($title ?? ''); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Category *</label>
                            <select name="category_id" class="form-select" required>
                                <option value="">Select the service category...</option>
                                <?php foreach($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>">
                                        <?php echo htmlspecialchars($cat['name']) . ' - ' . htmlspecialchars($cat['description']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Description *</label>
                            <textarea name="description" class="form-control" rows="5" placeholder="Describe the problem in detail. Mention any specific requirements..." required><?php echo htmlspecialchars($description ?? ''); ?></textarea>
                            <div class="form-text text-muted">The more details you provide, the better bids you will receive.</div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">Post Job Now</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>