<?php
require_once '../config/db.php';

// حماية الواجهة
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'technician') {
    header("Location: ../login.php");
    exit();
}

$technician_id = $_SESSION['user_id'];
$error = '';
$success = '';

// 1. معالجة إرسال التذكرة
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_ticket'])) {
    $ticket_type = $_POST['ticket_type'] ?? 'complaint';
    $details = trim($_POST['details']);
    $reported_user_id = null;
    $reason = '';

    if ($ticket_type === 'complaint') {
        $target_type = $_POST['target_type'] ?? 'system';
        if ($target_type === 'person') {
            $reported_user_id = !empty($_POST['reported_user_id']) ? $_POST['reported_user_id'] : null;
        }
        $reason = $_POST['complaint_reason'] ?? '';
    } else {
        $reason = $_POST['suggestion_type'] ?? '';
    }

    if (empty($details) || empty($reason) || ($ticket_type === 'complaint' && $target_type === 'person' && !$reported_user_id)) {
        $error = "Please fill all required fields correctly.";
    } else {
        try {
            $sql = "INSERT INTO complaints (type, reporter_id, reported_user_id, reason, details, status) VALUES (?, ?, ?, ?, ?, 'pending')";
            $stmt = $pdo->prepare($sql);
            
            if ($stmt->execute([$ticket_type, $technician_id, $reported_user_id, $reason, $details])) {
                $success = "Your ticket has been submitted successfully.";

                // --- الإشعارات الذكية ---
                $notif_title = ($ticket_type === 'complaint') ? ($lang['notif_complaint_title'] ?? 'Complaint Received 🛡️') : ($lang['notif_suggestion_title'] ?? 'Suggestion Received 💡');
                $notif_message = ($ticket_type === 'complaint') ? ($lang['notif_complaint_msg'] ?? 'We have received your complaint.') : ($lang['notif_suggestion_msg'] ?? 'Thank you for your suggestion!');
                $notif_link = "Technician/complaints.php";

                $notif_stmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, link) VALUES (?, ?, ?, ?)");
                $notif_stmt->execute([$technician_id, $notif_title, $notif_message, $notif_link]);

            } else {
                $error = "Something went wrong. Please try again.";
            }
        } catch (Exception $e) {
            $error = "System Error: " . $e->getMessage();
        }
    }
}

