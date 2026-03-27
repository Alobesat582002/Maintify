<?php
require_once '../config/db.php';

// حماية الصفحة: للأدمن فقط
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$success = '';
$error = '';

// معالجة حذف الطلب
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $job_id = $_POST['job_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM job_requests WHERE id = ?");
        if ($stmt->execute([$job_id])) {
            $success = "Job deleted successfully.";
        }
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            $error = "Cannot delete this job because it has active bids or orders.";
        } else {
            $error = "Database error: " . $e->getMessage();
        }
    }
}

$query = "
    SELECT 
        j.id, j.title, j.status, j.created_at AS posted_at,
        ho.first_name AS ho_fname, ho.last_name AS ho_lname,
        c.name AS category_name,
        t.first_name AS tech_fname, t.last_name AS tech_lname, t.profile_image AS tech_avatar,
        o.created_at AS started_at, o.completed_at
    FROM job_requests j
    JOIN users ho ON j.homeowner_id = ho.id
    LEFT JOIN categories c ON j.category_id = c.id
    LEFT JOIN orders o ON j.id = o.job_id
    LEFT JOIN bids b ON o.bid_id = b.id
    LEFT JOIN users t ON b.technician_id = t.id
    ORDER BY j.created_at DESC
";
$stmt = $pdo->query($query);
$jobs = $stmt->fetchAll();

include_once '../includes/header.php';
include_once '../includes/navbar.php';
?>

<div class="admin-wrapper">
    <?php include_once 'includes/sidebar.php'; ?>

    <div class="admin-content p-4 p-md-5 w-100">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold">Manage Jobs & Orders</h2>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success fw-bold"><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger fw-bold"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 py-3">Job Details</th>
                                <th class="py-3">Homeowner</th>
                                <th class="py-3">Technician</th>
                                <th class="py-3">Status</th>
                                <th class="py-3">Timeline</th>
                                <th class="pe-4 py-3 text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($jobs as $job): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-primary"><?php echo htmlspecialchars($job['title']); ?></div>
                                        <span class="badge bg-light text-dark border mt-1">
                                            <?php echo htmlspecialchars($job['category_name'] ?? 'Uncategorized'); ?>
                                        </span>
                                    </td>

                                    <td>
                                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($job['ho_fname'] . ' ' . $job['ho_lname']); ?></div>
                                    </td>

                                    <td>
                                        <?php if ($job['tech_fname']): ?>
                                            <div class="d-flex align-items-center gap-2">
                                                <?php
                                                $db_avatar = $job['tech_avatar'];
                                                $file_path = '../assets/images/avatars/' . $db_avatar;

                                                if (!empty($db_avatar) && file_exists($file_path)) {
                                                    $avatar = $file_path;
                                                } else {
                                                    $avatar = '../assets/images/default-avatar.png';
                                                }
                                                ?>
                                                <img src="<?php echo htmlspecialchars($avatar); ?>" alt="Avatar" class="rounded-circle object-fit-cover border" width="35" height="35">
                                                <div class="fw-bold text-dark"><?php echo htmlspecialchars($job['tech_fname'] . ' ' . $job['tech_lname']); ?></div>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted small fst-italic">Not Assigned Yet</span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?php
                                        $badgeClass = 'bg-secondary';
                                        if ($job['status'] == 'open') $badgeClass = 'bg-primary';
                                        if ($job['status'] == 'in_progress') $badgeClass = 'bg-warning text-dark';
                                        if ($job['status'] == 'completed') $badgeClass = 'bg-success';
                                        if ($job['status'] == 'cancelled') $badgeClass = 'bg-danger';
                                        ?>
                                        <span class="badge <?php echo $badgeClass; ?> px-2 py-1">
                                            <?php echo ucfirst(str_replace('_', ' ', $job['status'])); ?>
                                        </span>
                                    </td>

                                    <td>
                                        <div class="small text-muted">
                                            <div><strong>Posted:</strong> <?php echo date('M d, Y - h:i A', strtotime($job['posted_at'])); ?></div>
                                            <?php if ($job['started_at']): ?>
                                                <div class="text-success mt-1"><strong>Started:</strong> <?php echo date('M d, Y - h:i A', strtotime($job['started_at'])); ?></div>
                                            <?php endif; ?>
                                            <?php if ($job['completed_at']): ?>
                                                <div class="text-primary mt-1"><strong>Ended:</strong> <?php echo date('M d, Y - h:i A', strtotime($job['completed_at'])); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </td>

                                    <td class="pe-4 text-end">
                                        <form action="manage_jobs.php" method="POST" class="d-inline">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="job_id" value="<?php echo $job['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger fw-bold" onclick="return confirm('Are you sure you want to delete this job? This action cannot be undone.');">
                                                Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>

                            <?php if (empty($jobs)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">No jobs found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>