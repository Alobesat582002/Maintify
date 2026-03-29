<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once 'vendor/autoload.php';
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
        $error = $lang['invalid_email'] ?? "Please enter a valid email address.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email=? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            $error = $lang['account_not_found'] ?? "No account found with that email.";
        } else {
            $otp     = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $expires = date('Y-m-d H:i:s', time() + 1800); // 30 دقيقة

            $pdo->prepare("
                INSERT INTO password_resets (email, otp, expires_at, used)
                VALUES (?, ?, ?, 0)
                ON DUPLICATE KEY UPDATE otp=?, expires_at=?, used=0
            ")->execute([$email, $otp, $expires, $otp, $expires]);

           
            // ================== إعدادات PHPMailer ==================
            $mail = new PHPMailer(true);

            try {
                // إعدادات السيرفر (SMTP)
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'aanalshikh@gmail.com';     
                $mail->Password   = 'rdup oyux pidu stjy';         
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port       = 465;
                $mail->CharSet    = 'UTF-8';

                // إعدادات المرسل والمستقبل
                $mail->setFrom('your_email@gmail.com', 'Maintify Support'); 
                $mail->addAddress($email);

                
                $mail->isHTML(true);
                $mail->Subject = 'Maintify - Password Reset Code';
                $mail->Body    = "
                    <div style='font-family: Arial, sans-serif; padding: 20px; background-color: #0f172a; color: white; text-align: center; border-radius: 10px;'>
                        <h2 style='color: #f8fafc;'>Password Reset Request</h2>
                        <p style='color: #94a3b8; font-size: 16px;'>You requested to reset your password. Please use the following code:</p>
                        <div style='background-color: #1e293b; padding: 15px; margin: 20px auto; border-radius: 8px; border: 1px solid #334155; display: inline-block;'>
                            <h1 style='color: #F36F21; letter-spacing: 5px; margin: 0;'>$otp</h1>
                        </div>
                        <p style='color: #64748b; font-size: 14px;'>This code is valid for 30 minutes. If you didn't request this, safely ignore this email.</p>
                    </div>
                ";

                $mail->send();

                // إذا نجح الإرسال، ننتقل للخطوة التالية
                $_SESSION['reset_email'] = $email;
                $_SESSION['otp_attempts'] = 0;
                $success = $lang['code_sent'] ?? "Verification code has been sent to your email.";
                $step = STEP_OTP;

            } catch (Exception $e) {
                // في حال فشل الإرسال
                $error = "عذراً، فشل إرسال البريد الإلكتروني. تفاصيل الخطأ: {$mail->ErrorInfo}";
            }
            // ========================================================
        }
    }
}

