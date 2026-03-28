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

    /* Services grid */
    .services-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 16px;
        margin-top: 10px;
    }
    .service-card {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 22px 18px;
        text-align: center;
        transition: box-shadow 0.2s, border-color 0.2s, transform 0.2s;
        background: #fff;
    }
    .service-card:hover {
        box-shadow: 0 8px 28px rgba(0,0,0,0.08);
        border-color: #cbd5e1;
        transform: translateY(-3px);
    }
    .service-icon {
        font-size: 2.2rem;
        margin-bottom: 12px;
        display: block;
    }
    .service-name {
        font-size: 1rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 6px;
    }
    .service-desc {
        font-size: 0.875rem;
        color: #64748b;
        line-height: 1.6;
        margin: 0;
    }
</style>

<div class="page-header-bg">
    <div class="container">
        <h1 class="page-title"><?php echo $lang['services_title'] ?? ''; ?></h1>
        <p class="page-subtitle"><?php echo $lang['services_subtitle'] ?? ''; ?></p>
    </div>
</div>

<div class="container content-wrapper">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="google-card">

                <!-- Intro -->
                <div class="text-section">
                    <h3><?php echo $lang['services_intro_title'] ?? ''; ?></h3>
                    <p><?php echo $lang['services_intro_desc'] ?? ''; ?></p>
                </div>

                <!-- Services grid -->
                <div class="text-section">
                    <h3><?php echo $lang['services_list_title'] ?? ''; ?></h3>
                    <div class="services-grid">

                        <div class="service-card">
                            <span class="service-icon">🔧</span>
                            <div class="service-name"><?php echo $lang['srv_plumbing'] ?? ''; ?></div>
                            <p class="service-desc"><?php echo $lang['srv_plumbing_desc'] ?? ''; ?></p>
                        </div>

                        <div class="service-card">
                            <span class="service-icon">⚡</span>
                            <div class="service-name"><?php echo $lang['srv_electrical'] ?? ''; ?></div>
                            <p class="service-desc"><?php echo $lang['srv_electrical_desc'] ?? ''; ?></p>
                        </div>

                        <div class="service-card">
                            <span class="service-icon">❄️</span>
                            <div class="service-name"><?php echo $lang['srv_ac'] ?? ''; ?></div>
                            <p class="service-desc"><?php echo $lang['srv_ac_desc'] ?? ''; ?></p>
                        </div>

                        <div class="service-card">
                            <span class="service-icon">🎨</span>
                            <div class="service-name"><?php echo $lang['srv_painting'] ?? ''; ?></div>
                            <p class="service-desc"><?php echo $lang['srv_painting_desc'] ?? ''; ?></p>
                        </div>

                        <div class="service-card">
                            <span class="service-icon">🪟</span>
                            <div class="service-name"><?php echo $lang['srv_carpentry'] ?? ''; ?></div>
                            <p class="service-desc"><?php echo $lang['srv_carpentry_desc'] ?? ''; ?></p>
                        </div>

                        <div class="service-card">
                            <span class="service-icon">🧹</span>
                            <div class="service-name"><?php echo $lang['srv_cleaning'] ?? ''; ?></div>
                            <p class="service-desc"><?php echo $lang['srv_cleaning_desc'] ?? ''; ?></p>
                        </div>

                        <div class="service-card">
                            <span class="service-icon">🏗️</span>
                            <div class="service-name"><?php echo $lang['srv_renovation'] ?? ''; ?></div>
                            <p class="service-desc"><?php echo $lang['srv_renovation_desc'] ?? ''; ?></p>
                        </div>

                        <div class="service-card">
                            <span class="service-icon">🔒</span>
                            <div class="service-name"><?php echo $lang['srv_security'] ?? ''; ?></div>
                            <p class="service-desc"><?php echo $lang['srv_security_desc'] ?? ''; ?></p>
                        </div>

                    </div>
                </div>

                <!-- Closing note -->
                <div class="text-section mb-0">
                    <h3><?php echo $lang['services_note_title'] ?? ''; ?></h3>
                    <p class="mb-0"><?php echo $lang['services_note_desc'] ?? ''; ?></p>
                </div>

            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>