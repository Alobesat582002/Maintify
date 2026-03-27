<?php
require_once '../config/db.php';

// حماية الواجهة
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'homeowner') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// معالجة طلب الحذف
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    // التحقق من أن الطلب يخص المستخدم نفسه قبل الحذف
    $stmt = $pdo->prepare("DELETE FROM job_requests WHERE id = ? AND homeowner_id = ?");
    $stmt->execute([$delete_id, $user_id]);
    // إعادة توجيه لتنظيف الرابط بعد الحذف
    header("Location: my_jobs.php");
    exit();
}

// جلب طلبات صاحب المنزل مع اسم القسم
$sql = "SELECT j.*, c.name as category_name 
        FROM job_requests j 
        JOIN categories c ON j.category_id = c.id 
        WHERE j.homeowner_id = ? 
        ORDER BY j.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$jobs = $stmt->fetchAll();

include_once '../includes/header.php';
include_once '../includes/navbar.php';
?>

<div class="google-wrapper">
    <?php include_once '../includes/user_sidebar.php'; ?>

    <main class="google-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1"><?php echo $lang['my_maintenance_requests']; ?></h3>
            </div>
            <a href="post_job.php" class="btn btn-primary rounded-pill px-4 fw-bold">
                <i class="bi bi-plus-lg me-1"></i> <?php echo $lang['post_job']; ?>
            </a>
        </div>

        <div class="google-card p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 border-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3 text-muted small border-0"><?php echo $lang['job_title']; ?></th>
                            <th class="py-3 text-muted small border-0"><?php echo $lang['category']; ?></th>
                            <th class="py-3 text-muted small border-0"><?php echo $lang['status']; ?></th>
                            <th class="py-3 text-muted small border-0"><?php echo $lang['date_posted']; ?></th>
                            <th class="pe-4 py-3 text-end text-muted small border-0"><?php echo $lang['action']; ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($jobs) > 0): ?>
                            <?php foreach ($jobs as $job): ?>
                                <tr>
                                    <td class="ps-4 py-3 fw-bold text-dark border-light"><?php echo htmlspecialchars($job['title']); ?></td>
                                    <td class="py-3 border-light">
                                        <span class="badge bg-light text-secondary border rounded-pill px-3"><?php echo htmlspecialchars($job['category_name']); ?></span>
                                    </td>
                                    <td class="py-3 border-light">
                                        <?php 
                                            // تنسيق الألوان حسب الحالة
                                            $badge_class = 'bg-primary';
                                            if ($job['status'] == 'in-progress') $badge_class = 'bg-warning text-dark';
                                            if ($job['status'] == 'completed') $badge_class = 'bg-success';
                                            if ($job['status'] == 'cancelled') $badge_class = 'bg-danger';
                                        ?>
                                        <span class="badge <?php echo $badge_class; ?> rounded-pill px-3"><?php echo ucfirst($job['status']); ?></span>
                                    </td>
                                    <td class="py-3 text-muted small border-light"><?php echo date('M d, Y', strtotime($job['created_at'])); ?></td>
                                    <td class="pe-4 py-3 text-end border-light">
                                        <a href="view_bids.php?job_id=<?php echo $job['id']; ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold mb-1 mb-md-0">
                                            <?php echo $lang['view_bids_btn']; ?>
                                        </a>
                                        
                                        <?php if ($job['status'] == 'open'): ?>
                                            <a href="my_jobs.php?delete_id=<?php echo $job['id']; ?>" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-bold ms-1" onclick="return confirm('<?php echo $lang['confirm_delete_job']; ?>');">
                                                <?php echo $lang['delete']; ?>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted border-0">
                                    <i class="bi bi-folder2-open fs-1 d-block mb-3 text-light-subtle"></i>
                                    <?php echo $lang['no_jobs_posted']; ?> <a href="post_job.php" class="text-decoration-none fw-bold"><?php echo $lang['post_job']; ?></a>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<?php include_once '../includes/footer.php'; ?>