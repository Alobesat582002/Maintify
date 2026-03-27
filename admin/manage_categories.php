<?php
require_once '../config/db.php';

// حماية الصفحة: للأدمن فقط
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$success = '';
$error = '';

// معالجة عمليات (إضافة، تعديل، حذف)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        
        if (!empty($name) && !empty($description)) {
            $stmt = $pdo->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
            if ($stmt->execute([$name, $description])) {
                $success = "Category added successfully.";
            } else {
                $error = "Failed to add category.";
            }
        } else {
            $error = "All fields are required.";
        }
    } elseif ($action === 'edit') {
        $id = $_POST['category_id'];
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        
        if (!empty($name) && !empty($description)) {
            $stmt = $pdo->prepare("UPDATE categories SET name = ?, description = ? WHERE id = ?");
            if ($stmt->execute([$name, $description, $id])) {
                $success = "Category updated successfully.";
            } else {
                $error = "Failed to update category.";
            }
        } else {
            $error = "All fields are required.";
        }
    } elseif ($action === 'delete') {
        $id = $_POST['category_id'];
        try {
            $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
            $stmt->execute([$id]);
            $success = "Category deleted successfully.";
        } catch (PDOException $e) {
            // الكود 23000 يعني أن هناك ارتباطات (Foreign Key Constraint)
            if ($e->getCode() == 23000) {
                $error = "Cannot delete this category because it is linked to existing jobs or technician interests.";
            } else {
                $error = "Database error: " . $e->getMessage();
            }
        }
    }
}

// جلب جميع الأقسام
$stmt = $pdo->query("SELECT * FROM categories ORDER BY id DESC");
$categories = $stmt->fetchAll();

include_once '../includes/header.php';
include_once '../includes/navbar.php';
?>

<div class="admin-wrapper">
    <?php include_once 'includes/sidebar.php'; ?>

    <div class="admin-content p-4 p-md-5 w-100">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold">Manage Categories</h2>
            <div>
                <button class="btn btn-primary fw-bold" data-bs-toggle="modal" data-bs-target="#addCategoryModal">+ Add New Category</button>
            </div>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success fw-bold"><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger fw-bold"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 py-3">ID</th>
                                <th class="py-3">Category Name</th>
                                <th class="py-3">Description</th>
                                <th class="pe-4 py-3 text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $cat): ?>
                                <tr>
                                    <td class="ps-4 text-muted">#<?php echo $cat['id']; ?></td>
                                    <td class="fw-bold text-primary"><?php echo htmlspecialchars($cat['name']); ?></td>
                                    <td class="text-muted" style="max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        <?php echo htmlspecialchars($cat['description']); ?>
                                    </td>
                                    <td class="pe-4 text-end">
                                        <button class="btn btn-sm btn-outline-primary fw-bold me-1" data-bs-toggle="modal" data-bs-target="#editCategoryModal<?php echo $cat['id']; ?>">
                                            Edit
                                        </button>
                                        
                                        <form action="manage_categories.php" method="POST" class="d-inline">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="category_id" value="<?php echo $cat['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger fw-bold" onclick="return confirm('Are you sure you want to delete this category?');">
                                                Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>

                                <div class="modal fade" id="editCategoryModal<?php echo $cat['id']; ?>" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow">
                                            <div class="modal-header bg-primary text-white border-0">
                                                <h5 class="modal-title fw-bold">Edit Category</h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="manage_categories.php" method="POST">
                                                <div class="modal-body p-4 text-start">
                                                    <input type="hidden" name="action" value="edit">
                                                    <input type="hidden" name="category_id" value="<?php echo $cat['id']; ?>">
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Category Name</label>
                                                        <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($cat['name']); ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Description</label>
                                                        <textarea name="description" class="form-control" rows="3" required><?php echo htmlspecialchars($cat['description']); ?></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-0">
                                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary fw-bold">Save Changes</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                            <?php endforeach; ?>
                            
                            <?php if (empty($categories)): ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">No categories found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white border-0">
                <h5 class="modal-title fw-bold">Add New Category</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="manage_categories.php" method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Category Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g., Plumbing" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Brief description of the services included..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark fw-bold">Add Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>