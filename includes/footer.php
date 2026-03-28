<footer class="maintify-footer">
    <div class="container">
        <div class="row gy-5">

            <div class="col-lg-4 col-md-6">
                <h3 class="footer-brand mb-3">
                    <span class="brand-maint">Maint</span><span class="brand-ify">ify</span>
                </h3>
                <p class="footer-desc pe-lg-4">
                    <?php echo $lang['footer_desc'] ?? ''; ?>
                </p>
                <div class="social-links-wrapper mt-4">
                    <a href="#" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                    <a href="#" aria-label="Twitter"><i class="bi bi-twitter-x"></i></a>
                    <a href="#" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                    <a href="#" aria-label="LinkedIn"><i class="bi bi-linkedin"></i></a>
                </div>
            </div>

            <div class="col-lg-2 col-md-6">
                <h4 class="footer-title"><?php echo $lang['footer_quick_links'] ?? ''; ?></h4>
                <ul class="footer-links">
                    <li><a href="<?php echo $base_url ?? '/Maintify/'; ?>index.php"><?php echo $lang['footer_home'] ?? ''; ?></a></li>
                    <li><a href="<?php echo $base_url ?? '/Maintify/'; ?>pages/about.php"><?php echo $lang['footer_about'] ?? ''; ?></a></li>
                    <li><a href="<?php echo $base_url ?? '/Maintify/'; ?>pages/services.php"><?php echo $lang['footer_services'] ?? ''; ?></a></li>
                    <li><a href="<?php echo $base_url ?? '/Maintify/'; ?>pages/how_it_works.php"><?php echo $lang['footer_how_it_works'] ?? ''; ?></a></li>
                </ul>
            </div>

            <div class="col-lg-3 col-md-6">
                <h4 class="footer-title"><?php echo $lang['footer_support'] ?? ''; ?></h4>
                <ul class="footer-links">
                    <li><a href="<?php echo $base_url ?? '/Maintify/'; ?>pages/faq.php"><?php echo $lang['footer_faq'] ?? ''; ?></a></li>
                    <li><a href="<?php echo $base_url ?? '/Maintify/'; ?>pages/help.php"><?php echo $lang['footer_help_center'] ?? ''; ?></a></li>
                    <li><a href="<?php echo $base_url ?? '/Maintify/'; ?>pages/terms.php"><?php echo $lang['footer_terms'] ?? ''; ?></a></li>
                    <li><a href="<?php echo $base_url ?? '/Maintify/'; ?>pages/privacy.php"><?php echo $lang['footer_privacy'] ?? ''; ?></a></li>
                </ul>
            </div>

            <div class="col-lg-3 col-md-6">
                <h4 class="footer-title"><?php echo $lang['footer_contact'] ?? ''; ?></h4>
                <ul class="footer-links">
                    <li class="d-flex align-items-center mb-3" style="font-size: 14px; color: #9ca3af;">
                        <i class="bi bi-geo-alt me-2 fs-5" style="color: #F36F21;"></i> <?php echo $lang['footer_address'] ?? ''; ?>
                    </li>
                    <li class="d-flex align-items-center mb-3" style="font-size: 14px; color: #9ca3af;">
                        <i class="bi bi-envelope me-2 fs-5" style="color: #F36F21;"></i> support@maintify.com
                    </li>
                    <li class="d-flex align-items-center" style="font-size: 14px; color: #9ca3af;">
                        <i class="bi bi-telephone me-2 fs-5" style="color: #F36F21;"></i> +962 7X XXX XXXX
                    </li>
                </ul>
            </div>

        </div>

        <div class="footer-divider"></div>

        <div class="footer-bottom">
            <p class="copyright-text">
                &copy; <?php echo date('Y'); ?> <?php echo $lang['footer_copyright'] ?? ''; ?>
            </p>
            <span class="version-tag">v1.0.0 Beta</span>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>