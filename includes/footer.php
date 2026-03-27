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

    <style>
        html, body {
            height: 100%;
            margin: 0;
        }

        body {
            display: flex;
            flex-direction: column;
        }

        main {
            flex: 1;
        }

        .maintify-footer {
            background: #1a1a2e;
            color: #fff;
            padding: 40px 0 20px;
            margin-top: auto;
        }

        .footer-brand {
            font-size: 24px;
            font-weight: bold;
        }

        .footer-brand span {
            color: #F36F21;
        }

        .footer-desc {
            font-size: 14px;
            opacity: 0.9;
        }

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

        .footer-divider {
            border-color: rgba(255,255,255,0.2);
            margin: 20px 0;
        }

        .copyright-text {
            font-size: 14px;
        }

        .version-tag {
            display: block;
            font-size: 12px;
            opacity: 0.7;
        }

        .footer-bottom {
            font-size: 13px;
            opacity: 0.8;
        }
    </style>
</head>

<body>

    <!-- محتوى الصفحة -->
    <main class="container py-5">
       
    </main>

    <!-- الفوتر -->
    <footer class="maintify-footer">
        <div class="container">
            <div class="row align-items-center gy-4">
                
                <div class="col-md-4 text-center text-md-start">
                    <h5 class="footer-brand">Maintify<span>.</span></h5>
                    <p class="footer-desc">
                        منصة لإدارة الصيانة بسهولة واحترافية. حلول ذكية لخدمات منزلية أسرع.
                    </p>
                </div>

                <div class="col-md-4 text-center">
                    <div class="social-links-wrapper">
                        <a href="https://www.facebook.com/"><i class="bi bi-facebook"></i></a>
                        <a href="https://www.linkedin.com/login/"><i class="bi bi-linkedin"></i></a>
                        <a href="https://www.instagram.com/"><i class="bi bi-instagram"></i></a>
                        <a href="https://github.com/login"><i class="bi bi-github"></i></a>
                    </div>
                </div>

                <div class="col-md-4 text-center text-md-end">
                    <p class="copyright-text mb-0">
                        &copy; 2026 Maintify. جميع الحقوق محفوظة.
                    </p>
                    <span class="version-tag">Version 1.0.0</span>
                </div>

            </div>

            <hr class="footer-divider">

          
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>