<?php
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'technician') {
    header("Location: ../login.php");
    exit();
}

// جلب جميع الطلبات المتاحة (المفتوحة) مع اسم القسم ومعلومات صاحب المنزل (مدينته)
$sql = "SELECT j.*, c.name as category_name, u.first_name, u.city 
        FROM job_requests j 
        JOIN categories c ON j.category_id = c.id 
        JOIN users u ON j.homeowner_id = u.id 
        WHERE j.status = 'open' 
        ORDER BY j.created_at DESC";

$stmt = $pdo->query($sql);
$jobs = $stmt->fetchAll();

include_once '../includes/header.php';
include_once '../includes/navbar.php';
?>

<div class="google-wrapper">
    <?php include_once '../includes/user_sidebar.php'; ?>

    <main class="google-content">
        <div class="mb-4">
            <h3 class="fw-bold mb-1"><?php echo $lang['available_maintenance_jobs']; ?></h3>
            <p class="text-muted small"><?php echo $lang['browse_open_requests']; ?></p>
        </div>

        <div class="row g-4">
            <?php if (count($jobs) > 0): ?>
                <?php foreach ($jobs as $job): ?>
                    <div class="col-md-6">
                        <div class="google-card p-4 h-100 d-flex flex-column hover-card">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h5 class="fw-bold text-primary mb-0"><?php echo htmlspecialchars($job['title']); ?></h5>
                                <span class="badge bg-light text-secondary border rounded-pill px-3 py-2"><?php echo htmlspecialchars($job['category_name']); ?></span>
                            </div>
                            
                            <div class="d-flex flex-wrap gap-3 text-muted small mb-3 bg-light p-2 rounded-4">
                                <div><i class="bi bi-person text-primary me-1"></i> <?php echo htmlspecialchars($job['first_name']); ?></div>
                                <div><i class="bi bi-geo-alt-fill text-danger me-1"></i> <?php echo htmlspecialchars($job['city'] ?? $lang['location_not_specified']); ?></div>
                                <div><i class="bi bi-clock-fill text-warning me-1"></i> <span dir="ltr"><?php echo date('M d, Y', strtotime($job['created_at'])); ?></span></div>
                            </div>
                            
                            <p class="card-text text-muted mb-4" style="font-size: 14px; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; line-clamp: 3; overflow: hidden;">
                                <?php echo htmlspecialchars($job['description']); ?>
                            </p>
                            
                            <div class="mt-auto pt-3 border-top">
                                <a href="job_details.php?id=<?php echo $job['id']; ?>" class="btn btn-outline-primary w-100 fw-bold rounded-pill"><?php echo $lang['view_details_bid']; ?></a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <div class="google-card p-5 mx-auto" style="max-width: 500px;">
                        <i class="bi bi-search fs-1 text-muted d-block mb-3"></i>
                        <h4 class="fw-bold text-dark"><?php echo $lang['no_open_jobs']; ?></h4>
                        <p class="text-muted mb-0"><?php echo $lang['check_back_later']; ?></p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<style>
    .hover-card { transition: transform 0.2s, box-shadow 0.2s; }
    .hover-card:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,0.05)!important; }
</style>

<?php include_once '../includes/footer.php'; ?>