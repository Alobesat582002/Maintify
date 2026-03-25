<?php
require_once 'config/db.php';
include_once 'includes/header.php'; // ← ضع رابط main.css هنا داخل header.php
include_once 'includes/navbar.php';

?>
<link rel="stylesheet" href="assets/css/main.css">
<!-- ══ HERO ══ -->
<section class="hero">

  <!-- يسار: نص -->
  <div class="hero-left">
    <div class="hero-badge">
      <span class="hero-badge-dot"></span>
      خدمات المنزل الموثوقة
    </div>

    <h1 class="hero-title">
      منزلك يستحق
      <span class="hero-title-accent">أفضل رعاية.</span>
    </h1>

    <p class="hero-subtitle">
      تواصل مع فنيين معتمدين لجميع احتياجات صيانة منزلك — سريع، شفاف، وبدون تعقيد.
    </p>

    <div class="hero-cta">
      <?php if (!isset($_SESSION['user_id'])): ?>
        <a href="register.php" class="btn btn-accent">ابدأ مجاناً</a>
        <a href="login.php"    class="btn btn-outline-light">تسجيل الدخول</a>
      <?php else: ?>
        <a href="dashboard.php" class="btn btn-accent">لوحة التحكم</a>
      <?php endif; ?>
    </div>
  </div>

  <!-- يمين: رسم منزل + بطاقات عائمة -->
  <div class="hero-right">
    <div class="hero-house-wrap">

      <!-- SVG منزل -->
      <svg class="hero-house-svg" viewBox="0 0 400 340" fill="none" xmlns="http://www.w3.org/2000/svg">
        <!-- سماء -->
        <rect width="400" height="340" rx="24" fill="#f0ebe0"/>
        <!-- شمس -->
        <circle cx="340" cy="55" r="38" fill="#fde68a" opacity=".6"/>
        <!-- أرضية -->
        <rect y="240" width="400" height="100" rx="0" fill="#d4c9b0" opacity=".5"/>
        <!-- عشب -->
        <ellipse cx="200" cy="248" rx="180" ry="14" fill="#8bc34a" opacity=".4"/>
        <!-- جسم البيت -->
        <rect x="80" y="155" width="240" height="155" rx="4" fill="#fff"/>
        <!-- سقف -->
        <polygon points="55,165 200,60 345,165" fill="#e8651a"/>
        <polygon points="55,165 200,80 345,165" fill="#c85510" opacity=".3"/>
        <!-- مدخل -->
        <rect x="162" y="220" width="76" height="90" rx="8" fill="#c85510" opacity=".8"/>
        <circle cx="230" cy="268" r="4" fill="#fff" opacity=".6"/>
        <!-- نافذة يسار -->
        <rect x="100" y="185" width="60" height="55" rx="6" fill="#bfdbfe"/>
        <line x1="130" y1="185" x2="130" y2="240" stroke="white" stroke-width="2"/>
        <line x1="100" y1="212" x2="160" y2="212" stroke="white" stroke-width="2"/>
        <!-- نافذة يمين -->
        <rect x="240" y="185" width="60" height="55" rx="6" fill="#bfdbfe"/>
        <line x1="270" y1="185" x2="270" y2="240" stroke="white" stroke-width="2"/>
        <line x1="240" y1="212" x2="300" y2="212" stroke="white" stroke-width="2"/>
        <!-- مدخنة -->
        <rect x="270" y="75" width="28" height="50" rx="3" fill="#c85510"/>
        <rect x="266" y="68" width="36" height="12" rx="3" fill="#a83f00"/>
        <!-- شجرة -->
        <rect x="30"  y="205" width="8" height="45" fill="#8b6914"/>
        <circle cx="34" cy="195" r="26" fill="#66bb6a"/>
        <rect x="358" y="215" width="8" height="35" fill="#8b6914"/>
        <circle cx="362" cy="206" r="20" fill="#4caf50"/>
      </svg>

      <!-- بطاقة عائمة يسار -->
      <div class="hero-float-badge left">
        <span class="hfb-icon">🔧</span>
        <div>
          <div class="hfb-num">2,400+</div>
          <div class="hfb-label">فني معتمد</div>
        </div>
      </div>

      
      <div class="hero-float-badge right">
        <span class="hfb-icon">⭐</span>
        <div>
          <div class="hfb-num">4.9/5</div>
          <div class="hfb-label">رضا العملاء</div>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- ══ TRUST BAR ══ -->
<div class="trust-bar">
  <div class="trust-bar-inner container-main">
    <div class="trust-item">
      <span class="trust-item-icon">✅</span>
      <span><b class="trust-item-bold">فنيون موثقون</b> 100%</span>
    </div>
    <div class="trust-item">
      <span class="trust-item-icon">⚡</span>
      <span>استجابة خلال <b class="trust-item-bold">15 دقيقة</b></span>
    </div>
    <div class="trust-item">
      <span class="trust-item-icon">💬</span>
      <span><b class="trust-item-bold">تواصل مباشر</b> مع الفني</span>
    </div>
    <div class="trust-item">
      <span class="trust-item-icon">🏆</span>
      <span>أكثر من <b class="trust-item-bold">12,000</b> طلب منجز</span>
    </div>
  </div>
</div>

