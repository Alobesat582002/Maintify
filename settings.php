<?php
require_once 'config/db.php';

// حماية الواجهة
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// معالجة تغيير كلمة المرور
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = "All password fields are required.";
    } elseif ($new_password !== $confirm_password) {
        $error = "New passwords do not match.";
    } elseif (strlen($new_password) < 6) {
        $error = "New password must be at least 6 characters.";
    } else {
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();

        if ($user && password_verify($current_password, $user['password'])) {
            $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            if ($update_stmt->execute([$new_hashed_password, $user_id])) {
                $success = "Password changed successfully!";
            } else {
                $error = "Something went wrong. Please try again.";
            }
        } else {
            $error = "Current password is incorrect.";
        }
    }
}

// جلب بيانات المستخدم الأساسية
$stmt = $pdo->prepare("SELECT first_name, last_name, phone, profile_image, role FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user_info = $stmt->fetch();

include_once 'includes/header.php';
include_once 'includes/navbar.php';
?>

<style>
    .settings-container { max-width: 600px; margin: auto; }
    .settings-group { border-radius: 16px; overflow: hidden; border: none; }
    .settings-item { border: none; border-bottom: 1px solid #f3f4f6; padding: 1rem 1.25rem; font-size: 15px; font-weight: 500; color: #111827; }
    .settings-item:last-child { border-bottom: none; }
    .settings-item i.bi-chevron-right, .settings-item i.bi-chevron-left { color: #9ca3af; font-size: 14px; }
    .form-switch .form-check-input { width: 2.5em; height: 1.25em; cursor: pointer; }
    .form-switch .form-check-input:checked { background-color: #4f46e5; border-color: #4f46e5; }
    body { background-color: #f8fafc; }
</style>

<div class="container mt-4 mb-5 settings-container">
    
    <?php if($error): ?> <div class="alert alert-danger rounded-4"><?php echo $error; ?></div> <?php endif; ?>
    <?php if($success): ?> <div class="alert alert-success rounded-4"><?php echo $success; ?></div> <?php endif; ?>

    <div class="d-flex align-items-center bg-white p-3 rounded-4 shadow-sm mb-4">
        <?php 
            $img_src = (!empty($user_info['profile_image']) && $user_info['profile_image'] !== 'default.png') 
                ? "assets/images/avatars/" . $user_info['profile_image'] 
                : "assets/images/logo.png";
        ?>
        <img src="<?php echo htmlspecialchars($img_src); ?>" class="rounded-circle object-fit-cover border" style="width: 60px; height: 60px;">
        <div class="ms-3">
            <h5 class="fw-bold mb-0"><?php echo htmlspecialchars($user_info['first_name'] . ' ' . $user_info['last_name']); ?></h5>
            <small class="text-muted"><?php echo htmlspecialchars($user_info['phone'] ?? ucfirst($user_info['role'])); ?></small>
        </div>
    </div>

    <h6 class="fw-bold mb-3 ms-2"><?php echo $lang['my_profile']; ?></h6>
    <div class="list-group shadow-sm settings-group mb-4 bg-white">
        <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center settings-item" data-bs-toggle="modal" data-bs-target="#passwordModal">
            <span><?php echo $lang['change_password']; ?></span>
            <i class="bi <?php echo ($current_lang == 'ar') ? 'bi-chevron-left' : 'bi-chevron-right'; ?>"></i>
        </a>
        <a href="<?php echo ($user_info['role'] == 'technician') ? 'Technician/profile.php' : 'Homeowner/profile.php'; ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center settings-item">
            <span><?php echo $lang['edit_profile']; ?></span>
            <i class="bi <?php echo ($current_lang == 'ar') ? 'bi-chevron-left' : 'bi-chevron-right'; ?>"></i>
        </a>
    </div>

    <h6 class="fw-bold mb-3 ms-2"><?php echo $lang['settings']; ?></h6>
    <div class="list-group shadow-sm settings-group mb-4 bg-white">
        <div class="list-group-item d-flex justify-content-between align-items-center settings-item">
            <span><?php echo $lang['app_language']; ?></span>
            <?php if ($current_lang == 'en'): ?>
                <a href="?lang=ar" class="text-primary fw-bold text-decoration-none">العربية <i class="bi bi-translate ms-1"></i></a>
            <?php else: ?>
                <a href="?lang=en" class="text-primary fw-bold text-decoration-none">English <i class="bi bi-translate ms-1"></i></a>
            <?php endif; ?>
        </div>
        <div class="list-group-item d-flex justify-content-between align-items-center settings-item">
            <span><?php echo $lang['location_services']; ?></span>
            <div class="form-check form-switch m-0">
                <input class="form-check-input" type="checkbox" id="locationSwitch" checked>
            </div>
        </div>
        <div class="list-group-item d-flex justify-content-between align-items-center settings-item">
            <span><?php echo $lang['push_notifications']; ?></span>
            <div class="form-check form-switch m-0">
                <input class="form-check-input" type="checkbox" id="notifSwitch" checked>
            </div>
        </div>
    </div>

    <h6 class="fw-bold mb-3 ms-2"><?php echo $lang['help_support']; ?></h6>
    <div class="list-group shadow-sm settings-group mb-4 bg-white">
        <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center settings-item">
            <span><?php echo $lang['help_center']; ?></span>
            <i class="bi <?php echo ($current_lang == 'ar') ? 'bi-chevron-left' : 'bi-chevron-right'; ?>"></i>
        </a>
        <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center settings-item">
            <span><?php echo $lang['privacy_policy']; ?></span>
            <i class="bi <?php echo ($current_lang == 'ar') ? 'bi-chevron-left' : 'bi-chevron-right'; ?>"></i>
        </a>
    </div>

    <div class="list-group shadow-sm settings-group bg-white">
        <a href="logout.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center settings-item text-danger fw-bold">
            <span><?php echo $lang['logout']; ?></span>
            <i class="bi bi-box-arrow-right"></i>
        </a>
    </div>

</div>

<div class="modal fade" id="passwordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold"><?php echo $lang['change_password']; ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="settings.php" method="POST">
                    <div class="mb-3">
                        <label class="form-label text-muted small"><?php echo $lang['current_password']; ?></label>
                        <input type="password" name="current_password" class="form-control rounded-3" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small"><?php echo $lang['new_password']; ?></label>
                        <input type="password" name="new_password" class="form-control rounded-3" required minlength="6">
                    </div>
                    <div class="mb-4">
                        <label class="form-label text-muted small"><?php echo $lang['confirm_password']; ?></label>
                        <input type="password" name="confirm_password" class="form-control rounded-3" required minlength="6">
                    </div>
                    <button type="submit" name="change_password" class="btn btn-primary w-100 fw-bold rounded-3 py-2"><?php echo $lang['update_password']; ?></button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>