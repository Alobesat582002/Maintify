<?php
require_once 'config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// ================== CONSTANTS ==================
define('STEP_EMAIL', 'email');
define('STEP_OTP', 'otp');
define('STEP_RESET', 'reset');
define('STEP_DONE', 'done');

$step   = STEP_EMAIL;
$error  = '';
$success = '';

// ================== STEP 1: EMAIL ==================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['step_email'])) {

    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);

    if (!$email) {
        $error = "Enter a valid email.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email=? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            $error = "No account found.";
        } else {

            $otp     = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $expires = date('Y-m-d H:i:s', time() + 1800); // 30 دقيقة

            $pdo->prepare("
                INSERT INTO password_resets (email, otp, expires_at, used)
                VALUES (?, ?, ?, 0)
                ON DUPLICATE KEY UPDATE otp=?, expires_at=?, used=0
            ")->execute([$email, $otp, $expires, $otp, $expires]);

            mail($email, "Reset Code", "Your OTP: $otp");

            $_SESSION['reset_email'] = $email;
            $_SESSION['otp_attempts'] = 0;

            $success = "Verification code sent.";
            $step = STEP_OTP;
        }
    }
}

// ================== STEP 2: OTP ==================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['step_otp'])) {

    $_SESSION['otp_attempts'] = ($_SESSION['otp_attempts'] ?? 0) + 1;

    if ($_SESSION['otp_attempts'] > 5) {
        die("Too many attempts.");
    }

    $email = $_SESSION['reset_email'] ?? '';
    $otp   = trim($_POST['otp']);

    $stmt = $pdo->prepare("
        SELECT * FROM password_resets 
        WHERE email=? AND otp=? AND used=0
    ");
    $stmt->execute([$email, $otp]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        $error = "Invalid code.";
        $step  = STEP_OTP;

    } elseif (strtotime($row['expires_at']) < time()) {
        $error = "Code expired.";
        $step  = STEP_OTP;

    } else {
        $_SESSION['reset_verified'] = true;
        $step = STEP_RESET;
    }
}

// ================== STEP 3: RESET ==================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['step_reset'])) {

    $email = $_SESSION['reset_email'] ?? '';
    $pw    = $_POST['password'] ?? '';

    if (!$email || empty($_SESSION['reset_verified'])) {
        header("Location: forgot_password.php");
        exit();
    }

    if (strlen($pw) < 6) {
        $error = "Weak password.";
        $step  = STEP_RESET;

    } elseif ($pw !== $_POST['confirm_password']) {
        $error = "Passwords do not match.";
        $step  = STEP_RESET;

    } else {

        $pdo->prepare("UPDATE users SET password=? WHERE email=?")
            ->execute([password_hash($pw, PASSWORD_DEFAULT), $email]);

        $pdo->prepare("UPDATE password_resets SET used=1 WHERE email=?")
            ->execute([$email]);

        session_unset();
        session_destroy();

        $step = STEP_DONE;
    }
}
 
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Reset Password</title>

<style>
body {
  font-family:sans-serif;
  background:#0f172a;
  color:white;
  display:flex;
  justify-content:center;
  align-items:center;
  height:100vh;
}

.card {
  width:350px;
  padding:30px;
  background:#1e293b;
  border-radius:15px;
}

input, button {
  width:100%;
  padding:10px;
  margin-top:10px;
  border:none;
  border-radius:8px;
}

button {
  background:#6366f1;
  color:white;
}

.error {color:red;}
.success {color:lime;}
</style>
</head>

<body>

<div class="card">

<?php if($error): ?><div class="error"><?= $error ?></div><?php endif; ?>
<?php if($success): ?><div class="success"><?= $success ?></div><?php endif; ?>

<?php if($step === STEP_EMAIL): ?>

<h2>Forgot Password</h2>
<form method="POST">
<input type="hidden" name="step_email">
<input type="email" name="email" placeholder="Email" required>
<button>Send Code</button>
</form>

<?php elseif($step === STEP_OTP): ?>

<h2>Enter Code</h2>
<form method="POST">
<input type="hidden" name="step_otp">
<input type="text" name="otp" placeholder="6-digit code" required>
<button>Verify</button>
</form>

<?php elseif($step === STEP_RESET): ?>

<h2>New Password</h2>
<form method="POST">
<input type="hidden" name="step_reset">
<input type="password" name="password" placeholder="Password" required>
<input type="password" name="confirm_password" placeholder="Confirm Password" required>
<button>Reset</button>
</form>

<?php else: ?>

<h2>Done ✅</h2>
<p>Password updated</p>
<a href="login.php"><button>Login</button></a>

<?php endif; ?>

</div>

</body>
</html>