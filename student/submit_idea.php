<?php
// Include session guard and database configuration
include "../includes/session.php";
include "../config/database.php"; // Fixed path from ../config.php to ../config/database.php

$student_id = $_SESSION['user_id'];
$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $category = trim($_POST['category']);
    $description = trim($_POST['description']);
    $file_path = NULL;

    // Handle File Upload
    if (isset($_FILES['idea_file']) && $_FILES['idea_file']['error'] == 0) {
        // target directory set to ../upload/ as per your folder structure
        $target_dir = "../upload/";
        
        // Auto-create folder if it doesn't exist to prevent "Failed to open stream" error
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_name = $_FILES['idea_file']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_exts = array('pdf', 'doc', 'docx');

        if (in_array($file_ext, $allowed_exts)) {
            // Generate unique filename to avoid overwriting
            $new_file_name = rand(10000000, 99999999) . "_" . preg_replace("/[^a-zA-Z0-9\._-]/", "_", $file_name);
            $target_file = $target_dir . $new_file_name;

            if (move_uploaded_file($_FILES['idea_file']['tmp_name'], $target_file)) {
                $file_path = $new_file_name;
            } else {
                $message = "Failed to upload file to target directory.";
                $message_type = "danger";
            }
        } else {
            $message = "Invalid file type! Only PDF, DOC, and DOCX files are allowed.";
            $message_type = "danger";
        }
    } else {
        $message = "Please upload a document for your idea proposal.";
        $message_type = "danger";
    }

    // Insert into Database if no file error
    if (empty($message)) {
        $sql = "INSERT INTO ideas (student_id, title, category, description, file_path, status, submitted_at) 
                VALUES (?, ?, ?, ?, ?, 'pending', NOW())";
        
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("issss", $student_id, $title, $category, $description, $file_path);
            if ($stmt->execute()) {
                $message = "Idea submitted successfully! Faculty will review it soon.";
                $message_type = "success";
            } else {
                $message = "Database error: Unable to submit idea.";
                $message_type = "danger";
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit New Idea - EWU Innovation Hub</title>
    
    <link rel="icon" type="image/png" href="../assets/images/ewu_logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <style>
        body { background-color: #0f172a; color: #f8fafc; min-height: 100vh; }
        .main-content { margin-left: 250px; padding: 30px; }
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
        @media (max-width: 768px) { .main-content { margin-left: 0; padding: 15px; } }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- Student Sidebar Included -->
        <?php include "../includes/student_sidebar.php"; ?>

        <!-- Main Content Area -->
        <main class="col-md-9 ms-sm-auto col-lg-10 main-content">
            
            <div class="pt-3 pb-2 mb-4 border-bottom border-secondary">
                <h1 class="h2 text-cyan">Submit New Innovation Idea 💡</h1>
                <p class="text-white-50">Share your project or startup concept with faculty mentors.</p>
            </div>

            <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-lg-8">
                    <div class="card bg-dark text-white p-4 shadow-sm mb-4">
                        <form action="submit_idea.php" method="POST" enctype="multipart/form-data">
                            
                            <div class="mb-3">
                                <label for="title" class="form-label text-white">Idea Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="title" name="title" placeholder="e.g., AI-based Smart Traffic Management System" required>
                            </div>

                            <div class="mb-3">
                                <label for="category" class="form-label text-white">Category <span class="text-danger">*</span></label>
                                <select class="form-select" id="category" name="category" required>
                                    <option value="" selected disabled>Select Category</option>
                                    <option value="Artificial Intelligence">Artificial Intelligence</option>
                                    <option value="Software & Web">Software & Web</option>
                                    <option value="IoT & Robotics">IoT & Robotics</option>
                                    <option value="Cyber Security">Cyber Security</option>
                                    <option value="Healthcare Tech">Healthcare Tech</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="idea_file" class="form-label text-white">Upload Idea Document (PDF / DOCX) <span class="text-danger">*</span></label>
                                <input type="file" class="form-control" id="idea_file" name="idea_file" accept=".pdf,.doc,.docx" required>
                                <small class="text-white-50">Upload your prepared idea proposal (Max 10MB).</small>
                            </div>

                            <div class="mb-4">
                                <label for="description" class="form-label text-white">Short Description / Note</label>
                                <textarea class="form-control" id="description" name="description" rows="4" placeholder="Briefly explain the problem, proposed solution, and technology stack..."></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary px-4 py-2 fw-bold">🚀 Submit Idea Proposal</button>
                        </form>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card bg-dark text-white p-4 shadow-sm border-start border-info border-4">
                        <h5 class="text-cyan fw-bold mb-3">📝 Submission Guidelines</h5>
                        <ul class="text-white-50 ps-3 small leading-relaxed">
                            <li class="mb-2"><strong>Title Clarity:</strong> Provide a concise and meaningful project title.</li>
                            <li class="mb-2"><strong>Document Upload:</strong> Make sure your PDF/Word file details the problem statement & solution.</li>
                            <li class="mb-2"><strong>Review Process:</strong> Status remains <code>Pending</code> until assigned faculty reviews your idea.</li>
                        </ul>
                    </div>
                </div>
            </div>

        </main>
    </div>
</div>

<?php $conn->close(); ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>