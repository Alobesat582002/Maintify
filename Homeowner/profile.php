<?php
require_once '../config/db.php';

// حماية الواجهة
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'homeowner') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// 1. معالجة تحديث البيانات عند إرسال الفورم
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $phone = trim($_POST['phone']);
    $country = trim($_POST['country']);
    $city = trim($_POST['city']);
    $address = trim($_POST['address']);
    
    // جلب الاهتمامات
    $interests = isset($_POST['interests']) && is_array($_POST['interests']) ? $_POST['interests'] : [];

    // معالجة رفع الصورة مع فحص الحجم (5 ميجابايت)
    $profile_image = null;
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $filename = $_FILES['profile_image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $max_size = 5 * 1024 * 1024; // 5 MB

        if ($_FILES['profile_image']['size'] > $max_size) {
            $error = "Image size is too large. Maximum allowed size is 5MB.";
        } elseif (in_array($ext, $allowed)) {
            $new_filename = uniqid('avatar_') . '.' . $ext;
            $upload_path = '../assets/images/avatars/' . $new_filename;
            
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_path)) {
                $profile_image = $new_filename;
            } else {
                $error = "Failed to upload image. Check folder permissions.";
            }
        } else {
            $error = "Invalid image format. Use JPG, PNG, or WEBP.";
        }
    }

    if (empty($first_name) || empty($last_name)) {
        $error = "First name and last name are required.";
    } elseif (empty($error)) {
        try {
            $pdo->beginTransaction();

            // أ. تحديث بيانات المستخدم
            if ($profile_image) {
                $sql = "UPDATE users SET first_name = ?, last_name = ?, phone = ?, country = ?, city = ?, address = ?, profile_image = ? WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$first_name, $last_name, $phone, $country, $city, $address, $profile_image, $user_id]);
            } else {
                $sql = "UPDATE users SET first_name = ?, last_name = ?, phone = ?, country = ?, city = ?, address = ? WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$first_name, $last_name, $phone, $country, $city, $address, $user_id]);
            }

            // ب. تحديث الاهتمامات
            $stmt_delete = $pdo->prepare("DELETE FROM user_interests WHERE user_id = ?");
            $stmt_delete->execute([$user_id]);

            if (!empty($interests)) {
                $stmt_insert = $pdo->prepare("INSERT INTO user_interests (user_id, category_id) VALUES (?, ?)");
                foreach ($interests as $cat_id) {
                    $stmt_insert->execute([$user_id, $cat_id]);
                }
            }

            $pdo->commit();
            $_SESSION['name'] = $first_name . ' ' . $last_name;
            $success = "Profile updated successfully!";
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Failed to update profile: " . $e->getMessage();
        }
    }
}

// 2. جلب بيانات المستخدم الحالية
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// 3. جلب الاهتمامات
$stmt_int = $pdo->prepare("SELECT category_id FROM user_interests WHERE user_id = ?");
$stmt_int->execute([$user_id]);
$current_interests = $stmt_int->fetchAll(PDO::FETCH_COLUMN);

// 4. جلب جميع الأقسام
$stmt_cat = $pdo->query("SELECT id, name, description FROM categories ORDER BY id ASC");
$all_categories = $stmt_cat->fetchAll();

include_once '../includes/header.php';
include_once '../includes/navbar.php';
?>

