<?php
require_once '../config/db.php';
include_once '../includes/header.php';
include_once '../includes/navbar.php';

// نتحقق إذا كان المستخدم مسجل دخول عشان نوجهه لصفحة الشكاوى، وإذا لا نوجهه لصفحة تسجيل الدخول
$ticket_link = isset($_SESSION['user_id']) 
    ? ($base_url . ($_SESSION['role'] === 'technician' ? 'Technician/complaints.php' : 'Homeowner/complaints.php'))
    : ($base_url . 'login.php');
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
    
    .help-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        margin-bottom: 15px;
    }
    .help-card {
        height: 100%;
        text-align: center;
        padding: 30px !important;
    }
</style>

<div class="page-header-bg">
    <div class="container">
        <h1 class="page-title"><?php echo $lang['help_title'] ?? 'Help Center'; ?></h1>
        <p class="page-subtitle"><?php echo $lang['help_subtitle'] ?? 'We are here to support you 24/7.'; ?></p>
    </div>
</div>

<div class="container content-wrapper">
    <div class="row g-4 justify-content-center">
        
        <div class="col-lg-4 col-md-6">
            <div class="google-card help-card hover-card">
                <div class="help-icon bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-envelope-paper"></i>
                </div>
                <h4 class="fw-bold fs-5 mb-3"><?php echo $lang['help_sec1_title'] ?? 'Contact Support'; ?></h4>
                <p class="text-muted mb-4"><?php echo $lang['help_sec1_desc'] ?? 'Reach our team via email.'; ?></p>
                <a href="mailto:support@maintify.com" class="btn btn-outline-primary rounded-pill px-4 fw-bold">support@maintify.com</a>
            </div>
        </div>

        <div class="col-lg-4 col-md-6">
            <div class="google-card help-card hover-card">
                <div class="help-icon bg-danger bg-opacity-10 text-danger">
                    <i class="bi bi-headset"></i>
                </div>
                <h4 class="fw-bold fs-5 mb-3"><?php echo $lang['help_sec2_title'] ?? 'Report an Issue'; ?></h4>
                <p class="text-muted mb-4"><?php echo $lang['help_sec2_desc'] ?? 'Open a ticket from your dashboard.'; ?></p>
                <a href="<?php echo $ticket_link; ?>" class="btn btn-danger rounded-pill px-4 fw-bold">
                    <?php echo isset($_SESSION['user_id']) ? ($lang['complaints_suggestions'] ?? 'Open Ticket') : ($lang['login'] ?? 'Login to Report'); ?>
                </a>
            </div>
        </div>

        <div class="col-lg-4 col-md-6">
            <div class="google-card help-card hover-card">
                <div class="help-icon bg-success bg-opacity-10 text-success">
                    <i class="bi bi-question-circle"></i>
                </div>
                <h4 class="fw-bold fs-5 mb-3"><?php echo $lang['faq_title'] ?? 'FAQ'; ?></h4>
                <p class="text-muted mb-4"><?php echo $lang['faq_subtitle'] ?? 'Find quick answers to your questions.'; ?></p>
                <a href="faq.php" class="btn btn-outline-success rounded-pill px-4 fw-bold">
                    <?php echo $lang['footer_faq'] ?? 'Read FAQ'; ?>
                </a>
            </div>
        </div>

    </div>
</div>

<?php include_once '../includes/footer.php'; ?>