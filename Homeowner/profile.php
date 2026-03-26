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
    
    // جلب الاهتمامات والتأكد أنها مصفوفة
    $interests = isset($_POST['interests']) && is_array($_POST['interests']) ? $_POST['interests'] : [];

    if (empty($first_name) || empty($last_name)) {
        $error = "First name and last name are required.";
    } else {
        try {
            $pdo->beginTransaction();

            // أ. تحديث بيانات المستخدم في جدول users
            $sql = "UPDATE users SET first_name = ?, last_name = ?, phone = ?, country = ?, city = ?, address = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$first_name, $last_name, $phone, $country, $city, $address, $user_id]);

            // ب. تحديث الاهتمامات: حذف القديم أولاً ثم إدخال الجديد
            $stmt_delete = $pdo->prepare("DELETE FROM user_interests WHERE user_id = ?");
            $stmt_delete->execute([$user_id]);

            if (!empty($interests)) {
                $stmt_insert = $pdo->prepare("INSERT INTO user_interests (user_id, category_id) VALUES (?, ?)");
                foreach ($interests as $cat_id) {
                    $stmt_insert->execute([$user_id, $cat_id]);
                }
            }

            $pdo->commit();
            
            // تحديث الاسم في الجلسة لكي يتغير في الـ Navbar فوراً
            $_SESSION['name'] = $first_name . ' ' . $last_name;
            $success = "Profile updated successfully!";
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Failed to update profile: " . $e->getMessage();
        }
    }
}

// 2. جلب بيانات المستخدم الحالية لتعبئة الفورم
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// 3. جلب اهتمامات المستخدم الحالية كـ (مصفوفة من الأرقام) لتحديد الـ Checkboxes
$stmt_int = $pdo->prepare("SELECT category_id FROM user_interests WHERE user_id = ?");
$stmt_int->execute([$user_id]);
// جلب كعمود واحد فقط لتسهيل البحث لاحقاً
$current_interests = $stmt_int->fetchAll(PDO::FETCH_COLUMN);

// 4. جلب جميع الأقسام المتاحة
$stmt_cat = $pdo->query("SELECT id, name, description FROM categories ORDER BY id ASC");
$all_categories = $stmt_cat->fetchAll();

include_once '../includes/header.php';
include_once '../includes/navbar.php';
?>

<div class="container mt-5" style="min-height: 70vh;">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>My Profile</h2>
                <a href="dashboard.php" class="btn btn-outline-secondary">← Back to Dashboard</a>
            </div>

            <div class="card shadow-sm border-0 mb-5">
                <div class="card-body p-4">
                    <?php if(!empty($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <?php if(!empty($success)): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>

                    <form action="profile.php" method="POST">
                        <h5 class="mb-3 text-primary fw-bold">Personal Information</h5>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">First Name *</label>
                                <input type="text" name="first_name" class="form-control" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Last Name *</label>
                                <input type="text" name="last_name" class="form-control" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Email Address (Read Only)</label>
                                <input type="email" class="form-control text-muted" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone Number</label>
                                <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                            </div>
                        </div>

                        <h5 class="mb-3 text-primary fw-bold">Location Details</h5>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Country</label>
                                <input type="text" name="country" class="form-control" value="<?php echo htmlspecialchars($user['country'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">City</label>
                                <input type="text" name="city" class="form-control" value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Detailed Address</label>
                            <input type="text" name="address" class="form-control" placeholder="Street name, Building number..." value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>">
                        </div>

                        <h5 class="mb-3 text-primary fw-bold">My Interests (Smart Feed Preferences)</h5>
                        <p class="text-muted small mb-3">Select the services you are most interested in. We will use this to show you relevant technicians.</p>
                        
                        <div class="row g-3 mb-4">
                            <?php foreach($all_categories as $cat): ?>
                                <?php 
                                    // فحص ما إذا كان القسم الحالي موجوداً في مصفوفة اهتمامات المستخدم
                                    $is_checked = in_array($cat['id'], $current_interests) ? 'checked' : ''; 
                                ?>
                                <div class="col-md-6">
                                    <div class="form-check border rounded p-2">
                                        <input class="form-check-input ms-1" type="checkbox" name="interests[]" value="<?php echo $cat['id']; ?>" id="cat_<?php echo $cat['id']; ?>" <?php echo $is_checked; ?>>
                                        <label class="form-check-label ms-2" for="cat_<?php echo $cat['id']; ?>">
                                            <strong><?php echo htmlspecialchars($cat['name']); ?></strong><br>
                                            <span class="text-muted small"><?php echo htmlspecialchars($cat['description']); ?></span>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>