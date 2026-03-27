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

<div class="google-wrapper">
    <?php include_once '../includes/user_sidebar.php'; ?>

    <main class="google-content">
        <div class="mb-4">
            <h3 class="fw-bold mb-1"><?php echo $lang['post_new_job']; ?></h3>
        </div>

        <div class="google-card p-4 mx-auto" style="max-width: 800px;">
            <?php if(!empty($error)): ?>
                <div class="alert alert-danger rounded-4"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if(!empty($success)): ?>
                <div class="alert alert-success rounded-4 fw-bold"><i class="bi bi-check-circle-fill me-2"></i> <?php echo $success; ?></div>
            <?php endif; ?>

            <form action="post_job.php" method="POST" onsubmit="return confirm('<?php echo $lang['confirm_post_job']; ?>');">
                <div class="mb-4">
                    <label class="form-label fw-bold text-dark"><?php echo $lang['job_title_label']; ?></label>
                    <input type="text" name="title" class="form-control rounded-4 py-2 bg-light border-0" placeholder="<?php echo $lang['job_title_placeholder']; ?>" value="<?php echo htmlspecialchars($title ?? ''); ?>" required>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold text-dark"><?php echo $lang['category_label']; ?></label>
                    <select name="category_id" class="form-select rounded-4 py-2 bg-light border-0" required>
                        <option value=""><?php echo $lang['select_category']; ?></option>
                        <?php foreach($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>">
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold text-dark"><?php echo $lang['description_label']; ?></label>
                    <textarea name="description" class="form-control rounded-4 py-2 bg-light border-0" rows="5" placeholder="<?php echo $lang['description_placeholder']; ?>" required><?php echo htmlspecialchars($description ?? ''); ?></textarea>
                    <div class="form-text text-muted mt-2"><i class="bi bi-info-circle me-1"></i> <?php echo $lang['description_hint']; ?></div>
                </div>

                <div class="pt-2 border-top mt-4">
                    <button type="submit" class="btn btn-primary w-100 py-3 fw-bold rounded-pill mt-3"><?php echo $lang['post_job_now']; ?></button>
                </div>
            </form>
        </div>
    </main>
</div>

<?php include_once '../includes/footer.php'; ?>