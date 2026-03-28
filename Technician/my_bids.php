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

<div class="google-wrapper">
    <?php include_once '../includes/user_sidebar.php'; ?>

    <main class="google-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold mb-1"><?php echo $lang['my_proposals']; ?></h3>
            <a href="browse_jobs.php" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
                <i class="bi bi-search me-1"></i> <?php echo $lang['find_more_jobs']; ?>
            </a>
        </div>

        <div class="google-card p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle border-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3 text-muted small border-0"><?php echo $lang['job_title']; ?></th>
                            <th class="py-3 text-muted small border-0"><?php echo $lang['category']; ?></th>
                            <th class="py-3 text-muted small border-0"><?php echo $lang['my_bid']; ?></th>
                            <th class="py-3 text-muted small border-0"><?php echo $lang['status']; ?></th>
                            <th class="py-3 text-muted small border-0"><?php echo $lang['date_submitted']; ?></th>
                            <th class="text-end pe-4 py-3 text-muted small border-0"><?php echo $lang['action']; ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($my_bids) > 0): ?>
                            <?php foreach ($my_bids as $bid): ?>
                                <tr>
                                    <td class="ps-4 py-3 border-light">
                                        <div class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($bid['job_title']); ?></div>
                                        <small class="text-muted"><i class="bi bi-person me-1"></i><?php echo $lang['by_owner']; ?> <?php echo htmlspecialchars($bid['owner_name']); ?></small>
                                    </td>
                                    <td class="py-3 border-light">
                                        <span class="badge bg-light text-secondary border rounded-pill px-3"><?php echo htmlspecialchars($bid['category_name']); ?></span>
                                    </td>
                                    <td class="py-3 border-light">
                                        <span class="fw-bold text-success" dir="ltr">$<?php echo number_format($bid['price'], 2); ?></span>
                                    </td>
                                    <td class="py-3 border-light">
                                        <?php 
                                            $status_class = 'bg-warning text-dark'; // Pending
                                            if ($bid['status'] == 'accepted') $status_class = 'bg-success';
                                            if ($bid['status'] == 'rejected') $status_class = 'bg-danger';
                                        ?>
                                        <span class="badge <?php echo $status_class; ?> rounded-pill px-3 py-2">
                                            <?php echo ucfirst($bid['status']); ?>
                                        </span>
                                    </td>
                                    <td class="text-muted small py-3 border-light">
                                        <?php echo date('M d, Y', strtotime($bid['created_at'])); ?>
                                    </td>
                                    <td class="text-end pe-4 py-3 border-light">
                                        <a href="job_details.php?id=<?php echo $bid['job_id']; ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold"><?php echo $lang['view_details']; ?></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted border-0">
                                    <i class="bi bi-file-earmark-text fs-1 d-block mb-3 text-light-subtle"></i>
                                    <?php echo $lang['no_proposals_yet']; ?>
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