<?php
require_once '../config/db.php';

// حماية الصفحة: للأدمن فقط
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // 1. تحديث حالة الشكوى
    if ($_POST['action'] === 'update_status') {
        $complaint_id = $_POST['complaint_id'];
        $status = $_POST['status'];
        
        $stmt = $pdo->prepare("UPDATE complaints SET status = ? WHERE id = ?");
        if ($stmt->execute([$status, $complaint_id])) {
            $success = "Complaint status updated successfully.";
        } else {
            $error = "Failed to update status.";
        }
    } 
    // 2. إيقاف المشتكى عليه بناءً على شكوى
    elseif ($_POST['action'] === 'suspend_user') {
        $user_id = $_POST['target_user_id'];
        $days = (int)$_POST['suspend_days'];
        $complaint_id = $_POST['complaint_id'];

        if ($days > 0) {
            $suspended_until = date('Y-m-d H:i:s', strtotime("+$days days"));
            try {
                $pdo->beginTransaction();
                
                // إيقاف الحساب
                $stmt = $pdo->prepare("UPDATE users SET status = 'suspended', suspended_until = ? WHERE id = ?");
                $stmt->execute([$suspended_until, $user_id]);
                
                // تحويل الشكوى إلى "تم الحل" تلقائياً
                $stmt2 = $pdo->prepare("UPDATE complaints SET status = 'resolved' WHERE id = ?");
                $stmt2->execute([$complaint_id]);
                
                $pdo->commit();
                $success = "User suspended and complaint marked as resolved.";
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = "Database error: " . $e->getMessage();
            }
        } else {
            $error = "Please enter valid days.";
        }
    }
}

// جلب الشكاوي مع البيانات المرتبطة
$query = "
    SELECT c.*, 
           u1.first_name AS reporter_fname, u1.last_name AS reporter_lname, u1.role AS reporter_role,
           u2.first_name AS reported_fname, u2.last_name AS reported_lname, u2.role AS reported_role, u2.status AS reported_status,
           j.title AS job_title
    FROM complaints c
    JOIN users u1 ON c.reporter_id = u1.id
    JOIN users u2 ON c.reported_user_id = u2.id
    LEFT JOIN job_requests j ON c.job_id = j.id
    ORDER BY c.created_at DESC
";
$stmt = $pdo->query($query);
$complaints = $stmt->fetchAll();

include_once '../includes/header.php';
include_once '../includes/navbar.php';
?>

<div class="admin-wrapper">
    <?php include_once 'includes/sidebar.php'; ?>

    <div class="admin-content p-4 p-md-5 w-100">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold">Manage Complaints</h2>
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
                                <th class="ps-4 py-3">Reporter</th>
                                <th class="py-3">Reported User</th>
                                <th class="py-3">Reason</th>
                                <th class="py-3">Status</th>
                                <th class="pe-4 py-3 text-end">Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($complaints as $c): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($c['reporter_fname']); ?></div>
                                        <span class="badge bg-secondary small"><?php echo ucfirst($c['reporter_role']); ?></span>
                                    </td>
                                    
                                    <td>
                                        <div class="fw-bold text-danger"><?php echo htmlspecialchars($c['reported_fname']); ?></div>
                                        <?php if($c['reported_status'] === 'suspended'): ?>
                                            <span class="badge bg-danger">Suspended</span>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <td>
                                        <div class="fw-bold"><?php echo htmlspecialchars($c['reason']); ?></div>
                                        <small class="text-muted">Job: <?php echo htmlspecialchars($c['job_title'] ?? 'General'); ?></small>
                                    </td>
                                    
                                    <td>
                                        <?php 
                                            $badgeClass = 'bg-warning text-dark';
                                            if ($c['status'] == 'reviewed') $badgeClass = 'bg-info text-dark';
                                            if ($c['status'] == 'resolved') $badgeClass = 'bg-success';
                                        ?>
                                        <span class="badge <?php echo $badgeClass; ?>"><?php echo ucfirst($c['status']); ?></span>
                                    </td>
                                    
                                    <td class="pe-4 text-end">
                                        <button class="btn btn-sm btn-outline-primary fw-bold" data-bs-toggle="modal" data-bs-target="#viewModal<?php echo $c['id']; ?>">
                                            View
                                        </button>
                                    </td>
                                </tr>

                                <div class="modal fade" id="viewModal<?php echo $c['id']; ?>" tabindex="-1">
                                    <div class="modal-dialog modal-lg modal-dialog-centered">
                                        <div class="modal-content border-0 shadow">
                                            <div class="modal-header bg-dark text-white border-0">
                                                <h5 class="modal-title fw-bold">Complaint Overview</h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body p-4">
                                                <h6 class="fw-bold text-primary mb-2">Complaint Details:</h6>
                                                <p class="p-3 bg-light rounded border"><?php echo nl2br(htmlspecialchars($c['details'])); ?></p>
                                                
                                                <div class="row mt-4">
                                                    <div class="col-md-6 border-end">
                                                        <h6 class="fw-bold">Update Status</h6>
                                                        <form action="manage_complaints.php" method="POST" class="d-flex gap-2">
                                                            <input type="hidden" name="action" value="update_status">
                                                            <input type="hidden" name="complaint_id" value="<?php echo $c['id']; ?>">
                                                            <select name="status" class="form-select form-select-sm">
                                                                <option value="pending" <?php echo $c['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                                <option value="reviewed" <?php echo $c['status'] == 'reviewed' ? 'selected' : ''; ?>>Reviewed</option>
                                                                <option value="resolved" <?php echo $c['status'] == 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                                                            </select>
                                                            <button type="submit" class="btn btn-sm btn-primary">Save</button>
                                                        </form>
                                                    </div>
                                                    <div class="col-md-6 ps-4">
                                                        <h6 class="fw-bold text-danger">Take Action (Suspend)</h6>
                                                        <?php if ($c['reported_status'] === 'active'): ?>
                                                            <form action="manage_complaints.php" method="POST" class="d-flex gap-2">
                                                                <input type="hidden" name="action" value="suspend_user">
                                                                <input type="hidden" name="complaint_id" value="<?php echo $c['id']; ?>">
                                                                <input type="hidden" name="target_user_id" value="<?php echo $c['reported_user_id']; ?>">
                                                                <input type="number" name="suspend_days" class="form-control form-control-sm" placeholder="Days" required>
                                                                <button type="submit" class="btn btn-sm btn-danger">Suspend</button>
                                                            </form>
                                                        <?php else: ?>
                                                            <span class="text-muted small">User is already suspended.</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>