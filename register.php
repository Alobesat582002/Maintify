<?php
require_once 'config/db.php';

if (isset($_SESSION['user_id'])) {
  header("Location: index.php");
  exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['final_submit'])) {
  $first_name   = trim($_POST['first_name']);
  $last_name    = trim($_POST['last_name']);
  $email        = trim($_POST['email']);
  $password     = $_POST['password'];
  $phone        = trim($_POST['phone']);
  $role         = $_POST['role'];
  $country      = trim($_POST['country']);
  $city         = trim($_POST['city']);

  $interests_str = trim($_POST['interests'] ?? '');
  $interests     = !empty($interests_str) ? explode(',', $interests_str) : [];

  $specialty    = trim($_POST['specialty'] ?? '');
  $experience   = trim($_POST['experience'] ?? 0);

  if (empty($first_name) || empty($last_name) || empty($email) || empty($password) || empty($role)) {
    $error = "Please fill all required fields.";
  } else {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->rowCount() > 0) {
      $error = "This email is already registered!";
    } else {
      try {
        $pdo->beginTransaction();

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (first_name, last_name, email, phone, password, role, country, city) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$first_name, $last_name, $email, $phone, $hashed_password, $role, $country, $city]);

        $user_id = $pdo->lastInsertId();

        if ($role === 'technician') {
          $stmt_tech = $pdo->prepare("INSERT INTO technician_profiles (user_id, bio, experience_years) VALUES (?, ?, ?)");
          $stmt_tech->execute([$user_id, $specialty, $experience]);
        }

        if ($role === 'homeowner' && !empty($interests)) {
          $stmt_int = $pdo->prepare("INSERT INTO user_interests (user_id, category_id) VALUES (?, ?)");
          foreach ($interests as $cat_id) {
            $stmt_int->execute([$user_id, $cat_id]);
          }
        }

        $pdo->commit();
        $success = "true";
      } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Database Error: " . $e->getMessage();
      }
    }
  }
}

$categories = [];
try {
  $stmt = $pdo->query("SELECT id, name FROM categories ORDER BY name");
  $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
}

include_once 'includes/header.php';
?>

<link rel="stylesheet" href="assets/css/auth.css">

