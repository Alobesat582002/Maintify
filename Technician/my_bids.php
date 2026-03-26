<?php
require_once '../config/db.php';

// حماية الواجهة للفنيين فقط
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'technician') {
    header("Location: ../login.php");
    exit();
}

$technician_id = $_SESSION['user_id'];

// جلب جميع العروض التي قدمها الفني مع تفاصيل الطلب المرتبط بها
$sql = "SELECT b.*, j.title as job_title, j.status as job_status, c.name as category_name, u.first_name as owner_name
        FROM bids b
        JOIN job_requests j ON b.job_id = j.id
        JOIN categories c ON j.category_id = c.id
        JOIN users u ON j.homeowner_id = u.id
        WHERE b.technician_id = ?
        ORDER BY b.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$technician_id]);
$my_bids = $stmt->fetchAll();

include_once '../includes/header.php';
include_once '../includes/navbar.php';
?>

<div class="container mt-5" style="min-height: 70vh;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">My Proposals</h2>
        <a href="browse_jobs.php" class="btn btn-primary">+ Find More Jobs</a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Job Title</th>
                            <th>Category</th>
                            <th>Your Bid</th>
                            <th>Status</th>
                            <th>Date Submitted</th>
                            <th class="text-end pe-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($my_bids) > 0): ?>
                            <?php foreach ($my_bids as $bid): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($bid['job_title']); ?></div>
                                        <small class="text-muted">By: <?php echo htmlspecialchars($bid['owner_name']); ?></small>
                                    </td>
                                    <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($bid['category_name']); ?></span></td>
                                    <td><span class="fw-bold text-success">$<?php echo number_format($bid['price'], 2); ?></span></td>
                                    <td>
                                        <?php 
                                            $status_class = 'bg-warning text-dark'; // Pending
                                            if ($bid['status'] == 'accepted') $status_class = 'bg-success';
                                            if ($bid['status'] == 'rejected') $status_class = 'bg-danger';
                                        ?>
                                        <span class="badge <?php echo $status_class; ?>">
                                            <?php echo ucfirst($bid['status']); ?>
                                        </span>
                                    </td>
                                    <td class="text-muted small"><?php echo date('M d, Y', strtotime($bid['created_at'])); ?></td>
                                    <td class="text-end pe-4">
                                        <a href="job_details.php?id=<?php echo $bid['job_id']; ?>" class="btn btn-sm btn-outline-primary">View Details</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-file-earmark-text fs-1 d-block mb-2"></i>
                                    You haven't submitted any proposals yet.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>