<?php
require_once '../config/db.php';

// حماية الصفحة: للأدمن فقط
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$success = '';
$error = '';

// معالجة طلبات الإيقاف والتفعيل
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['target_user_id'])) {
        $target_id = $_POST['target_user_id'];
        
        if ($_POST['action'] === 'suspend') {
            $days = (int)$_POST['suspend_days'];
            if ($days > 0) {
                $suspended_until = date('Y-m-d H:i:s', strtotime("+$days days"));
                $stmt = $pdo->prepare("UPDATE users SET status = 'suspended', suspended_until = ? WHERE id = ?");
                if ($stmt->execute([$suspended_until, $target_id])) {
                    $success = "User suspended successfully for $days days.";
                }
            } else {
                $error = "Please enter a valid number of days.";
            }
        } elseif ($_POST['action'] === 'activate') {
            $stmt = $pdo->prepare("UPDATE users SET status = 'active', suspended_until = NULL WHERE id = ?");
            if ($stmt->execute([$target_id])) {
                $success = "User activated successfully.";
            }
        }
    }
}

// جلب جميع المستخدمين ما عدا الإدارة
$stmt = $pdo->query("SELECT id, first_name, last_name, email, role, status, suspended_until, created_at FROM users WHERE role != 'admin' ORDER BY created_at DESC");
$users = $stmt->fetchAll();

include_once '../includes/header.php';
include_once '../includes/navbar.php';
?>

<div class="admin-wrapper">
    <?php include_once 'includes/sidebar.php'; ?>

    <div class="admin-content p-4 p-md-5 w-100">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold">Manage Users</h2>
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
                                <th class="ps-4 py-3">Name</th>
                                <th class="py-3">Email</th>
                                <th class="py-3">Role</th>
                                <th class="py-3">Status</th>
                                <th class="pe-4 py-3 text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $u): ?>
                                <tr>
                                    <td class="ps-4 fw-bold"><?php echo htmlspecialchars($u['first_name'] . ' ' . $u['last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                                    <td>
                                        <span class="badge bg-secondary px-2 py-1"><?php echo ucfirst($u['role']); ?></span>
                                    </td>
                                    <td>
                                        <?php if ($u['status'] === 'active'): ?>
                                            <span class="badge bg-success px-2 py-1">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger px-2 py-1">Suspended</span>
                                            <small class="d-block text-muted mt-1" style="font-size: 11px;">
                                                Until: <?php echo date('M d, Y', strtotime($u['suspended_until'])); ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="pe-4 text-end">
                                        <?php if ($u['status'] === 'active'): ?>
                                            <button class="btn btn-sm btn-outline-danger fw-bold" data-bs-toggle="modal" data-bs-target="#suspendModal<?php echo $u['id']; ?>">
                                                Suspend
                                            </button>
                                        <?php else: ?>
                                            <form action="manage_users.php" method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="activate">
                                                <input type="hidden" name="target_user_id" value="<?php echo $u['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-success fw-bold" onclick="return confirm('Are you sure you want to activate this user?');">
                                                    Activate
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>

                                <?php if ($u['status'] === 'active'): ?>
                                <div class="modal fade" id="suspendModal<?php echo $u['id']; ?>" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow">
                                            <div class="modal-header bg-danger text-white border-0">
                                                <h5 class="modal-title fw-bold">Suspend User: <?php echo htmlspecialchars($u['first_name']); ?></h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="manage_users.php" method="POST">
                                                <div class="modal-body p-4">
                                                    <input type="hidden" name="action" value="suspend">
                                                    <input type="hidden" name="target_user_id" value="<?php echo $u['id']; ?>">
                                                    <p class="mb-3">How many days should this account be suspended?</p>
                                                    <div class="input-group mb-3">
                                                        <input type="number" name="suspend_days" class="form-control" min="1" max="365" required placeholder="e.g., 7">
                                                        <span class="input-group-text">Days</span>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-0">
                                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-danger fw-bold">Confirm Suspension</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>

                            <?php endforeach; ?>
                            
                            <?php if (empty($users)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">No users found.</td>
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