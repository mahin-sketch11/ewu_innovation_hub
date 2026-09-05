<?php
include "../includes/session.php";
include "../config/database.php";

$faculty_id = $_SESSION['user_id'];

/*
   SQL Query: Fetch all students whose ideas have been APPROVED by this logged-in faculty.
*/
$sql = "SELECT DISTINCT 
            u.user_id AS student_id,
            u.name AS student_name,
            u.email AS student_email,
            u.department AS student_dept,
            i.idea_id,
            i.title AS project_title,
            i.category,
            i.submitted_at
        FROM ideas i
        JOIN users u ON i.student_id = u.user_id
        JOIN reviews r ON i.idea_id = r.idea_id
        WHERE r.faculty_id = ? AND i.status = 'approved'
        ORDER BY i.submitted_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $faculty_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Mentees - EWU Innovation Hub</title>
    
    <link rel="icon" type="image/png" href="../assets/images/ewu_logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <style>
        body { background-color: #0f172a; color: #f8fafc; min-height: 100vh; overflow-x: hidden; }
        .dashboard-wrapper { display: flex; min-height: 100vh; }
        .main-content { 
            flex: 1; 
            padding: 30px; 
            width: 100%;
            margin-left: 0 !important; /* অতিরিক্ত গ্যাপ বন্ধ করার জন্য */
        }
        .card.bg-dark {
            background: rgba(30, 41, 59, 0.7) !important;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
            border-radius: 12px;
        }
        .text-cyan { color: #06b6d4 !important; }
        .table-dark { background-color: transparent !important; }
        .table-dark th { background-color: rgba(15, 23, 42, 0.6) !important; color: #06b6d4; }
        .table-dark td { background-color: transparent !important; color: #cbd5e1; vertical-align: middle; }
    </style>
</head>
<body>

<div class="dashboard-wrapper">
    <?php include "../includes/faculty_sidebar.php"; ?>

    <main class="main-content">
        <div class="d-flex justify-content-between align-items-center pb-3 mb-4 border-bottom border-secondary">
            <h1 class="h2 text-cyan mb-0">My Student Mentees 🎓</h1>
            <a href="dashboard.php" class="btn btn-outline-secondary btn-sm">← Back to Dashboard</a>
        </div>

        <div class="card bg-dark text-white p-4 shadow-sm">
            <?php if ($result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-dark table-hover mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>STUDENT NAME</th>
                                <th>DEPT</th>
                                <th>APPROVED PROJECT</th>
                                <th>CATEGORY</th>
                                <th class="text-end">ACTION</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $count = 1;
                            while ($row = $result->fetch_assoc()): 
                            ?>
                                <tr>
                                    <td><?php echo $count++; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($row['student_name']); ?></strong><br>
                                        <small class="text-white-50"><?php echo htmlspecialchars($row['student_email']); ?></small>
                                    </td>
                                    <td><span class="badge bg-info text-dark"><?php echo htmlspecialchars($row['student_dept']); ?></span></td>
                                    <td><?php echo htmlspecialchars($row['project_title']); ?></td>
                                    <td><span class="badge bg-secondary"><?php echo htmlspecialchars($row['category']); ?></span></td>
                                    <td class="text-end">
                                        <a href="idea_details.php?id=<?php echo $row['idea_id']; ?>" class="btn btn-sm btn-outline-info">
                                            🔍 View Details
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <h4 class="text-white-50">No Mentees Assigned Yet! 🤝</h4>
                    <p class="text-white-50 mb-0">Approve student ideas in the Pending Ideas portal to assign them under your mentorship.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php 
$stmt->close();
$conn->close(); 
?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>