<div class="google-wrapper">
    <?php include_once '../includes/user_sidebar.php'; ?>

    <main class="google-content">
        <div class="mb-4">
            <h3 class="fw-bold mb-1"><?php echo $lang['my_profile']; ?></h3>
        </div>

        <div class="google-card p-4 mx-auto" style="max-width: 800px;">
            <?php if(!empty($error)): ?>
                <div class="alert alert-danger rounded-4"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if(!empty($success)): ?>
                <div class="alert alert-success rounded-4 fw-bold"><i class="bi bi-check-circle-fill me-2"></i><?php echo $success; ?></div>
            <?php endif; ?>

            <form action="profile.php" method="POST" enctype="multipart/form-data" onsubmit="return confirm('<?php echo $lang['confirm_save_changes']; ?>');">
                
                <div class="d-flex align-items-center mb-4 pb-4 border-bottom">
                    <?php 
                        $img_src = (!empty($user['profile_image']) && $user['profile_image'] !== 'default.png') 
                            ? "../assets/images/avatars/" . $user['profile_image'] 
                            : "../assets/images/logo.png";
                    ?>
                    <img src="<?php echo htmlspecialchars($img_src); ?>" id="profilePreview" alt="Profile" class="rounded-circle object-fit-cover shadow-sm border" style="width: 100px; height: 100px;">
                    <div class="ms-4">
                        <label class="form-label fw-bold d-block text-dark"><?php echo $lang['profile_photo']; ?></label>
                        <input class="form-control form-control-sm w-auto rounded-3" type="file" name="profile_image" id="profileInput" accept="image/*">
                        <small class="text-muted d-block mt-1"><?php echo $lang['max_size_note']; ?></small>
                    </div>
                </div>

                <h6 class="mb-3 text-primary fw-bold"><?php echo $lang['personal_info']; ?></h6>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-medium"><?php echo $lang['first_name']; ?> *</label>
                        <input type="text" name="first_name" class="form-control rounded-3" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium"><?php echo $lang['last_name']; ?> *</label>
                        <input type="text" name="last_name" class="form-control rounded-3" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-medium"><?php echo $lang['email_readonly']; ?></label>
                        <input type="email" class="form-control rounded-3 text-muted bg-light" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium"><?php echo $lang['phone_number']; ?></label>
                        <input type="text" name="phone" class="form-control rounded-3" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" dir="ltr">
                    </div>
                </div>

                <h6 class="mb-3 text-primary fw-bold mt-4"><?php echo $lang['location_details']; ?></h6>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-medium"><?php echo $lang['country']; ?></label>
                        <input type="text" name="country" class="form-control rounded-3" value="<?php echo htmlspecialchars($user['country'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium"><?php echo $lang['city']; ?></label>
                        <input type="text" name="city" class="form-control rounded-3" value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>">
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-medium"><?php echo $lang['detailed_address']; ?></label>
                    <input type="text" name="address" class="form-control rounded-3" placeholder="<?php echo $lang['address_placeholder']; ?>" value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>">
                </div>

                <h6 class="mb-2 text-primary fw-bold mt-4"><?php echo $lang['my_interests']; ?></h6>
                <p class="text-muted small mb-3"><?php echo $lang['interests_hint']; ?></p>
                
                <div class="row g-3 mb-4">
                    <?php foreach($all_categories as $cat): ?>
                        <?php $is_checked = in_array($cat['id'], $current_interests) ? 'checked' : ''; ?>
                        <div class="col-md-6">
                            <div class="form-check border rounded-4 p-3 h-100 bg-light bg-opacity-50">
                                <input class="form-check-input ms-1 mt-1" type="checkbox" name="interests[]" value="<?php echo $cat['id']; ?>" id="cat_<?php echo $cat['id']; ?>" <?php echo $is_checked; ?>>
                                <label class="form-check-label ms-2 w-100" for="cat_<?php echo $cat['id']; ?>">
                                    <strong class="d-block mb-1 text-dark"><?php echo htmlspecialchars($cat['name']); ?></strong>
                                    <span class="text-muted small" style="line-height: 1.4; display: block;"><?php echo htmlspecialchars($cat['description']); ?></span>
                                </label>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="pt-3 border-top">
                    <button type="submit" class="btn btn-primary w-100 py-3 fw-bold rounded-pill"><?php echo $lang['save_changes']; ?></button>
                </div>
            </form>
        </div>
    </main>
</div>

<script>
// سكربت معاينة الصورة وفحص الحجم قبل الرفع
document.getElementById('profileInput').addEventListener('change', function(event) {
    const file = event.target.files[0];
    if (file) {
        if (file.size > 5242880) { // 5MB
            alert('File is too large. Maximum size is 5MB.');
            this.value = '';
            return;
        }
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('profilePreview').src = e.target.result;
        }
        reader.readAsDataURL(file);
    }
});
</script>

<?php include_once '../includes/footer.php'; ?>