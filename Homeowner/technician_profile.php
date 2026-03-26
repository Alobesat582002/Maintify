<?php
require_once '../config/db.php';

// حماية الواجهة
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'homeowner') {
    header("Location: ../login.php");
    exit();
}

$tech_id = $_GET['id'] ?? null;

if (!$tech_id) {
    die("Technician ID is required.");
}

// 1. جلب بيانات الفني الأساسية
$stmt_tech = $pdo->prepare("
    SELECT u.id, u.first_name, u.last_name, u.city, u.profile_image, t.bio, t.experience_years 
    FROM users u 
    LEFT JOIN technician_profiles t ON u.id = t.user_id 
    WHERE u.id = ? AND u.role = 'technician'
");
$stmt_tech->execute([$tech_id]);
$tech = $stmt_tech->fetch();

if (!$tech) {
    die("Technician not found.");
}

// 2. جلب التخصصات
$stmt_skills = $pdo->prepare("
    SELECT c.name 
    FROM user_interests ui 
    JOIN categories c ON ui.category_id = c.id 
    WHERE ui.user_id = ?
");
$stmt_skills->execute([$tech_id]);
$specialties = $stmt_skills->fetchAll(PDO::FETCH_COLUMN);

// 3. جلب إحصائيات التقييمات (المتوسط والعدد)
$stmt_stats = $pdo->prepare("
    SELECT COUNT(id) as total_reviews, AVG(rating) as avg_rating 
    FROM reviews 
    WHERE technician_id = ?
");
$stmt_stats->execute([$tech_id]);
$stats = $stmt_stats->fetch();
$avg_rating = $stats['avg_rating'] ? number_format($stats['avg_rating'], 1) : 0;
$total_reviews = $stats['total_reviews'];

// 4. جلب قائمة التقييمات مع بيانات أصحاب المنازل
$stmt_reviews = $pdo->prepare("
    SELECT r.rating, r.comment, r.created_at, h.first_name, h.last_name, h.profile_image 
    FROM reviews r 
    JOIN users h ON r.homeowner_id = h.id 
    WHERE r.technician_id = ? 
    ORDER BY r.created_at DESC
");
$stmt_reviews->execute([$tech_id]);
$reviews = $stmt_reviews->fetchAll();

include_once '../includes/header.php';
include_once '../includes/navbar.php';
?>

<div class="container mt-5" style="max-width: 900px; min-height: 70vh;">
    <a href="feed.php" class="btn btn-sm btn-outline-secondary mb-4">← Back to Feed</a>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-4 text-center p-4 sticky-top" style="top: 100px;">
                <?php 
                    $img_src = (!empty($tech['profile_image']) && $tech['profile_image'] !== 'default.png') 
                        ? "../assets/images/avatars/" . $tech['profile_image'] 
                        : "../assets/images/logo.png";
                ?>
                <img src="<?php echo htmlspecialchars($img_src); ?>" alt="Profile" class="rounded-circle object-fit-cover shadow-sm border mx-auto mb-3" style="width: 120px; height: 120px;">
                
                <h4 class="fw-bold mb-1"><?php echo htmlspecialchars($tech['first_name'] . ' ' . $tech['last_name']); ?></h4>
                <p class="text-muted small mb-3"><i class="bi bi-geo-alt-fill text-danger me-1"></i> <?php echo htmlspecialchars($tech['city'] ?? 'Location not set'); ?></p>
                
                <div class="d-flex justify-content-center gap-3 mb-4">
                    <div class="text-center">
                        <h5 class="fw-bold mb-0"><?php echo $avg_rating; ?> <i class="bi bi-star-fill text-warning fs-6"></i></h5>
                        <small class="text-muted">Rating</small>
                    </div>
                    <div class="border-end"></div>
                    <div class="text-center">
                        <h5 class="fw-bold mb-0"><?php echo $total_reviews; ?></h5>
                        <small class="text-muted">Reviews</small>
                    </div>
                    <div class="border-end"></div>
                    <div class="text-center">
                        <h5 class="fw-bold mb-0"><?php echo $tech['experience_years']; ?></h5>
                        <small class="text-muted">Years Exp.</small>
                    </div>
                </div>

                <a href="../chat.php?user_id=<?php echo $tech['id']; ?>" class="btn btn-primary w-100 fw-bold py-2"><i class="bi bi-chat-dots me-2"></i> Message Now</a>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm border-0 rounded-4 mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3">About Me</h5>
                    <p class="text-muted" style="line-height: 1.6; white-space: pre-wrap;"><?php echo htmlspecialchars($tech['bio'] ?? 'No bio provided.'); ?></p>
                    
                    <h6 class="fw-bold mt-4 mb-3">Specialties</h6>
                    <div class="d-flex flex-wrap gap-2">
                        <?php if(count($specialties) > 0): ?>
                            <?php foreach($specialties as $spec): ?>
                                <span class="badge bg-light text-dark border p-2"><i class="bi bi-tools me-1"></i> <?php echo htmlspecialchars($spec); ?></span>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <span class="text-muted small">No specialties listed.</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4">Reviews (<?php echo $total_reviews; ?>)</h5>
                    
                    <?php if (count($reviews) > 0): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($reviews as $review): ?>
                                <div class="list-group-item px-0 py-3 border-bottom">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div class="d-flex align-items-center">
                                            <?php 
                                                $rev_img = (!empty($review['profile_image']) && $review['profile_image'] !== 'default.png') 
                                                    ? "../assets/images/avatars/" . $review['profile_image'] 
                                                    : "../assets/images/logo.png";
                                            ?>
                                            <img src="<?php echo htmlspecialchars($rev_img); ?>" class="rounded-circle object-fit-cover me-2" style="width: 40px; height: 40px;">
                                            <div>
                                                <h6 class="fw-bold mb-0"><?php echo htmlspecialchars($review['first_name'] . ' ' . $review['last_name']); ?></h6>
                                                <small class="text-muted"><?php echo date('F j, Y', strtotime($review['created_at'])); ?></small>
                                            </div>
                                        </div>
                                        <div class="text-warning">
                                            <?php 
                                            for ($i=1; $i<=5; $i++) {
                                                echo ($i <= $review['rating']) ? '<i class="bi bi-star-fill"></i>' : '<i class="bi bi-star text-muted"></i>';
                                            }
                                            ?>
                                        </div>
                                    </div>
                                    <p class="text-muted mb-0 mt-2 ms-5">"<?php echo htmlspecialchars($review['comment']); ?>"</p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="bi bi-star-half fs-1 text-muted d-block mb-2"></i>
                            <p class="text-muted">No reviews yet. Be the first to hire and review this technician!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>