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
    $categories = [];
}

include_once 'includes/header.php';
include_once 'includes/navbar.php';
?>

<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">

<style>
  :root {
    --primary: #4f46e5;
    --primary-hover: #4338ca;
    --primary-light: #eef2ff;
    --success: #10b981;
    --error: #ef4444;
    --border: #e5e7eb;
    --border-error: #fca5a5;
    --text: #111827;
    --muted: #6b7280;
    --bg: #f8fafc;
  }

  body { font-family: 'DM Sans', sans-serif; background: var(--bg); }

  .reg-wrap {
    min-height: calc(100vh - 70px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 3rem 1rem;
  }

  .reg-card {
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 4px 32px rgba(0,0,0,0.08);
    width: 100%;
    max-width: 540px;
    overflow: hidden;
  }

  /* Progress bar */
  .reg-progress { padding: 1.5rem 2rem 0; }
  .progress-steps {
    display: flex;
    align-items: center;
    gap: 0;
    margin-bottom: 1.5rem;
  }
  .step-dot {
    width: 32px; height: 32px;
    border-radius: 50%;
    border: 2px solid var(--border);
    background: #fff;
    display: flex; align-items: center; justify-content: center;
    font-size: 13px; font-weight: 600; color: var(--muted);
    flex-shrink: 0;
    transition: all 0.3s ease;
    position: relative; z-index: 1;
  }
  .step-dot.active { border-color: var(--primary); background: var(--primary); color: #fff; }
  .step-dot.done { border-color: var(--success); background: var(--success); color: #fff; }
  .step-dot.done::after { content: '✓'; font-size: 14px; }
  .step-dot.done span { display: none; }
  .step-line {
    flex: 1; height: 2px;
    background: var(--border);
    transition: background 0.3s ease;
  }
  .step-line.done { background: var(--success); }

  /* Step panels */
  .reg-body { padding: 0 2rem 2rem; }
  .step-panel { display: none; }
  .step-panel.active { display: block; }

  .step-title { font-size: 20px; font-weight: 600; color: var(--text); margin: 1.5rem 0 0.25rem; }
  .step-subtitle { font-size: 14px; color: var(--muted); margin-bottom: 1.5rem; }

  /* Form fields */
  .form-label { font-size: 13px; font-weight: 500; color: var(--text); margin-bottom: 5px; display: block; }
  .form-control, .form-select {
    border: 1.5px solid var(--border);
    border-radius: 10px;
    padding: 10px 14px;
    font-size: 14px;
    font-family: 'DM Sans', sans-serif;
    transition: border-color 0.2s;
    width: 100%;
  }
  .form-control:focus, .form-select:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(79,70,229,0.1);
    outline: none;
  }
  .form-control.is-invalid,
  .form-select.is-invalid {
    border-color: var(--border-error) !important;
    background: #fff5f5;
  }
  .form-control.is-invalid:focus,
  .form-select.is-invalid:focus {
    border-color: var(--error) !important;
    box-shadow: 0 0 0 3px rgba(239,68,68,0.1);
  }

  /* Inline field error */
  .field-error {
    display: none;
    align-items: center;
    gap: 5px;
    font-size: 12px;
    color: var(--error);
    margin-top: 5px;
    font-weight: 500;
    animation: fadeInDown 0.2s ease;
  }
  .field-error.show { display: flex; }
  .field-error::before { content: '⚠'; font-size: 11px; }

  @keyframes fadeInDown {
    from { opacity: 0; transform: translateY(-4px); }
    to   { opacity: 1; transform: translateY(0); }
  }

  /* Role cards */
  .role-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 0.5rem; }
  .role-card {
    border: 2px solid var(--border);
    border-radius: 14px;
    padding: 1.25rem 1rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s ease;
    position: relative;
  }
  .role-card:hover { border-color: #a5b4fc; background: var(--primary-light); }
  .role-card.selected { border-color: var(--primary); background: var(--primary-light); }
  .role-card.is-invalid { border-color: var(--border-error) !important; background: #fff5f5; }
  .role-card input[type="radio"] { position: absolute; opacity: 0; width: 0; height: 0; }
  .role-icon { font-size: 32px; margin-bottom: 8px; }
  .role-label { font-size: 14px; font-weight: 600; color: var(--text); }
  .role-desc { font-size: 12px; color: var(--muted); margin-top: 3px; }

  /* Category checkboxes */
  .cat-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    gap: 10px;
    margin-bottom: 0.5rem;
  }
  .cat-item {
    border: 1.5px solid var(--border);
    border-radius: 10px;
    padding: 10px 12px;
    cursor: pointer;
    transition: all 0.18s;
    display: flex; align-items: center; gap: 8px;
    font-size: 13px; font-weight: 500; color: var(--text);
  }
  .cat-item:hover { border-color: #a5b4fc; background: var(--primary-light); }
  .cat-item.selected { border-color: var(--primary); background: var(--primary-light); color: var(--primary); }
  .cat-item input { display: none; }

  /* Experience slider */
  .exp-display { font-size: 28px; font-weight: 600; color: var(--primary); text-align: center; margin: 0.5rem 0; }
  .exp-display span { font-size: 14px; color: var(--muted); font-weight: 400; }
  input[type="range"] { width: 100%; accent-color: var(--primary); height: 6px; cursor: pointer; }

  /* Terms */
  .terms-box {
    background: var(--primary-light);
    border: 1.5px solid #c7d2fe;
    border-radius: 12px;
    padding: 1rem;
    font-size: 13px; color: var(--muted);
    max-height: 100px;
    overflow-y: auto;
    margin-bottom: 12px;
    line-height: 1.6;
  }
  .terms-check {
    display: flex; align-items: center; gap: 10px;
    font-size: 14px; font-weight: 500; color: var(--text);
    cursor: pointer;
  }
  .terms-check.is-invalid { color: var(--error); }
  .terms-check input { width: 18px; height: 18px; accent-color: var(--primary); cursor: pointer; }

  /* Buttons */
  .btn-next {
    background: var(--primary); color: #fff;
    border: none; border-radius: 10px;
    padding: 12px 28px; font-size: 15px; font-weight: 600;
    font-family: 'DM Sans', sans-serif;
    cursor: pointer; transition: all 0.2s;
    width: 100%; margin-top: 1rem;
  }
  .btn-next:hover { background: var(--primary-hover); transform: translateY(-1px); box-shadow: 0 4px 14px rgba(79,70,229,0.3); }
  .btn-back {
    background: none; border: 1.5px solid var(--border);
    border-radius: 10px; padding: 11px 20px;
    font-size: 14px; font-weight: 500; color: var(--muted);
    cursor: pointer; font-family: 'DM Sans', sans-serif;
    transition: all 0.2s; margin-top: 1rem;
  }
  .btn-back:hover { border-color: #9ca3af; color: var(--text); }
  .btn-row { display: flex; gap: 10px; }
  .btn-row .btn-next { flex: 1; margin-top: 1rem; }

  /* Success screen */
  .success-screen { text-align: center; padding: 2rem 2rem 2.5rem; display: none; }
  .success-screen.show { display: block; }
  .success-icon {
    width: 72px; height: 72px; background: #d1fae5; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 1.25rem; font-size: 32px;
  }
  .success-screen h3 { font-size: 22px; font-weight: 600; color: var(--text); margin-bottom: 8px; }
  .success-screen p { font-size: 14px; color: var(--muted); margin-bottom: 1.5rem; }
  .btn-login-link {
    display: inline-block; background: var(--primary); color: #fff;
    text-decoration: none; padding: 12px 32px; border-radius: 10px;
    font-weight: 600; font-size: 15px; transition: background 0.2s;
  }
  .btn-login-link:hover { background: var(--primary-hover); color: #fff; }

  .alert-danger {
    background: #fef2f2; border: 1px solid #fecaca;
    color: #dc2626; border-radius: 10px;
    padding: 10px 14px; font-size: 14px; margin-bottom: 1rem;
  }

  /* Password strength */
  .pw-strength { height: 4px; border-radius: 2px; margin-top: 6px; transition: all 0.3s; background: var(--border); }
  .pw-strength.weak { background: #ef4444; width: 33%; }
  .pw-strength.medium { background: #f59e0b; width: 66%; }
  .pw-strength.strong { background: var(--success); width: 100%; }
</style>

<div class="reg-wrap">
  <div class="reg-card" id="regCard">

    <!-- <div class="reg-progress" id="progressBar">
      <div class="progress-steps" id="progressSteps"></div>
    </div> -->

    <?php if (!empty($error)): ?>
    <div class="reg-body">
      <div class="alert-danger"><?php echo $error; ?></div>
    </div>
    <?php endif; ?>

    <div class="success-screen <?php echo $success === 'true' ? 'show' : ''; ?>" id="successScreen">
      <div class="success-icon">🎉</div>
      <h3>Account Created!</h3>
      <p>Your account has been successfully created.<br>You can now log in and get started.</p>
      <a href="login.php" class="btn-login-link">Go to Login</a>
    </div>

    <form action="register.php" method="POST" id="regForm" <?php echo $success === 'true' ? 'style="display:none"' : ''; ?>>
      <input type="hidden" name="final_submit" value="1">
      <input type="hidden" name="role" id="roleInput" value="">
      <input type="hidden" name="interests" id="interestsInput" value="">
      <input type="hidden" name="specialty" id="specialtyInput" value="">

      <div class="reg-body">

        <!-- STEP 1 -->
        <div class="step-panel" id="step1">
          <h2 class="step-title">Create your account</h2>
          <p class="step-subtitle">Let's start with your basic information</p>

          <div class="row g-3">
            <div class="col-6">
              <label class="form-label">First Name</label>
              <input type="text" name="first_name" id="firstNameInput" class="form-control">
              <div class="field-error" id="err-first_name">First name is required</div>
            </div>
            <div class="col-6">
              <label class="form-label">Last Name </label>
              <input type="text" name="last_name" id="lastNameInput" class="form-control" >
              <div class="field-error" id="err-last_name">Last name is required</div>
            </div>
          </div>

          <div class="mb-3 mt-3">
            <label class="form-label">Email Address</label>
            <input type="email" name="email" id="emailInput" class="form-control" placeholder="you@example.com">
            <div class="field-error" id="err-email">Please enter a valid email address</div>
          </div>

          <div class="mb-3">
            <label class="form-label">Password </label>
            <input type="password" name="password" id="pwInput" class="form-control" placeholder="Min. 6 characters">
            <div class="pw-strength" id="pwStrength"></div>
            <div class="field-error" id="err-password">Password must be at least 6 characters</div>
          </div>

          <div class="mb-3">
            <label class="form-label">Confirm Password </label>
            <input type="password" id="confirmPw" class="form-control" placeholder="Repeat password">
            <div class="field-error" id="err-confirmPw">Passwords do not match</div>
          </div>

          <div class="row g-3 mt-0">
            <div class="col-6">
              <label class="form-label">Gender </label>
              <select name="gender" id="genderInput" class="form-select">
                <option value="">Select gender</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
                
              </select>
              <div class="field-error" id="err-gender">Please select your gender</div>
            </div>
            <div class="col-6">
              <label class="form-label">Age </label>
              <input type="number" name="age" id="ageInput" class="form-control" placeholder="e.g. 25" min="16" max="100">
              <div class="field-error" id="err-age">Please enter a valid age (18–60)</div>
            </div>
          </div>

          <div class="mb-3 mt-3">
            <label class="form-label">Phone Number</label>
            <input type="tel" id="phone-number" name="phone" class="form-control" placeholder="+962 7xx xxx xxxx">
           <div class="field-error" id="err-phone">Please enter correct phone number </div>
          </div>

          <button type="button" class="btn-next" onclick="goNext(1)">Continue →</button>
        </div>

        <!-- STEP 2 -->
        <div class="step-panel" id="step2">
          <h2 class="step-title">What brings you here?</h2>
          <p class="step-subtitle">Choose your role to personalize your experience</p>

          <div class="role-grid">
            <label class="role-card" id="roleHomeowner" onclick="selectRole('homeowner')">
              <input type="radio" name="role_pick" value="homeowner">
              <div class="role-icon">🏠</div>
              <div class="role-label">Homeowner</div>
              <div class="role-desc">Looking for maintenance services</div>
            </label>
            <label class="role-card" id="roleTechnician" onclick="selectRole('technician')">
              <input type="radio" name="role_pick" value="technician">
              <div class="role-icon">🔧</div>
              <div class="role-label">Technician</div>
              <div class="role-desc">Offering professional services</div>
            </label>
          </div>
          <div class="field-error" id="err-role">Please select a role to continue</div>

          <div class="btn-row">
            <button type="button" class="btn-back" onclick="goBack(2)">← Back</button>
            <button type="button" class="btn-next" onclick="goNext(2)">Continue →</button>
          </div>
        </div>

        <!-- STEP 3 - Homeowner interests -->
        <div class="step-panel" id="step3home">
          <h2 class="step-title">What are you interested in?</h2>
          <p class="step-subtitle">Select all service categories that apply</p>

          <div class="cat-grid" id="catGridHome">
            <?php foreach ($categories as $cat): ?>
            <label class="cat-item" onclick="toggleCat(this, 'home')">
              <input type="checkbox" value="<?php echo htmlspecialchars($cat['id']); ?>">
              <?php echo htmlspecialchars($cat['name']); ?>
            </label>
            <?php endforeach; ?>
            <?php if (empty($categories)): ?>
            <label class="cat-item" onclick="toggleCat(this,'home')"><input type="checkbox" value="plumbing"> 🔧 Plumbing</label>
            <label class="cat-item" onclick="toggleCat(this,'home')"><input type="checkbox" value="electrical"> ⚡ Electrical</label>
            <label class="cat-item" onclick="toggleCat(this,'home')"><input type="checkbox" value="cleaning"> 🧹 Cleaning</label>
            <label class="cat-item" onclick="toggleCat(this,'home')"><input type="checkbox" value="ac"> ❄️ AC Repair</label>
            <label class="cat-item" onclick="toggleCat(this,'home')"><input type="checkbox" value="painting"> 🎨 Painting</label>
            <label class="cat-item" onclick="toggleCat(this,'home')"><input type="checkbox" value="carpentry"> 🪵 Carpentry</label>
            <?php endif; ?>
          </div>

          <div class="btn-row">
            <button type="button" class="btn-back" onclick="goBack(3)">← Back</button>
            <button type="button" class="btn-next" onclick="goNext(3, 'home')">Continue →</button>
          </div>
        </div>

        <!-- STEP 3 - Technician specialty -->
        <div class="step-panel" id="step3tech">
          <h2 class="step-title">What's your specialty?</h2>
          <p class="step-subtitle">Choose your main field of expertise</p>

          <div class="cat-grid" id="catGridTech">
            <?php foreach ($categories as $cat): ?>
            <label class="cat-item" onclick="selectSpecialty(this, '<?php echo htmlspecialchars($cat['id']); ?>')">
              <input type="radio" name="specialty_pick" value="<?php echo htmlspecialchars($cat['id']); ?>">
              <?php echo htmlspecialchars($cat['name']); ?>
            </label>
            <?php endforeach; ?>
            <?php if (empty($categories)): ?>
            <label class="cat-item" onclick="selectSpecialty(this,'plumbing')"><input type="radio"> 🔧 Plumbing</label>
            <label class="cat-item" onclick="selectSpecialty(this,'electrical')"><input type="radio"> ⚡ Electrical</label>
            <label class="cat-item" onclick="selectSpecialty(this,'cleaning')"><input type="radio"> 🧹 Cleaning</label>
            <label class="cat-item" onclick="selectSpecialty(this,'ac')"><input type="radio"> ❄️ AC Repair</label>
            <label class="cat-item" onclick="selectSpecialty(this,'painting')"><input type="radio"> 🎨 Painting</label>
            <label class="cat-item" onclick="selectSpecialty(this,'carpentry')"><input type="radio"> 🪵 Carpentry</label>
            <?php endif; ?>
          </div>
          <div class="field-error" id="err-specialty">Please select your specialty</div>

          <div class="btn-row">
            <button type="button" class="btn-back" onclick="goBack(3)">← Back</button>
            <button type="button" class="btn-next" onclick="goNext(3, 'tech')">Continue →</button>
          </div>
        </div>

        <!-- STEP 4 - Homeowner location -->
        <div class="step-panel" id="step4home">
          <h2 class="step-title">Your location</h2>
          <p class="step-subtitle">Help technicians find you</p>

          <div class="mb-3">
            <label class="form-label">Country</label>
            <input type="text" name="country" class="form-control" placeholder="Jordan">
          </div>
          <div class="mb-3">
            <label class="form-label">City</label>
            <input type="text" name="city" class="form-control" placeholder="Amman">
          </div>
          <div class="mb-3">
            <label class="form-label">Address</label>
            <input type="text" name="address" class="form-control" placeholder="Street, building...">
          </div>

          <div class="terms-box">
            By creating an account on Maintify, you agree to our Terms of Service and Privacy Policy. We collect your personal data to connect you with qualified technicians in your area. Your information will never be shared with third parties without consent. You can delete your account at any time.
          </div>
          <label class="terms-check mb-2" id="termsHomeLabel">
            <input type="checkbox" id="termsHome">
            I agree to the Terms & Conditions
          </label>
          <div class="field-error" id="err-termsHome">You must accept the Terms & Conditions</div>

          <div class="btn-row">
            <button type="button" class="btn-back" onclick="goBack(4, 'home')">← Back</button>
            <button type="button" class="btn-next" onclick="submitForm('home')">Create Account 🎉</button>
          </div>
        </div>

        <!-- STEP 4 - Technician experience -->
        <div class="step-panel" id="step4tech">
          <h2 class="step-title">Your experience</h2>
          <p class="step-subtitle">Tell clients about your background</p>

          <div class="mb-3">
            <label class="form-label">Country</label>
            <input type="text" name="country_tech" class="form-control" placeholder="Jordan">
          </div>
          <div class="mb-3">
            <label class="form-label">City</label>
            <input type="text" name="city_tech" class="form-control" placeholder="Amman">
          </div>

          <div class="mb-4">
            <label class="form-label">Years of Experience</label>
            <div class="exp-display"><span id="expVal">5</span> <span>years</span></div>
            <input type="range" name="experience" min="1" max="30" value="5" id="expSlider"
              oninput="document.getElementById('expVal').textContent = this.value">
            <div style="display:flex;justify-content:space-between;font-size:12px;color:var(--muted);margin-top:4px">
              <span>1 yr</span><span>30 yrs</span>
            </div>
          </div>

          <div class="terms-box">
            By joining Maintify as a technician, you agree to provide accurate information about your skills and experience. You commit to professional conduct and quality service. Maintify reserves the right to suspend accounts that violate our community standards.
          </div>
          <label class="terms-check mb-2" id="termsTechLabel">
            <input type="checkbox" id="termsTech">
            I agree to the Terms & Conditions
          </label>
          <div class="field-error" id="err-termsTech">You must accept the Terms & Conditions</div>

          <div class="btn-row">
            <button type="button" class="btn-back" onclick="goBack(4, 'tech')">← Back</button>
            <button type="button" class="btn-next" onclick="submitForm('tech')">Create Account 🎉</button>
          </div>
        </div>

      </div>
    </form>

  </div>
</div>

<script>
const TOTAL_STEPS = 4;
let currentStep = 1;
let selectedRole = '';
let selectedSpecialty = '';

// ── Helpers ──────────────────────────────────────────────────────────
function showError(id, msg) {
    const el = document.getElementById('err-' + id);
    if (!el) return;
    if (msg) el.textContent = msg;
    el.classList.add('show');
}

function clearError(id) {
    const el = document.getElementById('err-' + id);
    if (el) el.classList.remove('show');
}

function markInvalid(inputEl) {
    if (inputEl) inputEl.classList.add('is-invalid');
}

function markValid(inputEl) {
    if (inputEl) inputEl.classList.remove('is-invalid');
}

// Clear error on user input
document.addEventListener('input', function (e) {
    const name = e.target.name || e.target.id;
    if (name) clearError(name);
    if (e.target.classList.contains('is-invalid')) markValid(e.target);
});

// ── Progress Bar ─────────────────────────────────────────────────────
function buildProgress(total) {
    const el = document.getElementById('progressSteps');
    if (!el) return;
    el.innerHTML = '';
    for (let i = 1; i <= total; i++) {
        const dot = document.createElement('div');
        dot.className = 'step-dot';
        dot.id = 'dot' + i;
        dot.innerHTML = '<span>' + i + '</span>';
        el.appendChild(dot);
        if (i < total) {
            const line = document.createElement('div');
            line.className = 'step-line';
            line.id = 'line' + i;
            el.appendChild(line);
        }
    }
    updateProgress();
}

function updateProgress() {
    for (let i = 1; i <= TOTAL_STEPS; i++) {
        const dot = document.getElementById('dot' + i);
        if (!dot) continue;
        dot.className = 'step-dot';
        if (i < currentStep) dot.classList.add('done');
        else if (i === currentStep) dot.classList.add('active');
        const line = document.getElementById('line' + i);
        if (line) line.className = 'step-line' + (i < currentStep ? ' done' : '');
    }
}

function showPanel(id) {
    document.querySelectorAll('.step-panel').forEach(p => p.classList.remove('active'));
    const el = document.getElementById(id);
    if (el) el.classList.add('active');
}

// ── Validation helpers ───────────────────────────────────────────────
function validateStep1() {
    let valid = true;

    const firstName = document.querySelector('[name="first_name"]');
    const lastName  = document.querySelector('[name="last_name"]');
    const email     = document.querySelector('[name="email"]');
    const pw        = document.getElementById('pwInput');
    const cpw       = document.getElementById('confirmPw');
    const phonenumber  = document.getElementById('phone-number');

    if (!firstName.value.trim()) {
        markInvalid(firstName); showError('first_name', 'First name is required'); valid = false;
    } else { markValid(firstName); clearError('first_name'); }

    if (!lastName.value.trim()) {
        markInvalid(lastName); showError('last_name', 'Last name is required'); valid = false;
    } else { markValid(lastName); clearError('last_name'); }

    const emailVal = email.value.trim();
    if (!emailVal || !emailVal.includes('@') || !emailVal.includes('.')) {
        markInvalid(email); showError('email', 'Please enter a valid email address'); valid = false;
    } else { markValid(email); clearError('email'); }

    if (pw.value.length < 6) {
        markInvalid(pw); showError('password', 'Password must be at least 6 characters'); valid = false;
    } else { markValid(pw); clearError('password'); }

    if (pw.value !== cpw.value || cpw.value === '') {
        markInvalid(cpw); showError('confirmPw', 'Passwords do not match'); valid = false;
    } else { markValid(cpw); clearError('confirmPw'); }

    const gender = document.getElementById('genderInput');
    if (!gender.value) {
        markInvalid(gender); showError('gender', 'Please select your gender'); valid = false;
    } else { markValid(gender); clearError('gender'); }

    const age = document.getElementById('ageInput');
    const ageVal = parseInt(age.value);
    if (!age.value || ageVal < 16 || ageVal > 100) {
        markInvalid(age); showError('age', 'Please enter a valid age (16–60)'); valid = false;
    } else { markValid(age); clearError('age'); }

    const phoneVal = phonenumber.value.trim();

// خيار 1: رقم أردني (07XXXXXXXX)
const phoneRegex = /^(\+962|0)7[0-9]{8}$/;

// خيار 2 (بديل): يدعم +962 أو 07
// const phoneRegex = /^(\+962|0)7[0-9]{8}$/;

if (!phoneVal || !phoneRegex.test(phoneVal)) {
    markInvalid(phonenumber);
    showError('phone', 'Please enter a valid phone number (e.g. 962 7XXXXXXXX)');
    valid = false;
} else {
    markValid(phonenumber);
    clearError('phone');
}
    return valid;
}

// ── Next ─────────────────────────────────────────────────────────────
function goNext(step) {

    if (step === 1) {
        if (!validateStep1()) return;
        currentStep = 2;
        updateProgress();
        showPanel('step2');
    }

    else if (step === 2) {
        if (!selectedRole) {
            showError('role', 'Please select a role to continue');
            document.getElementById('roleHomeowner').classList.add('is-invalid');
            document.getElementById('roleTechnician').classList.add('is-invalid');
            return;
        }
        clearError('role');

        if (selectedRole === 'homeowner') {
            currentStep = 4;
            updateProgress();
            showPanel('step4home');
        } else {
            currentStep = 3;
            updateProgress();
            showPanel('step3tech');
        }
    }

    else if (step === 3) {
        if (!selectedSpecialty) {
            showError('specialty', 'Please select your specialty');
            return;
        }
        clearError('specialty');
        document.getElementById('specialtyInput').value = selectedSpecialty;
        currentStep = 4;
        updateProgress();
        showPanel('step4tech');
    }
}

// ── Back ─────────────────────────────────────────────────────────────
function goBack(step) {
    if (step === 2) { currentStep = 1; updateProgress(); showPanel('step1'); }
    else if (step === 3) { currentStep = 2; updateProgress(); showPanel('step2'); }
    else if (step === 4) {
        if (selectedRole === 'homeowner') { currentStep = 2; updateProgress(); showPanel('step2'); }
        else { currentStep = 3; updateProgress(); showPanel('step3tech'); }
    }
}

// ── Role select ───────────────────────────────────────────────────────
function selectRole(role) {
    selectedRole = role;
    document.getElementById('roleInput').value = role;
    document.getElementById('roleHomeowner').classList.toggle('selected', role === 'homeowner');
    document.getElementById('roleTechnician').classList.toggle('selected', role === 'technician');
    // Remove invalid highlight once selected
    document.getElementById('roleHomeowner').classList.remove('is-invalid');
    document.getElementById('roleTechnician').classList.remove('is-invalid');
    clearError('role');
}

// ── Cat toggles ───────────────────────────────────────────────────────
function toggleCat(el, type) {
    el.classList.toggle('selected');
    const ids = [...document.querySelectorAll('#catGridHome .cat-item.selected')]
        .map(i => i.querySelector('input').value);
    document.getElementById('interestsInput').value = ids.join(',');
}

function selectSpecialty(el, val) {
    document.querySelectorAll('#catGridTech .cat-item').forEach(i => i.classList.remove('selected'));
    el.classList.add('selected');
    selectedSpecialty = val;
    clearError('specialty');
}

// ── Submit ────────────────────────────────────────────────────────────
function submitForm(type) {
    const termsId    = type === 'home' ? 'termsHome' : 'termsTech';
    const termsLabel = type === 'home' ? 'termsHomeLabel' : 'termsTechLabel';
    const termsEl    = document.getElementById(termsId);

    if (!termsEl.checked) {
        showError(termsId, 'You must accept the Terms & Conditions');
        document.getElementById(termsLabel).classList.add('is-invalid');
        return;
    }
    clearError(termsId);
    document.getElementById(termsLabel).classList.remove('is-invalid');

    if (type === 'tech') {
        const techCountry = document.querySelector('[name="country_tech"]').value;
        const techCity    = document.querySelector('[name="city_tech"]').value;
        document.querySelector('[name="country"]').value = techCountry;
        document.querySelector('[name="city"]').value    = techCity;
    }

    document.getElementById('regForm').submit();
}

// ── Terms checkbox: clear error on check ─────────────────────────────
document.addEventListener('change', function (e) {
    if (e.target.id === 'termsHome') {
        clearError('termsHome');
        document.getElementById('termsHomeLabel').classList.remove('is-invalid');
    }
    if (e.target.id === 'termsTech') {
        clearError('termsTech');
        document.getElementById('termsTechLabel').classList.remove('is-invalid');
    }
});

// ── Password strength ─────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    buildProgress(TOTAL_STEPS);
    showPanel('step1');

    const pwInput = document.getElementById('pwInput');
    if (pwInput) {
        pwInput.addEventListener('input', function () {
            const pw  = this.value;
            const bar = document.getElementById('pwStrength');
            bar.className = 'pw-strength';
            if (pw.length === 0) return;
            if (pw.length < 6) bar.classList.add('weak');
            else if (pw.length < 10 || !/[A-Z]/.test(pw) || !/[0-9]/.test(pw)) bar.classList.add('medium');
            else bar.classList.add('strong');
        });
    }
});
</script>

<?php include_once 'includes/footer.php'; ?>