// 2. جلب قائمة أصحاب المنازل الذين تعامل معهم الفني (الذكاء في الفلترة)
$stmt_customers = $pdo->prepare("
    SELECT DISTINCT u.id, u.first_name, u.last_name 
    FROM users u 
    JOIN job_requests j ON u.id = j.homeowner_id 
    JOIN bids b ON j.id = b.job_id 
    WHERE b.technician_id = ?
");
$stmt_customers->execute([$technician_id]);
$interacted_customers = $stmt_customers->fetchAll();

// 3. جلب التذاكر السابقة
$stmt_tickets = $pdo->prepare("
    SELECT c.*, u.first_name, u.last_name 
    FROM complaints c 
    LEFT JOIN users u ON c.reported_user_id = u.id 
    WHERE c.reporter_id = ? 
    ORDER BY c.created_at DESC
");
$stmt_tickets->execute([$technician_id]);
$my_tickets = $stmt_tickets->fetchAll();

include_once '../includes/header.php';
include_once '../includes/navbar.php';
?>

<div class="google-wrapper">
    <?php include_once '../includes/user_sidebar.php'; ?>

    <main class="google-content">
        <div class="mb-4">
            <h3 class="fw-bold mb-1"><?php echo $lang['complaints_suggestions'] ?? 'Complaints & Suggestions'; ?></h3>
        </div>

        <ul class="nav nav-pills mb-4 bg-white p-2 rounded-pill shadow-sm d-inline-flex border" id="complaintsTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active rounded-pill fw-bold px-4" id="submit-tab" data-bs-toggle="pill" data-bs-target="#submit-pane" type="button" role="tab">
                    <i class="bi bi-pencil-square me-1"></i> <?php echo $lang['submit_new_ticket'] ?? 'Submit New Ticket'; ?>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill fw-bold px-4" id="history-tab" data-bs-toggle="pill" data-bs-target="#history-pane" type="button" role="tab">
                    <i class="bi bi-clock-history me-1"></i> <?php echo $lang['my_tickets_history'] ?? 'My Tickets History'; ?>
                </button>
            </li>
        </ul>

        <div class="tab-content" id="complaintsTabContent">
            
            <div class="tab-pane fade show active" id="submit-pane" role="tabpanel">
                <div class="google-card p-4 mx-auto" style="max-width: 700px;">
                    <?php if(!empty($error)): ?>
                        <div class="alert alert-danger rounded-4 py-2 small"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <?php if(!empty($success)): ?>
                        <div class="alert alert-success rounded-4 py-2 small fw-bold"><i class="bi bi-check-circle-fill me-1"></i> <?php echo $success; ?></div>
                    <?php endif; ?>

                    <form action="complaints.php" method="POST">
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark small"><?php echo $lang['ticket_type'] ?? 'Ticket Type'; ?> *</label>
                            <select name="ticket_type" id="ticketType" class="form-select rounded-pill bg-light border-0 py-2 px-3" onchange="toggleFields()">
                                <option value="complaint"><?php echo $lang['complaint'] ?? 'Complaint'; ?></option>
                                <option value="suggestion"><?php echo $lang['suggestion'] ?? 'Suggestion'; ?></option>
                            </select>
                        </div>

                        <div id="complaintSection">
                            <div class="mb-4">
                                <label class="form-label fw-bold text-dark small"><?php echo $lang['complaint_target'] ?? 'Complaint About'; ?> *</label>
                                <select name="target_type" id="targetType" class="form-select rounded-pill bg-light border-0 py-2 px-3" onchange="toggleFields()">
                                    <option value="system"><?php echo $lang['system_error'] ?? 'System Error'; ?></option>
                                    <option value="person"><?php echo $lang['person_complaint'] ?? 'A Specific Person'; ?></option>
                                </select>
                            </div>

                            <div class="mb-4 d-none" id="customerSelectDiv">
                                <label class="form-label fw-bold text-dark small"><?php echo $lang['select_customer'] ?? 'Select Customer'; ?> *</label>
                                <select name="reported_user_id" id="reportedUserId" class="form-select rounded-pill bg-light border-0 py-2 px-3">
                                    <option value=""><?php echo $lang['select_customer'] ?? 'Select Customer'; ?>...</option>
                                    <?php foreach($interacted_customers as $cust): ?>
                                        <option value="<?php echo $cust['id']; ?>"><?php echo htmlspecialchars($cust['first_name'] . ' ' . $cust['last_name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold text-dark small"><?php echo $lang['complaint_reason'] ?? 'Complaint Category'; ?> *</label>
                                <select name="complaint_reason" class="form-select rounded-pill bg-light border-0 py-2 px-3">
                                    <option value="Unresponsive Customer"><?php echo $lang['unresponsive_customer'] ?? 'Unresponsive Customer'; ?></option>
                                    <option value="Payment Issue"><?php echo $lang['payment_issue'] ?? 'Payment / Financial Issue'; ?></option>
                                    <option value="Inaccurate Job Description"><?php echo $lang['inaccurate_description'] ?? 'Inaccurate Job Description'; ?></option>
                                    <option value="Personal Behavior"><?php echo $lang['personal_issue'] ?? 'Personal / Behavioral Issue'; ?></option>
                                    <option value="Other"><?php echo $lang['other_issue'] ?? 'Other'; ?></option>
                                </select>
                            </div>
                        </div>

                        <div id="suggestionSection" class="d-none">
                            <div class="mb-4">
                                <label class="form-label fw-bold text-dark small"><?php echo $lang['suggestion_type'] ?? 'Suggestion Type'; ?> *</label>
                                <select name="suggestion_type" class="form-select rounded-pill bg-light border-0 py-2 px-3">
                                    <option value="UI Improvement"><?php echo $lang['ui_improvement'] ?? 'Interface Improvement'; ?></option>
                                    <option value="New Feature"><?php echo $lang['new_feature'] ?? 'New Feature Request'; ?></option>
                                    <option value="Other"><?php echo $lang['other_issue'] ?? 'Other'; ?></option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark small"><?php echo $lang['details_label'] ?? 'Description / Details'; ?> *</label>
                            <textarea name="details" class="form-control rounded-4 bg-light border-0 p-3" rows="5" required></textarea>
                        </div>

                        <button type="submit" name="submit_ticket" class="btn btn-primary w-100 rounded-pill py-3 fw-bold mt-2"><?php echo $lang['submit_btn'] ?? 'Submit'; ?></button>
                    </form>
                </div>
            </div>

            <div class="tab-pane fade" id="history-pane" role="tabpanel">
                <div class="google-card p-4 mx-auto" style="max-width: 800px;">
                    <?php if(count($my_tickets) > 0): ?>
                        <div class="row g-3">
                            <?php foreach($my_tickets as $ticket): ?>
                                <div class="col-12">
                                    <div class="border rounded-4 p-4 bg-white shadow-sm hover-card">
                                        <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                                            <span class="badge <?php echo $ticket['type'] == 'complaint' ? 'bg-danger-subtle text-danger border border-danger' : 'bg-info-subtle text-info border border-info'; ?> rounded-pill px-3 py-2">
                                                <?php echo $ticket['type'] == 'complaint' ? ($lang['complaint'] ?? 'Complaint') : ($lang['suggestion'] ?? 'Suggestion'); ?>
                                            </span>
                                            <small class="text-muted fw-bold" dir="ltr"><i class="bi bi-calendar-event me-1"></i> <?php echo date('M d, Y', strtotime($ticket['created_at'])); ?></small>
                                        </div>
                                        
                                        <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($ticket['reason']); ?></h5>
                                        
                                        <?php if($ticket['reported_user_id']): ?>
                                            <p class="text-danger small mb-2"><i class="bi bi-person-fill me-1"></i> Target: <?php echo htmlspecialchars($ticket['first_name'] . ' ' . $ticket['last_name']); ?></p>
                                        <?php endif; ?>
                                        
                                        <p class="text-secondary mt-3 mb-3 bg-light p-3 rounded-4" style="line-height: 1.6;">"<?php echo htmlspecialchars($ticket['details']); ?>"</p>
                                        
                                        <div class="text-end pt-2">
                                            <span class="small fw-bold text-muted me-2">Status: </span>
                                            <?php 
                                                $badgeClass = 'bg-warning text-dark';
                                                if ($ticket['status'] == 'reviewed') $badgeClass = 'bg-info text-dark';
                                                if ($ticket['status'] == 'resolved') $badgeClass = 'bg-success';
                                            ?>
                                            <span class="badge <?php echo $badgeClass; ?> rounded-pill px-3 py-2">
                                                <?php echo ucfirst($ticket['status']); ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="bi bi-inbox fs-1 text-light-subtle d-block mb-3"></i>
                            <h5 class="text-muted fw-bold">No tickets yet</h5>
                            <p class="text-muted">You have not submitted any complaints or suggestions.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </main>
</div>

<style>
    .hover-card { transition: 0.2s; }
    .hover-card:hover { transform: translateY(-3px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important; }
</style>

<script>
function toggleFields() {
    const type = document.getElementById('ticketType').value;
    const complaintSection = document.getElementById('complaintSection');
    const suggestionSection = document.getElementById('suggestionSection');
    const targetType = document.getElementById('targetType').value;
    const customerSelect = document.getElementById('customerSelectDiv');
    const reportedUserInput = document.getElementById('reportedUserId');

    if (type === 'complaint') {
        complaintSection.classList.remove('d-none');
        suggestionSection.classList.add('d-none');
        
        if(targetType === 'person') {
            customerSelect.classList.remove('d-none');
            reportedUserInput.required = true;
        } else {
            customerSelect.classList.add('d-none');
            reportedUserInput.required = false;
        }
    } else {
        complaintSection.classList.add('d-none');
        suggestionSection.classList.remove('d-none');
        customerSelect.classList.add('d-none');
        reportedUserInput.required = false;
    }
}
document.addEventListener('DOMContentLoaded', toggleFields);
</script>

<?php include_once '../includes/footer.php'; ?>