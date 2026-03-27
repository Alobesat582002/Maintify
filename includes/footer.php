<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintify Footer Demo</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    

    <!-- Custom CSS -->
     <!-- <style>/* يخلي الصفحة تاخذ كامل الطول */
html, body {
    height: 100%;
    margin: 0;
}

/* أهم جزء لتثبيت الفوتر */
body {
    display: flex;
    flex-direction: column;
}

/* أي محتوى قبل الفوتر */
main {
    flex: 1;
}

/* الفوتر */
.maintify-footer {
    background: #1a1a2e;
    color: #fff;
    padding: 40px 0 20px;
    margin-top: auto;
}

/* العنوان */
.footer-brand {
    font-size: 24px;
    font-weight: bold;
}

.footer-brand span {
    color: #F36F21;
}

/* الوصف */
.footer-desc {
    font-size: 14px;
    opacity: 0.9;
}

/* السوشيال */
.social-links-wrapper a {
    color: #fff;
    font-size: 20px;
    margin: 0 8px;
    transition: 0.3s;
}

.social-links-wrapper a:hover {
    color: #F36F21;
    transform: translateY(-3px);
}

/* الخط الفاصل */
.footer-divider {
    border-color: rgba(255,255,255,0.2);
    margin: 20px 0;
}

/* الحقوق */
.copyright-text {
    font-size: 14px;
}

/* النسخة */
.version-tag {
    display: block;
    font-size: 12px;
    opacity: 0.7;
}

/* النص السفلي */
.footer-bottom {
    font-size: 13px;
    opacity: 0.8;
}</style>
     -->
</head>
<body>



    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
<footer class="maintify-footer">
    <div class="container">
        <div class="row align-items-center gy-4">
            <div class="col-md-4 text-center text-md-start">
                <h5 class="footer-brand">Maintify<span>.</span></h5>
                <p class="footer-desc">منصة لإدارة الصيانة بسهولة واحترافية. حلول ذكية لخدمات منزلية أسرع.</p>
            </div>

            <div class="col-md-4 text-center">
                <div class="social-links-wrapper">
                    <a href="https://www.facebook.com/universityofjordanofficial/" target="_blank" title="Facebook"><i class="bi bi-facebook"></i></a>
                    <a href="https://x.com/ujedujo" target="_blank" title="Twitter"><i class="bi bi-twitter-x"></i></a>
                    <a href="https://www.linkedin.com/school/university-of-jordan/" target="_blank" title="LinkedIn"><i class="bi bi-linkedin"></i></a>
                    <a href="https://www.instagram.com/universityofjordanofficial/" target="_blank" title="Instagram"><i class="bi bi-instagram"></i></a>
                    <a href="https://github.com/" target="_blank" title="GitHub"><i class="bi bi-github"></i></a>
                </div>
            </div>

            <div class="col-md-4 text-center text-md-end">
                <p class="copyright-text mb-0">
                    &copy; <?php echo date("Y"); ?> Maintify. جميع الحقوق محفوظة.
                </p>
                <span class="version-tag">Version <?php echo isset($lang['Version']) ? $lang['Version'] : '1.0.0'; ?></span>
            </div>
        </div>
        
        <hr class="footer-divider">
        
        <div class="footer-bottom text-center">
            <p class="small text-muted">بكل فخر - مشروع تخرج الجامعة الأردنية</p>
        </div>
    </div>
</footer>
</html>