<?php
require_once '../config/db.php';
include_once '../includes/header.php';
include_once '../includes/navbar.php';
?>

<style>
    .page-header-bg {
        background: linear-gradient(135deg, #0a0f1e 0%, #111827 100%);
        padding: 80px 0 60px;
        color: white;
        text-align: center;
        margin-top: 70px;
    }
    .page-title { font-weight: 800; font-size: 2.5rem; margin-bottom: 10px; letter-spacing: -0.5px; }
    .page-subtitle { color: #9ca3af; font-size: 1.1rem; }
    .content-wrapper { margin-top: -40px; padding-bottom: 60px; }
    
    .text-section { margin-bottom: 30px; }
    .text-section h3 { font-size: 1.25rem; font-weight: 700; color: #1e293b; margin-bottom: 10px; }
    .text-section p { color: #64748b; line-height: 1.8; font-size: 1rem; }
</style>

<div class="page-header-bg">
    <div class="container">
        <h1 class="page-title"><?php echo $lang['terms_title'] ?? 'Terms of Service'; ?></h1>
        <p class="page-subtitle"><?php echo $lang['terms_subtitle'] ?? 'Please read carefully.'; ?></p>
    </div>
</div>

<div class="container content-wrapper">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="google-card">
                
                <div class="text-section">
                    <h3><?php echo $lang['terms_sec1_title'] ?? ''; ?></h3>
                    <p><?php echo $lang['terms_sec1_desc'] ?? ''; ?></p>
                </div>

                <div class="text-section">
                    <h3><?php echo $lang['terms_sec2_title'] ?? ''; ?></h3>
                    <p><?php echo $lang['terms_sec2_desc'] ?? ''; ?></p>
                </div>

                <div class="text-section mb-0">
                    <h3><?php echo $lang['terms_sec3_title'] ?? ''; ?></h3>
                    <p class="mb-0"><?php echo $lang['terms_sec3_desc'] ?? ''; ?></p>
                </div>

            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>