<style>
  /* تعديلات بسيطة خاصة بصفحة التسجيل لتتوافق مع auth.css */
  .reg-card {
    max-width: 650px !important;
  }

  .step-panel {
    display: none;
    animation: fadeIn 0.3s ease;
  }

  .step-panel.active {
    display: block;
  }

  @keyframes fadeIn {
    from {
      opacity: 0;
      transform: translateY(10px);
    }

    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  /* Error Highlights */
  .form-input.is-invalid {
    border-color: #ef4444 !important;
    background: rgba(239, 68, 68, 0.05);
  }

  .field-error {
    display: none;
    color: #fca5a5;
    font-size: 0.8rem;
    margin-top: 6px;
  }

  .field-error.show {
    display: block;
  }

  .field-error::before {
    content: '⚠ ';
  }

  /* Role & Category Cards */
  .selection-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    margin-bottom: 1.5rem;
  }

  .cat-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 10px;
    margin-bottom: 1.5rem;
  }

  .select-card {
    background: var(--input-bg);
    border: 1px solid var(--navy-border);
    border-radius: var(--radius);
    padding: 1.5rem 1rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s ease;
    color: var(--text-muted);
  }

  .select-card:hover {
    border-color: rgba(255, 255, 255, 0.2);
    color: var(--text);
  }

  .select-card.selected {
    border-color: var(--orange);
    background: rgba(249, 115, 22, 0.08);
    color: #fff;
    box-shadow: 0 0 15px rgba(249, 115, 22, 0.1);
  }

  .select-card.is-invalid {
    border-color: #ef4444;
  }

  .select-card input {
    display: none;
  }

  .select-card .icon {
    font-size: 2rem;
    margin-bottom: 0.5rem;
    display: block;
  }

  .select-card .title {
    font-size: 1rem;
    font-weight: 700;
    font-family: var(--font-head);
  }

  .select-card .desc {
    font-size: 0.8rem;
    margin-top: 0.5rem;
    opacity: 0.7;
  }

  .cat-item {
    padding: 0.8rem;
    font-size: 0.9rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .cat-item .icon {
    font-size: 1.2rem;
    margin: 0;
  }

  /* Buttons Row */
  .btn-row {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
  }

  .btn-row .btn-primary {
    margin-top: 0;
    flex: 2;
  }

  .btn-back {
    flex: 1;
    background: transparent;
    color: var(--text-muted);
    border: 1px solid var(--navy-border);
    border-radius: var(--radius);
    font-weight: 600;
    transition: 0.2s;
  }

  .btn-back:hover {
    background: rgba(255, 255, 255, 0.05);
    color: #fff;
  }

  /* Terms Box */
  .terms-box {
    background: var(--input-bg);
    border: 1px solid var(--navy-border);
    border-radius: var(--radius);
    padding: 1rem;
    font-size: 0.8rem;
    color: var(--text-muted);
    max-height: 100px;
    overflow-y: auto;
    margin-bottom: 1rem;
    line-height: 1.6;
  }

  .terms-check {
    font-size: 0.9rem;
    font-weight: 500;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 10px;
    color: var(--text);
  }

  .terms-check input {
    width: 16px;
    height: 16px;
    accent-color: var(--orange);
  }

  /* Progress Indicators */
  .step-indicator {
    display: flex;
    gap: 5px;
    margin-bottom: 2rem;
    justify-content: center;
  }

  .step-dot {
    height: 4px;
    width: 30px;
    background: var(--navy-border);
    border-radius: 2px;
    transition: 0.3s;
  }

  .step-dot.active {
    background: var(--orange);
    width: 40px;
    box-shadow: 0 0 8px var(--orange-glow);
  }

  /* Success Screen */
  .success-screen {
    text-align: center;
    display: none;
  }

  .success-screen.show {
    display: block;
    animation: fadeIn 0.5s ease;
  }

  .success-icon {
    font-size: 4rem;
    color: var(--orange);
    margin-bottom: 1rem;
    text-shadow: 0 0 20px var(--orange-glow);
  }
</style>

<div class="auth-wrap">
  <div class="glass-card reg-card">

    <a href="index.php" class="logo-wrap" style="text-decoration: none;">
      <img src="assets/images/logo.png" alt="Maintify" class="logo-img-auth">
      <h1 class="logo-name"><span class="maint-text">Maint</span><span class="ify-text">ify</span></h1>
    </a>

    <?php if (!empty($error)): ?>
      <div class="alert-err"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="success-screen <?php echo $success === 'true' ? 'show' : ''; ?>">
      <div class="success-icon"><i class="bi bi-check-circle-fill"></i></div>
      <h2 class="card-title mb-2">Account Created!</h2>
      <p class="card-sub mb-4">Welcome to Maintify. You can now log in and start your journey.</p>
      <a href="login.php" class="btn-primary">Go to Login <i class="bi bi-arrow-right ms-2"></i></a>
    </div>

    <form action="register.php" method="POST" id="regForm" <?php echo $success === 'true' ? 'style="display:none"' : ''; ?>>
      <input type="hidden" name="final_submit" value="1">
      <input type="hidden" name="role" id="roleInput" value="">
      <input type="hidden" name="interests" id="interestsInput" value="">
      <input type="hidden" name="specialty" id="specialtyInput" value="">

      <div class="step-indicator" id="stepIndicators">
        <div class="step-dot active" id="ind1"></div>
        <div class="step-dot" id="ind2"></div>
        <div class="step-dot" id="ind3"></div>
        <div class="step-dot" id="ind4"></div>
      </div>

      <div class="step-panel active" id="step1">
        <h2 class="card-title">Create Account</h2>
        <p class="card-sub">Let's start with your basic information.</p>

        <div class="row g-3">
          <div class="col-md-6 form-group mb-0">
            <label class="form-label">First Name</label>
            <input type="text" name="first_name" class="form-input" placeholder="John">
            <div class="field-error" id="err-first_name">First name required</div>
          </div>
          <div class="col-md-6 form-group mb-0">
            <label class="form-label">Last Name</label>
            <input type="text" name="last_name" class="form-input" placeholder="Doe">
            <div class="field-error" id="err-last_name">Last name required</div>
          </div>
        </div>

        <div class="form-group mt-3">
          <label class="form-label">Email Address</label>
          <input type="email" name="email" class="form-input" placeholder="you@example.com">
          <div class="field-error" id="err-email">Valid email required</div>
        </div>

        <div class="row g-3 mt-0">
          <div class="col-md-6 form-group mb-0">
            <label class="form-label">Password</label>
            <input type="password" name="password" id="pwInput" class="form-input" placeholder="Min 6 chars">
            <div class="field-error" id="err-password">Min 6 characters</div>
          </div>
          <div class="col-md-6 form-group mb-0">
            <label class="form-label">Confirm Password</label>
            <input type="password" id="confirmPw" class="form-input" placeholder="Repeat password">
            <div class="field-error" id="err-confirmPw">Passwords don't match</div>
          </div>
        </div>

        <div class="row g-3 mt-0">
          <div class="col-md-6 form-group mb-0">
            <label class="form-label">Age</label>
            <input type="number" name="age" class="form-input" placeholder="e.g. 25" min="16" max="100">
            <div class="field-error" id="err-age">Valid age (16-100)</div>
          </div>
          <div class="col-md-6 form-group mb-0">
            <label class="form-label">Phone Number</label>
            <input type="tel" name="phone" class="form-input" placeholder="07XXXXXXXX" dir="ltr">
            <div class="field-error" id="err-phone">Valid JD number</div>
          </div>
        </div>

        <button type="button" class="btn-primary w-100 mt-4" onclick="goNext(1)">Continue <i class="bi bi-arrow-right"></i></button>

        <div class="bottom-row">
          Already have an account? <a href="login.php">Sign In</a>
        </div>
      </div>

      <div class="step-panel" id="step2">
        <h2 class="card-title">Choose Your Role</h2>
        <p class="card-sub">How do you want to use Maintify?</p>

        <div class="selection-grid">
          <label class="select-card" id="roleHomeowner" onclick="selectRole('homeowner')">
            <i class="bi bi-house-door icon"></i>
            <div class="title">Homeowner</div>
            <div class="desc">I need maintenance services</div>
          </label>
          <label class="select-card" id="roleTechnician" onclick="selectRole('technician')">
            <i class="bi bi-tools icon"></i>
            <div class="title">Technician</div>
            <div class="desc">I offer professional services</div>
          </label>
        </div>
        <div class="field-error text-center mb-3" id="err-role">Please select a role</div>

        <div class="btn-row">
          <button type="button" class="btn-back" onclick="goBack(2)">Back</button>
          <button type="button" class="btn-primary" onclick="goNext(2)">Continue <i class="bi bi-arrow-right"></i></button>
        </div>
      </div>

      <div class="step-panel" id="step3">
        <h2 class="card-title" id="step3Title">Interests</h2>
        <p class="card-sub" id="step3Sub">Select categories</p>

        <div class="cat-grid" id="catGridHome" style="display:none;">
          <?php foreach ($categories as $cat): ?>
            <label class="select-card cat-item" onclick="toggleCat(this)">
              <input type="checkbox" value="<?php echo $cat['id']; ?>">
              <i class="bi bi-check2-square icon"></i> <?php echo htmlspecialchars($cat['name']); ?>
            </label>
          <?php endforeach; ?>
        </div>

        <div class="cat-grid" id="catGridTech" style="display:none;">
          <?php foreach ($categories as $cat): ?>
            <label class="select-card cat-item" onclick="selectSpecialty(this, '<?php echo $cat['id']; ?>')">
              <i class="bi bi-star icon"></i> <?php echo htmlspecialchars($cat['name']); ?>
            </label>
          <?php endforeach; ?>
        </div>
        <div class="field-error text-center mb-3" id="err-specialty">Please select your specialty</div>

        <div class="btn-row">
          <button type="button" class="btn-back" onclick="goBack(3)">Back</button>
          <button type="button" class="btn-primary" onclick="goNext(3)">Continue <i class="bi bi-arrow-right"></i></button>
        </div>
      </div>

      <div class="step-panel" id="step4">
        <h2 class="card-title">Final Details</h2>
        <p class="card-sub">Almost there! Where are you located?</p>

        <div class="row g-3 mb-3">
          <div class="col-md-6 form-group mb-0">
            <label class="form-label">Country</label>
            <input type="text" name="country" class="form-input" placeholder="Jordan">
          </div>
          <div class="col-md-6 form-group mb-0">
            <label class="form-label">City</label>
            <input type="text" name="city" class="form-input" placeholder="Amman">
          </div>
        </div>

        <div class="form-group mb-4" id="techExpDiv" style="display:none;">
          <label class="form-label">Years of Experience: <span id="expVal" class="text-white ms-2 fs-5">5</span></label>
          <input type="range" name="experience" min="1" max="30" value="5" class="w-100" style="accent-color: var(--orange);" oninput="document.getElementById('expVal').textContent = this.value">
        </div>

        <div class="terms-box">
          By joining Maintify, you agree to our Terms of Service and Privacy Policy. We collect data to connect users securely. Ensure all provided information is accurate.
        </div>
        <label class="terms-check">
          <input type="checkbox" id="termsCheck"> I agree to the Terms & Conditions
        </label>
        <div class="field-error" id="err-terms">You must accept the Terms</div>

        <div class="btn-row">
          <button type="button" class="btn-back" onclick="goBack(4)">Back</button>
          <button type="button" class="btn-primary" onclick="submitForm()">Create Account <i class="bi bi-check-lg ms-1"></i></button>
        </div>
      </div>

    </form>
  </div>
</div>

<script>
  let currentStep = 1;
  let selectedRole = '';
  let selectedSpecialty = '';

  // Helper Functions
  const el = id => document.getElementById(id);
  const q = sel => document.querySelector(sel);
  const qAll = sel => document.querySelectorAll(sel);

  function showError(id, msg) {
    const err = el('err-' + id);
    if (err) {
      err.textContent = msg;
      err.classList.add('show');
    }
  }

  function clearErrors() {
    qAll('.field-error').forEach(e => e.classList.remove('show'));
    qAll('.is-invalid').forEach(e => e.classList.remove('is-invalid'));
  }

  function markInvalid(input) {
    if (input) input.classList.add('is-invalid');
  }

  // Clear error on typing
  document.addEventListener('input', e => {
    if (e.target.classList.contains('is-invalid')) {
      e.target.classList.remove('is-invalid');
      const err = el('err-' + e.target.name);
      if (err) err.classList.remove('show');
    }
  });

  function updateIndicators(step) {
    for (let i = 1; i <= 4; i++) {
      el('ind' + i).className = i <= step ? 'step-dot active' : 'step-dot';
    }
  }

  function showStep(step) {
    qAll('.step-panel').forEach(p => p.classList.remove('active'));
    el('step' + step).classList.add('active');
    updateIndicators(step);
  }

  // Validation Step 1
  function validateStep1() {
    clearErrors();
    let valid = true;
    const fn = q('[name="first_name"]'),
      ln = q('[name="last_name"]'),
      em = q('[name="email"]');
    const pw = el('pwInput'),
      cpw = el('confirmPw'),
      phone = q('[name="phone"]'),
      age = q('[name="age"]');

    if (!fn.value.trim()) {
      markInvalid(fn);
      showError('first_name', 'Required');
      valid = false;
    }
    if (!ln.value.trim()) {
      markInvalid(ln);
      showError('last_name', 'Required');
      valid = false;
    }
    if (!em.value.trim() || !em.value.includes('@')) {
      markInvalid(em);
      showError('email', 'Valid email required');
      valid = false;
    }
    if (pw.value.length < 6) {
      markInvalid(pw);
      showError('password', 'Min 6 chars');
      valid = false;
    }
    if (pw.value !== cpw.value || !cpw.value) {
      markInvalid(cpw);
      showError('confirmPw', 'Passwords do not match');
      valid = false;
    }

    if (!age.value || age.value < 16) {
      markInvalid(age);
      showError('age', 'Valid age required');
      valid = false;
    }
    const phoneRegex = /^(\+962|0)7[0-9]{8}$/;
    if (!phone.value || !phoneRegex.test(phone.value)) {
      markInvalid(phone);
      showError('phone', 'Valid JD number required');
      valid = false;
    }

    return valid;
  }

  // Navigation Functions
  function goNext(step) {
    if (step === 1) {
      if (!validateStep1()) return;
      currentStep = 2;
      showStep(2);
    } else if (step === 2) {
      if (!selectedRole) {
        el('roleHomeowner').classList.add('is-invalid');
        el('roleTechnician').classList.add('is-invalid');
        showError('role', 'Please select a role');
        return;
      }

      // Setup Step 3 based on role
      if (selectedRole === 'homeowner') {
        el('step3Title').textContent = 'What are you interested in?';
        el('step3Sub').textContent = 'Select categories you need help with';
        el('catGridHome').style.display = 'grid';
        el('catGridTech').style.display = 'none';
      } else {
        el('step3Title').textContent = 'What is your specialty?';
        el('step3Sub').textContent = 'Choose your main expertise';
        el('catGridHome').style.display = 'none';
        el('catGridTech').style.display = 'grid';
      }
      currentStep = 3;
      showStep(3);
    } else if (step === 3) {
      if (selectedRole === 'technician' && !selectedSpecialty) {
        showError('specialty', 'Please select your specialty');
        return;
      }

      // Setup Step 4 based on role
      el('techExpDiv').style.display = selectedRole === 'technician' ? 'block' : 'none';
      currentStep = 4;
      showStep(4);
    }
  }

  function goBack(step) {
    currentStep = step - 1;
    showStep(currentStep);
  }

  // Selections
  function selectRole(role) {
    selectedRole = role;
    el('roleInput').value = role;
    el('roleHomeowner').className = role === 'homeowner' ? 'select-card selected' : 'select-card';
    el('roleTechnician').className = role === 'technician' ? 'select-card selected' : 'select-card';
    clearErrors();
  }

  function toggleCat(element) {
    element.classList.toggle('selected');
    const cb = element.querySelector('input');
    cb.checked = !cb.checked;

    // Update hidden input
    const ids = Array.from(qAll('#catGridHome .select-card.selected input')).map(i => i.value);
    el('interestsInput').value = ids.join(',');
  }

  function selectSpecialty(element, val) {
    qAll('#catGridTech .select-card').forEach(e => e.classList.remove('selected'));
    element.classList.add('selected');
    selectedSpecialty = val;
    el('specialtyInput').value = val;
    clearErrors();
  }

  // Final Submit
  function submitForm() {
    if (!el('termsCheck').checked) {
      showError('terms', 'You must accept the Terms');
      return;
    }
    el('regForm').submit();
  }
</script>

<?php include_once 'includes/footer.php'; ?>