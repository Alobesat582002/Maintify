<?php
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'homeowner') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// إحصائيات
$stmt = $pdo->prepare("SELECT COUNT(*) FROM job_requests WHERE homeowner_id = ? AND status = 'open'");
$stmt->execute([$user_id]);
$open_jobs = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM job_requests WHERE homeowner_id = ? AND status = 'in-progress'");
$stmt->execute([$user_id]);
$in_progress_jobs = $stmt->fetchColumn();

$stmt = $pdo->prepare("
    SELECT COUNT(b.id) 
    FROM bids b 
    JOIN job_requests j ON b.job_id = j.id 
    WHERE j.homeowner_id = ? AND j.status = 'open'
");
$stmt->execute([$user_id]);
$total_bids_received = $stmt->fetchColumn();

// آخر الطلبات
$stmt = $pdo->prepare("
    SELECT j.id, j.title, j.status, j.created_at, c.name as category_name,
           (SELECT COUNT(*) FROM bids WHERE job_id = j.id) as bids_count
    FROM job_requests j
    JOIN categories c ON j.category_id = c.id
    WHERE j.homeowner_id = ?
    ORDER BY j.created_at DESC
    LIMIT 5
");
$stmt->execute([$user_id]);
$recent_jobs = $stmt->fetchAll();

include_once '../includes/header.php';
include_once '../includes/navbar.php';
?>

<div class="google-wrapper">
    <?php include_once '../includes/user_sidebar.php'; ?>

    <main class="google-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1"><?php echo $lang['welcome_back']; ?>, <?php echo htmlspecialchars($_SESSION['name']); ?></h3>
                <p class="text-muted small"><?php echo $lang['dashboard_overview']; ?></p>
            </div>
            <a href="post_job.php" class="btn btn-primary rounded-pill px-4 fw-bold">
                <i class="bi bi-plus-lg me-1"></i> <?php echo $lang['post_job']; ?>
            </a>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="google-card p-4 d-flex justify-content-between align-items-center h-100">
                    <div>
                        <p class="text-muted fw-bold mb-1 small text-uppercase"><?php echo $lang['open_requests']; ?></p>
                        <h2 class="fw-bold mb-0 text-primary"><?php echo $open_jobs; ?></h2>
                    </div>
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                        <i class="bi bi-folder2-open fs-4"></i>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="google-card p-4 d-flex justify-content-between align-items-center h-100">
                    <div>
                        <p class="text-muted fw-bold mb-1 small text-uppercase"><?php echo $lang['proposals_received']; ?></p>
                        <h2 class="fw-bold mb-0 text-warning"><?php echo $total_bids_received; ?></h2>
                    </div>
                    <div class="bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                        <i class="bi bi-file-earmark-text fs-4"></i>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="google-card p-4 d-flex justify-content-between align-items-center h-100">
                    <div>
                        <p class="text-muted fw-bold mb-1 small text-uppercase"><?php echo $lang['active_orders']; ?></p>
                        <h2 class="fw-bold mb-0 text-success"><?php echo $in_progress_jobs; ?></h2>
                    </div>
                    <div class="bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                        <i class="bi bi-tools fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="google-card p-0">
            <div class="p-4 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0"><?php echo $lang['recent_job_posts']; ?></h6>
                <a href="my_jobs.php" class="btn btn-sm btn-light rounded-pill px-3"><?php echo $lang['view_all']; ?></a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 border-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3 text-muted small border-0"><?php echo $lang['job_title']; ?></th>
                            <th class="py-3 text-muted small border-0"><?php echo $lang['category']; ?></th>
                            <th class="py-3 text-muted small border-0"><?php echo $lang['date_posted']; ?></th>
                            <th class="py-3 text-muted small border-0"><?php echo $lang['status']; ?></th>
                            <th class="pe-4 py-3 text-end text-muted small border-0"><?php echo $lang['action']; ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($recent_jobs) > 0): ?>
                            <?php foreach($recent_jobs as $job): ?>
                                <tr>
                                    <td class="ps-4 py-3 fw-bold text-dark border-light"><?php echo htmlspecialchars($job['title']); ?></td>
                                    <td class="py-3 border-light"><span class="badge bg-light text-secondary border"><?php echo htmlspecialchars($job['category_name']); ?></span></td>
                                    <td class="py-3 text-muted small border-light"><?php echo date('M d, Y', strtotime($job['created_at'])); ?></td>
                                    <td class="py-3 border-light">
                                        <?php 
                                            $status_class = 'bg-primary';
                                            if ($job['status'] == 'in-progress') $status_class = 'bg-warning text-dark';
                                            if ($job['status'] == 'completed') $status_class = 'bg-success';
                                            if ($job['status'] == 'cancelled') $status_class = 'bg-danger';
                                        ?>
                                        <span class="badge <?php echo $status_class; ?> rounded-pill px-2"><?php echo ucfirst($job['status']); ?></span>
                                    </td>
                                    <td class="pe-4 py-3 text-end border-light">
                                        <a href="view_bids.php?job_id=<?php echo $job['id']; ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold"><?php echo $lang['manage']; ?></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted border-0">
                                    <i class="bi bi-inbox fs-1 d-block mb-3 text-light-subtle"></i>
                                    <?php echo $lang['no_jobs_posted']; ?>
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