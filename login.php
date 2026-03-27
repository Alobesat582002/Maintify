<?php
require_once 'config/db.php';

// توجيه المستخدم إذا كان مسجل الدخول مسبقاً بناءً على صلاحيته
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: Admin/dashboard.php");
    } elseif ($_SESSION['role'] === 'technician') {
        header("Location: Technician/dashboard.php");
    } else {
        header("Location: Homeowner/dashboard.php");
    }
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            
            // 1. فحص حالة الحساب (نظام الإيقاف والشكاوي)
            $can_login = true;
            
            if ($user['status'] === 'suspended') {
                if ($user['suspended_until'] !== null && strtotime($user['suspended_until']) > time()) {
                    // الحساب لا يزال موقوفاً
                    $error = "Your account is suspended until: " . date('Y-m-d h:i A', strtotime($user['suspended_until'])) . ". Contact support for more details.";
                    $can_login = false;
                } else {
                    // انتهت مدة الإيقاف، تفعيل الحساب برمجياً
                    $update_stmt = $pdo->prepare("UPDATE users SET status = 'active', suspended_until = NULL WHERE id = ?");
                    $update_stmt->execute([$user['id']]);
                    $user['status'] = 'active'; 
                }
            }

            // 2. إكمال تسجيل الدخول إذا لم يكن الحساب موقوفاً
            if ($can_login) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name'] = $user['first_name'] . ' ' . $user['last_name'];
                $_SESSION['role'] = $user['role'];

                // التوجيه
                if ($user['role'] === 'admin') {
                    header("Location: Admin/dashboard.php");
                } elseif ($user['role'] === 'technician') {
                    header("Location: Technician/dashboard.php");
                } else {
                    header("Location: Homeowner/dashboard.php");
                }
                exit();
            }

        } else {
            $error = "Invalid email or password.";
        }
    }
}

include_once 'includes/header.php';
include_once 'includes/navbar.php';
?>

<div class="container mt-5" style="min-height: 70vh;">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header bg-dark text-white rounded-top-4 py-3">
                    <h4 class="mb-0 text-center fw-bold">Login</h4>
                </div>
                <div class="card-body p-4">
                    <?php if(!empty($error)): ?>
                        <div class="alert alert-danger text-center fw-bold"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form action="login.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Email Address</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">Login</button>
                    </form>
                    
                    <div class="mt-4 text-center">
                        <p class="text-muted">Don't have an account? <a href="register.php" class="text-decoration-none fw-bold">Register here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>