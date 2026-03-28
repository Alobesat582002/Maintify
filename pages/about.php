<?php
require_once '../config/db.php';
include_once '../includes/header.php';
include_once '../includes/navbar.php';
?>

<style>
    .page-header-bg {
        background: linear-gradient(135deg, #0a1023 0%, #162039 100%);
        padding: 90px 0 70px;
        color: white;
        text-align: center;
        margin-top: 70px;
    }
    .page-title { font-weight: 800; font-size: 2.7rem; margin-bottom: 12px; letter-spacing: -0.6px; }
    .page-subtitle { color: #b0b7c3; font-size: 1.15rem; }
    .content-wrapper { margin-top: -50px; padding-bottom: 70px; }
    
    .about-section { margin-bottom: 35px; }
    .about-section:last-child { margin-bottom: 0; }
    .about-section h3 { font-size: 1.35rem; font-weight: 700; color: #1e293b; margin-bottom: 12px; }
    .about-section p { color: #64748b; line-height: 1.8; font-size: 1.02rem; }

    /* تأثير اللمسة البرتقالية على الصندوق */
    .premium-card {
        padding: 40px !important;
        position: relative;
        overflow: hidden;
    }
    .premium-card::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 3px;
        background: #F36F21;
        opacity: 0.6;
    }

    /* أيقونات الأقسام */
    .section-icon {
        font-size: 1.6rem;
        margin-bottom: 15px;
        display: inline-block;
        transition: transform 0.3s ease;
    }
    .about-section:hover .section-icon {
        transform: scale(1.1);
    }
</style>

<div class="page-header-bg">
    <div class="container">
        <h1 class="page-title"><?php echo $lang['about_title'] ?? 'About Maintify'; ?></h1>
        <p class="page-subtitle"><?php echo $lang['about_subtitle'] ?? 'We connect you with trusted technicians.'; ?></p>
    </div>
</div>

<div class="container content-wrapper">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="google-card premium-card hover-card">
                
                <div class="row gy-4">
                    <div class="col-md-6 about-section">
                        <i class="bi bi-rocket-takeoff section-icon text-primary opacity-75"></i>
                        <h3><?php echo $lang['about_sec1_title'] ?? 'Our Vision'; ?></h3>
                        <p><?php echo $lang['about_sec1_desc'] ?? ''; ?></p>
                    </div>

                    <div class="col-md-6 about-section">
                        <i class="bi bi-bullseye section-icon text-primary opacity-75"></i>
                        <h3><?php echo $lang['about_sec2_title'] ?? 'Our Mission'; ?></h3>
                        <p><?php echo $lang['about_sec2_desc'] ?? ''; ?></p>
                    </div>
                </div>

                <hr class="my-5" style="border-top: 1px solid #e2e8f0; opacity: 1;">

                <div class="about-section">
                    <i class="bi bi-shield-check section-icon text-success opacity-75"></i>
                    <h3><?php echo $lang['about_sec3_title'] ?? 'Why Maintify?'; ?></h3>
                    <ul class="list-unstyled text-muted ps-0" style="line-height: 2;">
                        <li><i class="bi bi-check2-circle text-success me-2 mx-1"></i><?php echo $lang['about_sec3_desc1'] ?? ''; ?></li>
                        <li><i class="bi bi-check2-circle text-success me-2 mx-1"></i><?php echo $lang['about_sec3_desc2'] ?? ''; ?></li>
                        <li><i class="bi bi-check2-circle text-success me-2 mx-1"></i><?php echo $lang['about_sec3_desc3'] ?? ''; ?></li>
                    </ul>
                </div>

                <div class="about-section mb-0">
                    <i class="bi bi-lightbulb section-icon text-warning opacity-75"></i>
                    <h3><?php echo $lang['about_sec4_title'] ?? 'Our Core Values'; ?></h3>
                    <ul class="list-unstyled text-muted ps-0" style="line-height: 2;">
                        <li><i class="bi bi-star-fill text-warning me-2 mx-1" style="font-size: 0.8rem;"></i><?php echo $lang['about_sec4_desc1'] ?? ''; ?></li>
                        <li><i class="bi bi-star-fill text-warning me-2 mx-1" style="font-size: 0.8rem;"></i><?php echo $lang['about_sec4_desc2'] ?? ''; ?></li>
                        <li><i class="bi bi-star-fill text-warning me-2 mx-1" style="font-size: 0.8rem;"></i><?php echo $lang['about_sec4_desc3'] ?? ''; ?></li>
                    </ul>
                </div>

            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>