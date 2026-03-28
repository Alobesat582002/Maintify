<?php
// تأكد من تشغيل الجلسة إذا لزم الأمر
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
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
        margin-top: 70px; /* لتعويض مساحة الناف بار الثابت */
    }
    
    .page-title {
        font-weight: 800;
        font-size: 2.5rem;
        margin-bottom: 10px;
        letter-spacing: -0.5px;
    }
    
    .page-subtitle {
        color: #9ca3af;
        font-size: 1.1rem;
    }

    .content-wrapper {
        margin-top: -40px; /* رفع الصندوق للأعلى ليتداخل مع الهيدر الداكن */
        padding-bottom: 60px;
    }

    /* أسلوب الأسئلة الشائعة */
    .faq-item {
        border-bottom: 1px solid #e5e7eb;
        padding: 20px 0;
    }
    .faq-item:last-child {
        border-bottom: none;
    }
    .faq-question {
        font-weight: 700;
        font-size: 1.1rem;
        color: #1e293b;
        margin-bottom: 10px;
    }
    .faq-answer {
        color: #64748b;
        line-height: 1.8;
    }
</style>

<div class="page-header-bg">
    <div class="container">
        <h1 class="page-title"><?php echo $lang['faq_title'] ?? 'FAQ'; ?></h1>
        <p class="page-subtitle"><?php echo $lang['faq_subtitle'] ?? 'Everything you need to know.'; ?></p>
    </div>
</div>

<div class="container content-wrapper">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="google-card">
                
                <div class="faq-item">
                    <div class="faq-question"><i class="bi bi-question-circle text-primary mx-2"></i> <?php echo $lang['faq_q1'] ?? ''; ?></div>
                    <div class="faq-answer">
                        <?php echo $lang['faq_a1'] ?? ''; ?>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question"><i class="bi bi-shield-check text-primary mx-2"></i> <?php echo $lang['faq_q2'] ?? ''; ?></div>
                    <div class="faq-answer">
                        <?php echo $lang['faq_a2'] ?? ''; ?>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question"><i class="bi bi-credit-card text-primary mx-2"></i> <?php echo $lang['faq_q3'] ?? ''; ?></div>
                    <div class="faq-answer">
                        <?php echo $lang['faq_a3'] ?? ''; ?>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question"><i class="bi bi-headset text-primary mx-2"></i> <?php echo $lang['faq_q4'] ?? ''; ?></div>
                    <div class="faq-answer">
                        <?php echo $lang['faq_a4'] ?? ''; ?>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php 
// استدعاء الفوتر
include_once '../includes/footer.php'; 
?>