<?php
require_once '../config/db.php';

// حماية الواجهة
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'homeowner') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 1. جلب اهتمامات صاحب المنزل لعمل (Smart Ranking)
$stmt = $pdo->prepare("SELECT category_id FROM user_interests WHERE user_id = ?");
$stmt->execute([$user_id]);
$my_interests = $stmt->fetchAll(PDO::FETCH_COLUMN);

// حساب نقاط التطابق
$match_sql = "0";
if (count($my_interests) > 0) {
    $safe_interests = implode(',', array_map('intval', $my_interests));
    $match_sql = "(SELECT COUNT(*) FROM user_interests ui2 WHERE ui2.user_id = u.id AND ui2.category_id IN ($safe_interests))";
}

// 2. استقبال متغيرات البحث
$search = trim($_GET['search'] ?? '');
$category_filter = $_GET['category'] ?? '';

// 3. بناء الاستعلام الديناميكي (تم إضافة u.profile_image)
$query = "SELECT u.id, u.first_name, u.last_name, u.city, u.profile_image, t.bio, t.experience_years,
          GROUP_CONCAT(c.name SEPARATOR ' • ') as specialties,
          $match_sql as match_score
          FROM users u
          JOIN technician_profiles t ON u.id = t.user_id
          LEFT JOIN user_interests ui ON u.id = ui.user_id
          LEFT JOIN categories c ON ui.category_id = c.id
          WHERE u.role = 'technician' ";

$params = [];

if (!empty($search)) {
    $query .= " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR t.bio LIKE ?) ";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($category_filter)) {
    $query .= " AND u.id IN (SELECT user_id FROM user_interests WHERE category_id = ?) ";
    $params[] = $category_filter;
}

$query .= " GROUP BY u.id ORDER BY match_score DESC, u.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$technicians = $stmt->fetchAll();

$stmt_cats = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC");
$categories = $stmt_cats->fetchAll();

include_once '../includes/header.php';
include_once '../includes/navbar.php';
?>

<div class="google-wrapper">
    <?php include_once '../includes/user_sidebar.php'; ?>

    <main class="google-content">
        <div class="mb-4">
            <h3 class="fw-bold mb-1"><?php echo $lang['techs_feed']; ?></h3>
            <p class="text-muted"><?php echo $lang['find_right_pro']; ?></p>
        </div>

        <div class="google-card p-4 mb-4">
            <form action="feed.php" method="GET" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label fw-bold text-muted small ms-2"><?php echo $lang['search_techs']; ?></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0 rounded-start-pill ps-4"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-0 bg-light rounded-end-pill py-2" placeholder="<?php echo $lang['search_placeholder']; ?>" value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                </div>
                <div class="col-md-5">
                    <label class="form-label fw-bold text-muted small ms-2"><?php echo $lang['filter_category']; ?></label>
                    <select name="category" class="form-select border-0 bg-light rounded-pill py-2 px-4">
                        <option value=""><?php echo $lang['all_categories']; ?></option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo ($category_filter == $cat['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100 fw-bold rounded-pill py-2"><?php echo $lang['filter_btn']; ?></button>
                </div>
            </form>
        </div>

        <div class="row g-4">
            <?php if (count($technicians) > 0): ?>
                <?php foreach ($technicians as $tech): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="google-card p-4 h-100 d-flex flex-column">
                            
                            <div class="d-flex align-items-center mb-3">
                                <?php 
                                    $img_src = (!empty($tech['profile_image']) && $tech['profile_image'] !== 'default.png') 
                                        ? "../assets/images/avatars/" . $tech['profile_image'] 
                                        : "../assets/images/logo.png";
                                ?>
                                <img src="<?php echo htmlspecialchars($img_src); ?>" alt="Avatar" class="rounded-circle object-fit-cover border" style="width: 65px; height: 65px;">
                                <div class="ms-3">
                                    <h5 class="fw-bold mb-0 text-dark">
                                        <?php echo htmlspecialchars($tech['first_name'] . ' ' . $tech['last_name']); ?>
                                    </h5>
                                    <?php if ($tech['match_score'] > 0): ?>
                                        <span class="badge bg-success-subtle text-success border border-success mt-1 rounded-pill px-2" style="font-size: 0.7rem;"><?php echo $lang['recommended']; ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="d-flex text-muted small mb-3 bg-light p-2 rounded-4">
                                <div class="me-3"><i class="bi bi-geo-alt-fill text-danger me-1"></i> <?php echo htmlspecialchars($tech['city'] ?? $lang['unknown_location']); ?></div>
                                <div><i class="bi bi-briefcase-fill text-primary me-1"></i> <?php echo $tech['experience_years']; ?> <?php echo $lang['yrs_exp']; ?></div>
                            </div>

                            <p class="card-text text-muted mb-3" style="font-size: 14px; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; line-clamp: 3; overflow: hidden;">
                                <?php echo htmlspecialchars($tech['bio']); ?>
                            </p>

                            <div class="mb-4 mt-auto">
                                <span class="badge bg-light text-secondary border w-100 text-start overflow-hidden text-truncate py-2 rounded-pill px-3">
                                    <i class="bi bi-tools me-1"></i> <?php echo htmlspecialchars($tech['specialties'] ?? $lang['no_specialties']); ?>
                                </span>
                            </div>

                            <div class="d-flex gap-2">
                                <a href="technician_profile.php?id=<?php echo $tech['id']; ?>" class="btn btn-outline-primary w-50 fw-bold rounded-pill"><?php echo $lang['view_profile']; ?></a>
                                <a href="../chat.php?user_id=<?php echo $tech['id']; ?>" class="btn btn-primary w-50 fw-bold rounded-pill"><i class="bi bi-chat-dots me-1"></i> <?php echo $lang['chat']; ?></a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <div class="google-card p-5 mx-auto" style="max-width: 500px;">
                        <i class="bi bi-search fs-1 text-muted d-block mb-3"></i>
                        <h4 class="fw-bold text-dark"><?php echo $lang['no_techs_found']; ?></h4>
                        <p class="text-muted"><?php echo $lang['try_adjusting_search']; ?></p>
                        <a href="feed.php" class="btn btn-primary px-4 mt-2 rounded-pill fw-bold"><?php echo $lang['clear_filters']; ?></a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php include_once '../includes/footer.php'; ?>