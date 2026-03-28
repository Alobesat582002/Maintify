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

    /* Steps */
    .steps-list { list-style: none; padding: 0; margin: 0; counter-reset: step-counter; }
    .steps-list li {
        counter-increment: step-counter;
        display: flex; align-items: flex-start; gap: 18px;
        padding: 22px 0;
        border-bottom: 1px solid #f1f5f9;
    }
    .steps-list li:last-child { border-bottom: none; }
    .step-num {
        min-width: 40px; height: 40px; border-radius: 50%;
        background: linear-gradient(135deg, #0a0f1e, #1e40af);
        color: #fff; font-weight: 800; font-size: 1rem;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .step-body h4 { font-size: 1.05rem; font-weight: 700; color: #1e293b; margin-bottom: 5px; }
    .step-body p  { color: #64748b; font-size: 0.95rem; line-height: 1.7; margin: 0; }
</style>

<div class="page-header-bg">
    <div class="container">
        <h1 class="page-title"><?php echo $lang['hiw_title'] ?? ''; ?></h1>
        <p class="page-subtitle"><?php echo $lang['hiw_subtitle'] ?? ''; ?></p>
    </div>
</div>

<div class="container content-wrapper">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="google-card">

                <!-- Intro -->
                <div class="text-section">
                    <h3><?php echo $lang['hiw_intro_title'] ?? ''; ?></h3>
                    <p><?php echo $lang['hiw_intro_desc'] ?? ''; ?></p>
                </div>

                <!-- Steps -->
                <div class="text-section">
                    <h3><?php echo $lang['hiw_steps_title'] ?? ''; ?></h3>
                    <ul class="steps-list">
                        <li>
                            <div class="step-num">1</div>
                            <div class="step-body">
                                <h4><?php echo $lang['hiw_step1_title'] ?? ''; ?></h4>
                                <p><?php echo $lang['hiw_step1_desc'] ?? ''; ?></p>
                            </div>
                        </li>
                        <li>
                            <div class="step-num">2</div>
                            <div class="step-body">
                                <h4><?php echo $lang['hiw_step2_title'] ?? ''; ?></h4>
                                <p><?php echo $lang['hiw_step2_desc'] ?? ''; ?></p>
                            </div>
                        </li>
                        <li>
                            <div class="step-num">3</div>
                            <div class="step-body">
                                <h4><?php echo $lang['hiw_step3_title'] ?? ''; ?></h4>
                                <p><?php echo $lang['hiw_step3_desc'] ?? ''; ?></p>
                            </div>
                        </li>
                        <li>
                            <div class="step-num">4</div>
                            <div class="step-body">
                                <h4><?php echo $lang['hiw_step4_title'] ?? ''; ?></h4>
                                <p><?php echo $lang['hiw_step4_desc'] ?? ''; ?></p>
                            </div>
                        </li>
                        <li>
                            <div class="step-num">5</div>
                            <div class="step-body">
                                <h4><?php echo $lang['hiw_step5_title'] ?? ''; ?></h4>
                                <p><?php echo $lang['hiw_step5_desc'] ?? ''; ?></p>
                            </div>
                        </li>
                    </ul>
                </div>

                <!-- Closing note -->
                <div class="text-section mb-0">
                    <h3><?php echo $lang['hiw_note_title'] ?? ''; ?></h3>
                    <p class="mb-0"><?php echo $lang['hiw_note_desc'] ?? ''; ?></p>
                </div>

            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>