<!-- ══ ABOUT ══ -->
<section class="about-section">
  <div class="container-main">
    <div class="about-inner">

      <!-- بطاقات إحصائية بصرية -->
      <div class="about-visual">
        <div class="about-stat">
          <span style="font-size:2rem">🏠</span>
          <div>
            <div class="about-stat-num">12,000+</div>
            <div class="about-stat-label">منزل في عهدتنا</div>
          </div>
        </div>
        <div class="about-stat">
          <div class="about-stat-num">98%</div>
          <div class="about-stat-label">نسبة الرضا</div>
        </div>
        <div class="about-stat">
          <div class="about-stat-num">15 د</div>
          <div class="about-stat-label">أول رد</div>
        </div>
      </div>

      <!-- نص -->
      <div class="about-text-content">
        <div>
          <p class="section-eyebrow">من نحن</p>
          <h2 class="section-title">منصة صُممت لراحة منزلك.</h2>
        </div>
        <p class="about-desc">
          Maintify تجسّر الفجوة بين أصحاب المنازل والفنيين المحترفين. سواء كنت تحتاج إصلاحاً عاجلاً أو تريد نشر طلب للمزايدة — نجعل صيانة المنزل بسيطة، شفافة، وفعّالة.
        </p>
        <div class="about-feature-list">
          <div class="about-feature"><span class="about-feature-dot"></span> فنيون معتمدون ومدرّبون بشكل مستقل</div>
          <div class="about-feature"><span class="about-feature-dot"></span> أسعار تنافسية عبر نظام المزايدة الذكي</div>
          <div class="about-feature"><span class="about-feature-dot"></span> ضمان جودة على جميع الأعمال المنجزة</div>
          <div class="about-feature"><span class="about-feature-dot"></span> تتبع الطلب في الوقت الفعلي</div>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- ══ SERVICES ══ -->
<section class="services-section">
  <div class="container-main">
    <div class="services-header">
      <div>
        <p class="section-eyebrow">ما نقدمه</p>
        <h2 class="section-title light">خدماتنا</h2>
      </div>
      <a href="services.php" class="btn btn-outline-light">عرض الكل ←</a>
    </div>

    <div class="services-grid">
      <div class="service-card">
        <span class="service-icon">🚿</span>
        <div class="service-name">السباكة</div>
        <p class="service-desc">تسريبات، تمديدات، وتصريف — بأيدي سباكين معتمدين.</p>
      </div>
      <div class="service-card">
        <span class="service-icon">⚡</span>
        <div class="service-name">الكهرباء</div>
        <p class="service-desc">إصلاح وتمديد كهربائي آمن ومطابق للمعايير.</p>
      </div>
      <div class="service-card">
        <span class="service-icon">❄️</span>
        <div class="service-name">التكييف والتدفئة</div>
        <p class="service-desc">صيانة وتركيب أنظمة HVAC لراحة دائمة.</p>
      </div>
      <div class="service-card">
        <span class="service-icon">🪟</span>
        <div class="service-name">النجارة</div>
        <p class="service-desc">أبواب، خزائن، وأعمال خشبية بدقة وإتقان.</p>
      </div>
      <div class="service-card">
        <span class="service-icon">🎨</span>
        <div class="service-name">الدهانات</div>
        <p class="service-desc">طلاء داخلي وخارجي بتشطيبات احترافية.</p>
      </div>
      <div class="service-card">
        <span class="service-icon">🧹</span>
        <div class="service-name">التنظيف العميق</div>
        <p class="service-desc">تنظيف شامل للمطابخ والحمامات وكامل المنزل.</p>
      </div>
    </div>
  </div>
</section>

<!-- ══ WHY US ══ -->
<section class="why-section">
  <div class="container-main">
    <p class="section-eyebrow">لماذا Maintify</p>
    <h2 class="section-title">الطريقة الأذكى<br>لإدارة منزلك.</h2>

    <div class="why-grid">
      <div class="why-card">
        <div class="why-card-bar"></div>
        <div class="why-card-num">01</div>
        <span class="why-card-icon">💰</span>
        <div class="why-card-title">مزايدة ذكية</div>
        <p class="why-card-desc">انشر طلبك واستقبل عروضاً من فنيين متعددين للحصول على أفضل سعر.</p>
      </div>
      <div class="why-card">
        <div class="why-card-bar"></div>
        <div class="why-card-num">02</div>
        <span class="why-card-icon">🛡️</span>
        <div class="why-card-title">فنيون موثقون</div>
        <p class="why-card-desc">تصفّح الملفات الشخصية والتقييمات قبل اتخاذ أي قرار — شفافية تامة.</p>
      </div>
      <div class="why-card">
        <div class="why-card-bar"></div>
        <div class="why-card-num">03</div>
        <span class="why-card-icon">💬</span>
        <div class="why-card-title">تواصل مباشر</div>
        <p class="why-card-desc">تحدث مع مزود الخدمة مباشرة لتوضيح التفاصيل قبل التأكيد.</p>
      </div>
    </div>
  </div>
</section>

<!-- ══ CTA ══ -->
<?php if (!isset($_SESSION['user_id'])): ?>
<section class="cta-banner-section">
  <div class="container-main">
    <div class="cta-banner">
      <h2 class="cta-banner-title">جاهز لتجربة صيانة المنزل بشكل مختلف؟</h2>
      <a href="register.php" class="btn btn-dark">أنشئ حساباً مجانياً ←</a>
    </div>
  </div>
</section>
<?php endif; ?>

<?php include_once 'includes/footer.php'; ?>