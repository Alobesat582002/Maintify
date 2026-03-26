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

<div class="container mt-5" style="min-height: 70vh;">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="fw-bold">Available Maintenance Jobs</h2>
            <p class="text-muted">Browse open requests from homeowners and submit your proposals.</p>
        </div>
    </div>

    <div class="row g-4">
        <?php if (count($jobs) > 0): ?>
            <?php foreach ($jobs as $job): ?>
                <div class="col-md-6">
                    <div class="card shadow-sm h-100 card-hover border-0">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="fw-bold text-primary mb-0"><?php echo htmlspecialchars($job['title']); ?></h5>
                                <span class="badge bg-secondary"><?php echo htmlspecialchars($job['category_name']); ?></span>
                            </div>
                            
                            <p class="text-muted small mb-3">
                                <i class="bi bi-person me-1"></i> <?php echo htmlspecialchars($job['first_name']); ?> | 
                                <i class="bi bi-geo-alt ms-2 me-1"></i> <?php echo htmlspecialchars($job['city'] ?? 'Location not specified'); ?> |
                                <i class="bi bi-clock ms-2 me-1"></i> <?php echo date('M d, Y', strtotime($job['created_at'])); ?>
                            </p>
                            
                            <p class="card-text text-muted mb-4" style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; line-clamp: 3; overflow: hidden;">
                                <?php echo htmlspecialchars($job['description']); ?>
                            </p>
                            
                            <div class="mt-auto">
                                <a href="job_details.php?id=<?php echo $job['id']; ?>" class="btn btn-outline-primary w-100 fw-bold">View Details & Bid</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <i class="bi bi-search fs-1 text-muted d-block mb-3"></i>
                <h4 class="text-muted">No open jobs available right now</h4>
                <p>Check back later. Homeowners are posting new jobs every day.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>