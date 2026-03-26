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

// حساب نقاط التطابق (Match Score) بناءً على الاهتمامات المشتركة
$match_sql = "0";
if (count($my_interests) > 0) {
    // حماية الأرقام لتجنب ثغرات SQL Injection
    $safe_interests = implode(',', array_map('intval', $my_interests));
    $match_sql = "(SELECT COUNT(*) FROM user_interests ui2 WHERE ui2.user_id = u.id AND ui2.category_id IN ($safe_interests))";
}

// 2. استقبال متغيرات البحث والفلترة (إن وجدت)
$search = trim($_GET['search'] ?? '');
$category_filter = $_GET['category'] ?? '';

// 3. بناء الاستعلام الديناميكي لجلب بيانات الفنيين
$query = "SELECT u.id, u.first_name, u.last_name, u.city, t.bio, t.experience_years,
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

// تجميع النتائج لكل فني وترتيبها حسب نسبة التطابق ثم الأحدث
$query .= " GROUP BY u.id ORDER BY match_score DESC, u.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$technicians = $stmt->fetchAll();

// جلب الأقسام لقائمة الفلترة المنسدلة
$stmt_cats = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC");
$categories = $stmt_cats->fetchAll();

include_once '../includes/header.php';
include_once '../includes/navbar.php';
?>

<div class="container mt-5" style="min-height: 70vh;">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="fw-bold">Technicians Feed</h2>
            <p class="text-muted">Find the right professional for your home maintenance needs.</p>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-5 bg-light">
        <div class="card-body">
            <form action="feed.php" method="GET" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label fw-bold">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Search by name or bio..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-5">
                    <label class="form-label fw-bold">Filter by Category</label>
                    <select name="category" class="form-select">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo ($category_filter == $cat['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100 fw-bold">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-4">
        <?php if (count($technicians) > 0): ?>
            <?php foreach ($technicians as $tech): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card shadow-sm h-100 border-0">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="fw-bold mb-0 text-primary">
                                    <?php echo htmlspecialchars($tech['first_name'] . ' ' . $tech['last_name']); ?>
                                </h5>
                                <?php if ($tech['match_score'] > 0): ?>
                                    <span class="badge bg-success" title="Based on your profile interests">Recommended</span>
                                <?php endif; ?>
                            </div>

                            <p class="text-muted small mb-3">
                                <i class="text-secondary">📍 <?php echo htmlspecialchars($tech['city'] ?? 'Location not set'); ?></i> |
                                <strong><?php echo $tech['experience_years']; ?> Yrs Exp.</strong>
                            </p>

                            <p class="card-text small text-truncate-2" style="line-height: 1.4;">
                                <?php echo htmlspecialchars($tech['bio']); ?>
                            </p>

                            <div class="mb-4">
                                <span class="text-muted" style="font-size: 12px; font-weight: 600;">SPECIALTIES:</span><br>
                                <span class="badge bg-light text-dark border w-100 text-start overflow-hidden text-truncate">
                                    <?php echo htmlspecialchars($tech['specialties'] ?? 'Not specified'); ?>
                                </span>
                            </div>

                            <div class="d-flex gap-2 mt-auto">
                                <button class="btn btn-outline-primary w-50 btn-sm">View Profile</button>
                                <a href="../chat.php?user_id=<?php echo $tech['id']; ?>" class="btn btn-primary w-50 btn-sm">Chat</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <h4 class="text-muted">No technicians found</h4>
                <p>Try adjusting your search criteria or removing filters.</p>
                <a href="feed.php" class="btn btn-outline-secondary mt-2">Clear Filters</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>