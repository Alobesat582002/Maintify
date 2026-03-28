<?php
$lang_code = $current_lang ?? 'en';
$dir = ($lang_code === 'ar') ? 'rtl' : 'ltr';
?>
<!DOCTYPE html>
<html lang="<?php echo $lang_code; ?>" dir="<?php echo $dir; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Maintify - Home Facility Management Platform">
    <meta name="theme-color" content="#4f46e5">

    <title>Maintify - Home Facility Management</title>
    <link rel="icon" type="image/png" href="/Maintify/assets/images/logo.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">

    <?php if ($lang_code === 'ar'): ?>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
        <style>
            body {
                font-family: 'Tajawal', sans-serif !important;
            }

            /* تعديل اتجاه الأيقونات في حالة العربي */
            .bi-chevron-right::before {
                content: "\f284";
            }

            /* يقلب السهم لليسار */
            .bi-chevron-left::before {
                content: "\f285";
            }

            /* يقلب السهم لليمين */
        </style>
    <?php else: ?>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php endif; ?>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <link rel="stylesheet" href="/Maintify/assets/css/style.css?v=<?php echo time(); ?>">

    <link rel="stylesheet" href="/Maintify/assets/css/nav.css?v=<?php echo time(); ?>">

    <link rel="icon" type="image/png" href="/Maintify/assets/images/favicon.png">

    <link rel="stylesheet" href="/Maintify/assets/css/foot.css">


</head>

<body class="d-flex flex-column min-vh-100">