// ================== STEP 2: OTP ==================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['step_otp'])) {
    $_SESSION['otp_attempts'] = ($_SESSION['otp_attempts'] ?? 0) + 1;

    if ($_SESSION['otp_attempts'] > 5) {
        die("Too many attempts. Please try again later.");
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
        $error = $lang['invalid_code'] ?? "Invalid verification code.";
        $step  = STEP_OTP;
    } elseif (strtotime($row['expires_at']) < time()) {
        $error = $lang['expired_code'] ?? "Code has expired. Please request a new one.";
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
        $error = $lang['weak_password'] ?? "Password must be at least 6 characters.";
        $step  = STEP_RESET;
    } elseif ($pw !== $_POST['confirm_password']) {
        $error = $lang['password_mismatch'] ?? "Passwords do not match.";
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
<html lang="<?php echo $current_lang ?? 'en'; ?>" dir="<?php echo ($current_lang ?? 'en') === 'ar' ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $lang['forgot_password'] ?? 'Reset Password'; ?> - Maintify</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <link rel="stylesheet" href="assets/css/auth.css?v=<?php echo time(); ?>">

    <style>
        
        .alert-success-custom {
            background: rgba(34, 197, 94, 0.1);
            border: 1px solid rgba(34, 197, 94, 0.25);
            color: #86efac;
            border-radius: 10px;
            padding: .75rem 1rem;
            font-size: .875rem;
            margin-bottom: 1.4rem;
            display: flex;
            align-items: center;
            gap: .5rem;
        }
        .otp-input {
            letter-spacing: 8px;
            font-weight: 800;
            text-align: center;
            padding-left: 0 !important;
            font-size: 1.2rem;
        }
    </style>
</head>
<body>

<div class="auth-wrap">
    <div class="glass-card">
        
        <a href="index.php" class="logo-wrap" style="text-decoration: none;">
            <img src="assets/images/logo.png" alt="Maintify" class="logo-img-auth">
            <h2 class="logo-name"><span class="maint-text">Maint</span><span class="ify-text">ify</span></h2>
        </a>

        <?php if($error): ?>
            <div class="alert-err"><i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if($success): ?>
            <div class="alert-success-custom"><i class="bi bi-check-circle-fill"></i> <?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if($step === STEP_EMAIL): ?>
            <h1 class="card-title"><?php echo $lang['forgot_password'] ?? 'Forgot Password?'; ?></h1>
            <p class="card-sub"><?php echo $lang['forgot_password_desc'] ?? 'No worries, we\'ll send you reset instructions.'; ?></p>
            
            <form method="POST">
                <input type="hidden" name="step_email">
                <div class="form-group">
                    <label class="form-label"><?php echo $lang['email_address'] ?? 'Email Address'; ?></label>
                    <div class="input-wrap">
                        <span class="input-icon"><i class="bi bi-envelope"></i></span>
                        <input type="email" name="email" class="form-input" placeholder="name@example.com" required autofocus>
                    </div>
                </div>
                <button type="submit" class="btn-primary">
                    <?php echo $lang['send_reset_link'] ?? 'Send Reset Code'; ?> <i class="bi bi-arrow-right ms-2"></i>
                </button>
            </form>

        <?php elseif($step === STEP_OTP): ?>
            <h1 class="card-title"><?php echo $lang['check_email'] ?? 'Check Your Email'; ?></h1>
            <p class="card-sub">
                <?php echo $lang['otp_sent_to'] ?? 'We sent a 6-digit code to'; ?> <strong><?= htmlspecialchars($_SESSION['reset_email'] ?? '') ?></strong>
            </p>
            
            <form method="POST">
                <input type="hidden" name="step_otp">
                <div class="form-group">
                    <label class="form-label"><?php echo $lang['verification_code'] ?? 'Verification Code'; ?></label>
                    <div class="input-wrap">
                        <span class="input-icon"><i class="bi bi-shield-lock"></i></span>
                        <input type="text" name="otp" class="form-input otp-input" placeholder="000000" required autofocus maxlength="6" pattern="\d{6}">
                    </div>
                </div>
                <button type="submit" class="btn-primary">
                    <?php echo $lang['verify_code'] ?? 'Verify Code'; ?> <i class="bi bi-check2-circle ms-2"></i>
                </button>
            </form>

        <?php elseif($step === STEP_RESET): ?>
            <h1 class="card-title"><?php echo $lang['set_new_password'] ?? 'Set New Password'; ?></h1>
            <p class="card-sub"><?php echo $lang['new_password_desc'] ?? 'Your new password must be at least 6 characters.'; ?></p>
            
            <form method="POST">
                <input type="hidden" name="step_reset">
                <div class="form-group">
                    <label class="form-label"><?php echo $lang['new_password'] ?? 'New Password'; ?></label>
                    <div class="input-wrap">
                        <span class="input-icon"><i class="bi bi-lock"></i></span>
                        <input type="password" name="password" class="form-input" placeholder="••••••••" required minlength="6">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label"><?php echo $lang['confirm_password'] ?? 'Confirm Password'; ?></label>
                    <div class="input-wrap">
                        <span class="input-icon"><i class="bi bi-lock-fill"></i></span>
                        <input type="password" name="confirm_password" class="form-input" placeholder="••••••••" required minlength="6">
                    </div>
                </div>
                <button type="submit" class="btn-primary">
                    <?php echo $lang['update_password'] ?? 'Update Password'; ?> <i class="bi bi-arrow-repeat ms-2"></i>
                </button>
            </form>

        <?php else: ?>
            <div class="text-center w-100" style="text-align: center;">
                <i class="bi bi-check-circle-fill" style="font-size: 4.5rem; color: #22c55e; margin-bottom: 1rem; display: inline-block;"></i>
                <h1 class="card-title"><?php echo $lang['all_done'] ?? 'All Done!'; ?></h1>
                <p class="card-sub"><?php echo $lang['password_updated'] ?? 'Your password has been successfully updated.'; ?></p>
                <a href="login.php" class="btn-primary" style="text-decoration: none;">
                    <?php echo $lang['back_to_login'] ?? 'Back to Login'; ?> <i class="bi bi-box-arrow-in-right ms-2"></i>
                </a>
            </div>
        <?php endif; ?>

        <?php if($step !== STEP_DONE): ?>
            <div class="bottom-row">
                <?php echo $lang['remember_password'] ?? 'Remember your password?'; ?> <a href="login.php"><?php echo $lang['login'] ?? 'Log in'; ?></a>
            </div>
        <?php endif; ?>

    </div>
</div>

</body>
</html>