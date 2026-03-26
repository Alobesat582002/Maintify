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
            // حفظ بيانات المستخدم في الجلسة (لاحظ دمج الاسم الأول والأخير)
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['role'] = $user['role'];

            
            if ($user['role'] === 'admin') {
                header("Location: Admin/dashboard.php");
            } elseif ($user['role'] === 'technician') {
                header("Location: Technician/dashboard.php");
            } else {
                header("Location: Homeowner/dashboard.php");
            }
            exit();
        } else {
            $error = "Invalid email or password.";
        }
    }
}

include_once 'includes/header.php';
include_once 'includes/navbar.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h4 class="mb-0">Login</h4>
                </div>
                <div class="card-body">
                    <?php if(!empty($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form action="login.php" method="POST">
                        <div class="mb-3">
                            <label>Email Address</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Login</button>
                    </form>
                    <div class="mt-3 text-center">
                        <p>Don't have an account? <a href="register.php">Register here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>