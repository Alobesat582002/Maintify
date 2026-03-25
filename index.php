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

  <!-- يمين: فيديو slideshow -->
  <div class="hero-right">

    <!-- Video Slider -->
    <div class="vslider">

      <!-- الفيديوهات الثلاثة -->
      <div class="vslide active">
        <video autoplay muted loop playsinline>
          <source src="assets/vid/1vd.mp4" type="video/mp4">
        </video>
      </div>
      <div class="vslide">
        <video muted loop playsinline>
          <source src="assets/vid/2vd.mp4" type="video/mp4">
        </video>
      </div>
      <div class="vslide">
        <video muted loop playsinline>
          <source src="assets/vid/3vd.mp4" type="video/mp4">
        </video>
      </div>

      <!-- Gradient overlay -->
      <div class="vslider-overlay"></div>

      <!-- Label فوق الفيديو -->
      <div class="vslider-label">
        <span class="vslider-label-dot"></span>
        <span class="vslider-label-text">فنيون محترفون في الميدان</span>
      </div>

      <!-- Dots navigation -->
      <div class="vslider-dots">
        <button class="vdot active" data-index="0"></button>
        <button class="vdot" data-index="1"></button>
        <button class="vdot" data-index="2"></button>
      </div>

      <!-- Progress bar -->
      <div class="vslider-progress">
        <div class="vslider-progress-bar" id="vProgressBar"></div>
      </div>

    </div>

    <!-- بطاقات معلومات -->
    <div class="vslider-stats">
      <div class="vstat">
        <span class="vstat-icon">🔧</span>
        <div>
          <div class="vstat-num">2,400+</div>
          <div class="vstat-label">فني معتمد</div>
        </div>
      </div>
      <div class="vstat-divider"></div>
      <div class="vstat">
        <span class="vstat-icon">⭐</span>
        <div>
          <div class="vstat-num">4.9/5</div>
          <div class="vstat-label">رضا العملاء</div>
        </div>
      </div>
      <div class="vstat-divider"></div>
      <div class="vstat">
        <span class="vstat-icon">⚡</span>
        <div>
          <div class="vstat-num">15 د</div>
          <div class="vstat-label">أول استجابة</div>
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

<script>
(function () {
  const slides   = document.querySelectorAll('.vslide');
  const dots     = document.querySelectorAll('.vdot');
  const bar      = document.getElementById('vProgressBar');
  const DURATION = 7000; // مدة كل فيديو بالـ ms
  let current    = 0;
  let timer      = null;
  let startTime  = null;
  let rafId      = null;

  function goTo(idx) {
    // أوقف الحركة الحالية
    cancelAnimationFrame(rafId);

    // أخفي الشريحة الحالية
    slides[current].classList.remove('active');
    dots[current].classList.remove('active');
    slides[current].querySelector('video').pause();

    // فعّل الجديدة
    current = idx;
    slides[current].classList.add('active');
    dots[current].classList.add('active');

    const vid = slides[current].querySelector('video');
    vid.currentTime = 0;
    vid.play().catch(() => {});

    // ابدأ progress bar
    startTime = performance.now();
    animateBar();
  }

  function animateBar() {
    rafId = requestAnimationFrame(function tick(now) {
      const elapsed  = now - startTime;
      const progress = Math.min(elapsed / DURATION * 100, 100);
      bar.style.width = progress + '%';
      if (progress < 100) {
        rafId = requestAnimationFrame(tick);
      } else {
        goTo((current + 1) % slides.length);
      }
    });
  }

  // Dots click
  dots.forEach(dot => {
    dot.addEventListener('click', () => goTo(+dot.dataset.index));
  });

  // ابدأ
  goTo(0);
})();
</script>

<?php include_once 'includes/footer.php'; ?> 
