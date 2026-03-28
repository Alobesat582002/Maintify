<?php
require_once 'config/db.php';

// ── Redirect if already logged in ──────────────────────────────────────────
if (isset($_SESSION['user_id'])) {
  $dest = match ($_SESSION['role']) {
    'admin'      => 'Admin/dashboard.php',
    'technician' => 'Technician/dashboard.php',
    default      => 'Homeowner/dashboard.php',
  };
  header("Location: $dest");
  exit();
}

// ── Handle login form submission ────────────────────────────────────────────
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email    = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';

  if (!$email || !$password) {
    $error = "Please fill in all fields.";
  } else {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {

      // Lift expired suspension automatically
      if ($user['status'] === 'suspended') {
        if ($user['suspended_until'] && strtotime($user['suspended_until']) > time()) {
          $error = "Your account is suspended until "
            . date('Y-m-d h:i A', strtotime($user['suspended_until']))
            . ". Contact support for help.";
        } else {
          $pdo->prepare("UPDATE users SET status='active', suspended_until=NULL WHERE id=?")
            ->execute([$user['id']]);
          $user['status'] = 'active';
        }
      }

      if (!$error) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name']    = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['role']    = $user['role'];

        $dest = match ($user['role']) {
          'admin'      => 'Admin/dashboard.php',
          'technician' => 'Technician/dashboard.php',
          default      => 'Homeowner/dashboard.php',
        };
        header("Location: $dest");
        exit();
      }
    } else {
      $error = "Invalid email or password.";
    }
  }
}

// ── View ────────────────────────────────────────────────────────────────────
include_once 'includes/header.php';

?>

<link rel="stylesheet" href="assets/css/auth.css">


<div class="auth-wrap">
  <div class="glass-card">

    <a href="index.php" class="logo-wrap" style="text-decoration: none;">
      <img src="assets/images/logo.png" alt="Maintify" class="logo-img-auth">
      <h1 class="logo-name"><span class="maint-text">Maint</span><span class="ify-text">ify</span></h1>
    </a>
    
    <p class="card-sub">Sign in to your Maintify account</p>

    <?php if ($error): ?>
      <div class="alert-err"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form action="login.php" method="POST" autocomplete="on">

      <div class="form-group">
        <label class="form-label">Email Address</label>
        <div class="input-wrap">
          <span class="input-icon">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="2" y="4" width="20" height="16" rx="2" />
              <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" />
            </svg>
          </span>
          <input type="email" name="email" class="form-input"
            placeholder="you@example.com" required
            value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Password</label>
        <div class="input-wrap">
          <span class="input-icon">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="3" y="11" width="18" height="11" rx="2" />
              <path d="M7 11V7a5 5 0 0 1 10 0v4" />
            </svg>
          </span>
          <input type="password" name="password" id="pwField"
            class="form-input" placeholder="••••••••" required>
          <button type="button" class="pw-toggle" id="pwToggle" onclick="togglePw()">
            <svg id="eyeShow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7z" />
              <circle cx="12" cy="12" r="3" />
            </svg>
            <svg id="eyeHide" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none">
              <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24" />
              <line x1="1" y1="1" x2="23" y2="23" />
            </svg>
          </button>
        </div>
        <div class="forgot-row">
          <a href="forgot_password.php" class="forgot-link">Forgot password?</a>
        </div>
      </div>

      <button type="submit" class="btn-primary">Sign In →</button>
    </form>

    <div class="divider">or continue with</div>

    <div class="social-grid">
      <a href="auth/google.php" class="btn-social">
        <svg class="social-icon" viewBox="0 0 24 24">
          <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57C21.36 18.25 22.56 15.43 22.56 12.25z" fill="#4285F4" />
          <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853" />
          <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05" />
          <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335" />
        </svg>
        Google
      </a>
      <a href="auth/facebook.php" class="btn-social">
        <svg class="social-icon" viewBox="0 0 24 24">
          <path d="M24 12.073C24 5.405 18.627 0 12 0S0 5.405 0 12.073c0 6.027 4.388 11.021 10.125 11.927v-8.437H7.078v-3.49h3.047V9.41c0-3.025 1.792-4.697 4.533-4.697 1.312 0 2.686.236 2.686.236v2.97h-1.513c-1.491 0-1.956.93-1.956 1.883v2.258h3.328l-.532 3.49h-2.796V24C19.612 23.094 24 18.1 24 12.073z" fill="#1877F2" />
        </svg>
        Facebook
      </a>
    </div>

    <div class="bottom-row">
      Don't have an account? <a href="register.php">Register here</a>
    </div>

  </div>
</div>

<script>
  function togglePw() {
    const f = document.getElementById('pwField');
    const show = document.getElementById('eyeShow');
    const hide = document.getElementById('eyeHide');
    const isHidden = f.type === 'password';
    f.type = isHidden ? 'text' : 'password';
    show.style.display = isHidden ? 'none' : '';
    hide.style.display = isHidden ? '' : 'none';
  }
</script>

<?php include_once 'includes/footer.php'; ?>