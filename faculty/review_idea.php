<?php
include "../includes/session.php";
include "../config/database.php";

$faculty_id = $_SESSION['user_id'];
$idea_id = $_GET['id'] ?? 0;

$message = "";
$message_type = "";

// Handle Review Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $status = trim($_POST['status']);
    $comment = trim($_POST['comment']);

    if (!empty($status) && !empty($comment)) {
        // Update idea status
        $update_sql = "UPDATE ideas SET status = ? WHERE idea_id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("si", $status, $idea_id);
        $stmt->execute();
        $stmt->close();

        // Check if review already exists
        $check_sql = "SELECT review_id FROM reviews WHERE idea_id = ? AND faculty_id = ?";
        $stmt = $conn->prepare($check_sql);
        $stmt->bind_param("ii", $idea_id, $faculty_id);
        $stmt->execute();
        $review_exists = $stmt->get_result()->num_rows > 0;
        $stmt->close();

        if ($review_exists) {
            $rev_sql = "UPDATE reviews SET comment = ? WHERE idea_id = ? AND faculty_id = ?";
            $stmt = $conn->prepare($rev_sql);
            $stmt->bind_param("sii", $comment, $idea_id, $faculty_id);
        } else {
            $rev_sql = "INSERT INTO reviews (idea_id, faculty_id, comment) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($rev_sql);
            $stmt->bind_param("iis", $idea_id, $faculty_id, $comment);
        }

        if ($stmt->execute()) {
            $message = "Evaluation submitted successfully!";
            $message_type = "success";
        } else {
            $message = "Error submitting evaluation.";
            $message_type = "danger";
        }
        $stmt->close();
    } else {
        $message = "Please fill in all required fields.";
        $message_type = "warning";
    }
}

/* 
   FETCH IDEA DETAILS WITH FILE_PATH 
*/
$sql = "SELECT 
            i.idea_id, 
            i.title, 
            i.category, 
            i.description, 
            i.file_path, 
            i.status, 
            i.submitted_at, 
            u.name AS student_name, 
            u.email AS student_email, 
            u.department AS student_dept,
            r.comment AS faculty_comment
        FROM ideas i 
        JOIN users u ON i.student_id = u.user_id 
        LEFT JOIN reviews r ON i.idea_id = r.idea_id AND r.faculty_id = ?
        WHERE i.idea_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $faculty_id, $idea_id);
$stmt->execute();
$idea = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$idea) {
    header("Location: pending_ideas.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evaluate Idea - EWU Innovation Hub</title>
    
    <link rel="icon" type="image/png" href="../assets/images/ewu_logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <style>
        body { background-color: #0f172a; color: #f8fafc; min-height: 100vh; overflow-x: hidden; }
        .dashboard-wrapper { display: flex; min-height: 100vh; }
        .main-content { flex: 1; padding: 30px; width: 100%; }
        .card.bg-dark {
            background: rgba(30, 41, 59, 0.7) !important;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
            border-radius: 12px;
        }
        .text-cyan { color: #06b6d4 !important; }
        .form-control, .form-select {
            background-color: rgba(15, 23, 42, 0.8) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            color: #fff !important;
        }
        .form-control:focus, .form-select:focus {
            border-color: #06b6d4 !important;
            box-shadow: 0 0 0 0.25rem rgba(6, 182, 212, 0.25);
        }
    </style>
</head>
<body>

<div class="dashboard-wrapper">
    <?php include "../includes/faculty_sidebar.php"; ?>

    <main class="main-content">
        <div class="d-flex justify-content-between align-items-center pb-3 mb-4 border-bottom border-secondary">
            <h1 class="h2 text-cyan mb-0">Evaluate Project Idea Proposal 📝</h1>
            <a href="pending_ideas.php" class="btn btn-outline-secondary btn-sm">← Back to Pending Ideas</a>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- Left Side: Idea Details -->
            <div class="col-lg-7">
                <div class="card bg-dark text-white p-4 shadow-sm mb-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h3 class="text-white mb-0 fw-bold"><?php echo htmlspecialchars($idea['title']); ?></h3>
                        <span class="badge bg-secondary fs-6"><?php echo htmlspecialchars($idea['category']); ?></span>
                    </div>

                    <p class="text-white-50 small mb-4">
                        👤 <strong>Student:</strong> <span class="text-info"><?php echo htmlspecialchars($idea['student_name']); ?></span> | 
                        <strong>Dept:</strong> <?php echo htmlspecialchars($idea['student_dept']); ?> | 
                        <strong>Email:</strong> <a href="mailto:<?php echo htmlspecialchars($idea['student_email']); ?>" class="text-cyan text-decoration-none"><?php echo htmlspecialchars($idea['student_email']); ?></a>
                    </p>

                    <h6 class="text-cyan fw-bold mb-2">Project Description:</h6>
                    <p style="white-space: pre-line;" class="text-white-50 mb-4"><?php echo htmlspecialchars($idea['description']); ?></p>

                    <!-- 📄 Attached Proposal File Download Option -->
                    <?php if (!empty($idea['file_path'])): ?>
                        <div class="my-3 p-3 bg-slate-800 rounded border border-secondary d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div>
                                <span class="text-white fw-semibold d-block">📄 Attached Proposal File</span>
                                <small class="text-white-50">Review attached document submitted by student</small>
                            </div>
                            <a href="../uploads/<?php echo $idea['file_path']; ?>" class="btn btn-sm btn-info text-dark fw-bold" download>
                                📥 Download File
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="my-3 p-2 bg-dark rounded border border-secondary text-white-50 small">
                            ⚠️ No attached document found for this proposal.
                        </div>
                    <?php endif; ?>

                    <hr class="border-secondary">
                    <div class="small text-white-50">
                        Submitted Date: <?php echo date('F d, Y \a\t h:i A', strtotime($idea['submitted_at'])); ?>
                    </div>
                </div>
            </div>

            <!-- Right Side: Evaluation Form -->
            <div class="col-lg-5">
                <div class="card bg-dark text-white p-4 shadow-sm">
                    <h5 class="text-cyan fw-bold mb-3">Evaluation Form</h5>
                    
                    <form action="review_idea.php?id=<?php echo $idea_id; ?>" method="POST">
                        <div class="mb-3">
                            <label for="status" class="form-label text-white">Decision <span class="text-danger">*</span></label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="" disabled <?php echo empty($idea['status']) || $idea['status'] == 'pending' ? 'selected' : ''; ?>>-- Select Decision --</option>
                                <option value="approved" <?php echo ($idea['status'] == 'approved') ? 'selected' : ''; ?>>Approve Idea</option>
                                <option value="rejected" <?php echo ($idea['status'] == 'rejected') ? 'selected' : ''; ?>>Reject Idea</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="comment" class="form-label text-white">Feedback / Faculty Comments <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="comment" name="comment" rows="5" placeholder="Write detailed feedback or suggestions for the student..." required><?php echo htmlspecialchars($idea['faculty_comment'] ?? ''); ?></textarea>
                        </div>

                        <button type="submit" class="btn btn-info text-dark fw-bold w-100 py-2">Submit Evaluation</button>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>

<?php $conn->close(); ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>