<?php
require_once '../config/db.php';

// حماية الواجهة
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'homeowner') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// معالجة طلب الحذف (إذا قام المستخدم بالضغط على زر Delete)
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

<div class="container mt-5" style="min-height: 70vh;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>My Maintenance Requests</h2>
        <a href="dashboard.php" class="btn btn-outline-secondary">← Back to Dashboard</a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Date Posted</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($jobs) > 0): ?>
                            <?php foreach ($jobs as $job): ?>
                                <tr>
                                    <td class="align-middle fw-bold"><?php echo htmlspecialchars($job['title']); ?></td>
                                    <td class="align-middle"><?php echo htmlspecialchars($job['category_name']); ?></td>
                                    <td class="align-middle">
                                        <?php 
                                            // تنسيق الألوان حسب الحالة
                                            $badge_class = 'bg-primary';
                                            if ($job['status'] == 'in-progress') $badge_class = 'bg-warning text-dark';
                                            if ($job['status'] == 'completed') $badge_class = 'bg-success';
                                        ?>
                                        <span class="badge <?php echo $badge_class; ?>"><?php echo ucfirst($job['status']); ?></span>
                                    </td>
                                    <td class="align-middle text-muted"><?php echo date('M d, Y', strtotime($job['created_at'])); ?></td>
                                    <td class="align-middle text-end">
                                        <a href="view_job.php?id=<?php echo $job['id']; ?>" class="btn btn-sm btn-primary">View / Bids</a>
                                        
                                        <?php if ($job['status'] == 'open'): ?>
                                            <a href="my_jobs.php?delete_id=<?php echo $job['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this job request?');">Delete</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">You haven't posted any job requests yet. <a href="post_job.php">Post a job now.</a></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>