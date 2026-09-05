<?php
// Include session check and database configuration
include "../includes/session.php";
include "../config/database.php";

// Retrieve currently logged-in student's ID
$student_id = $_SESSION['user_id'];

/* 
   ========================================================================
   SQL QUERY TO FETCH MENTORS (FACULTY WHO APPROVED STUDENT IDEAS)
   ========================================================================
*/
$sql = "SELECT DISTINCT 
            u.user_id AS faculty_id,
            u.name AS faculty_name,
            u.email AS faculty_email,
            u.department AS faculty_dept,
            i.idea_id,
            i.title AS project_title,
            r.reviewed_at
        FROM ideas i
        JOIN reviews r ON i.idea_id = r.idea_id
        JOIN users u ON r.faculty_id = u.user_id
        WHERE i.student_id = ? AND i.status = 'approved'
        ORDER BY r.reviewed_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Mentors - EWU Innovation Hub</title>
    
    <!-- Favicon & Stylesheets -->
    <link rel="icon" type="image/png" href="../assets/images/ewu_logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <style>
        body { background-color: #0f172a; color: #f8fafc; min-height: 100vh; }
        .main-content { margin-left: 250px; padding: 30px; }
        .mentor-card {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            transition: transform 0.2s ease, border-color 0.2s ease;
        }
        .mentor-card:hover {
            transform: translateY(-3px);
            border-color: #06b6d4;
        }
        .avatar-circle {
            width: 45px;
            height: 45px;
            background-color: #0284c7;
            color: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.2rem;
        }
        .text-cyan { color: #06b6d4 !important; }
        @media (max-width: 768px) { .main-content { margin-left: 0; padding: 15px; } }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- Student Sidebar -->
        <?php include "../includes/student_sidebar.php"; ?>

        <!-- Main Content Area -->
        <main class="col-md-9 ms-sm-auto col-lg-10 main-content">
            
            <div class="pb-3 mb-4 border-bottom border-secondary">
                <h1 class="h2 text-cyan mb-1">My Mentors 👨‍🏫</h1>
                <p class="text-white-50 mb-0">Faculty members guiding your innovation projects.</p>
            </div>

            <div class="row g-4">
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($mentor = $result->fetch_assoc()): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="mentor-card p-4 h-100 d-flex flex-column justify-content-between shadow-sm">
                                <div>
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="avatar-circle me-3">
                                            <?php echo strtoupper(substr($mentor['faculty_name'], 0, 1)); ?>
                                        </div>
                                        <div>
                                            <h5 class="mb-0 text-white"><?php echo htmlspecialchars($mentor['faculty_name']); ?></h5>
                                            <small class="text-cyan fw-bold"><?php echo htmlspecialchars($mentor['faculty_dept']); ?></small>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <small class="text-white-50 d-block">Assigned Project:</small>
                                        <span class="fw-semibold text-info"><?php echo htmlspecialchars($mentor['project_title']); ?></span>
                                    </div>

                                    <div class="mb-3">
                                        <small class="text-white-50 d-block">Contact Email:</small>
                                        <a href="mailto:<?php echo htmlspecialchars($mentor['faculty_email']); ?>" class="text-white text-decoration-none small">
                                            ✉️ <?php echo htmlspecialchars($mentor['faculty_email']); ?>
                                        </a>
                                    </div>
                                </div>

                                <div class="pt-3 border-top border-secondary d-flex justify-content-between align-items-center">
                                    <small class="text-white-50">
                                        <?php echo date('M d, Y', strtotime($mentor['reviewed_at'])); ?>
                                    </small>
                                    <a href="idea_details.php?id=<?php echo $mentor['idea_id']; ?>" class="btn btn-sm btn-outline-info">
                                        View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-5">
                        <div class="mentor-card p-5">
                            <h4 class="text-white-50 mb-2">No Mentors Assigned Yet! 🤝</h4>
                            <p class="text-white-50 mb-0">Once a faculty member approves your submitted idea, they will automatically appear here as your mentor.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

        </main>
    </div>
</div>

<?php
$stmt->close();
$conn->close();
?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>