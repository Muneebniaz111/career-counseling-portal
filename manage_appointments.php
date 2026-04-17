<?php
require_once __DIR__ . '/bootstrap.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: Log-in (Admin).php");
    exit();
}

$mysqli = new mysqli("localhost", "root", "", "career_counseling");

$result = $mysqli->query("SELECT a.*, u.name as student_name, u.email as student_email, c.name as counselor_name 
                          FROM appointments a 
                          LEFT JOIN users u ON a.student_id = u.id 
                          LEFT JOIN counselors c ON a.counselor_id = c.id 
                          ORDER BY a.appointment_date DESC");
$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Appointments - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(to bottom, #000, #4b0082);
            color: white;
            font-family: 'Arial', sans-serif;
        }

        .navbar {
            background-color: black;
            padding: 20px 40px;
            margin-bottom: 40px;
        }

        .container-fluid {
            margin-top: 40px;
        }

        .card {
            background-color: rgba(0, 0, 0, 0.8);
            border: 1px solid #ff6b6b;
        }

        .card-header {
            background-color: #ff6b6b;
            color: white;
        }

        .table {
            background-color: rgba(0, 0, 0, 0.8);
        }

        .table thead th {
            border-color: #666;
            color: #ff6b6b;
        }

        .table td {
            border-color: #666;
            color: white;
        }

        .badge-pending {
            background-color: #ffc107;
            color: black;
        }

        .badge-confirmed {
            background-color: #28a745;
        }

        .badge-completed {
            background-color: #17a2b8;
        }

        .back-link {
            color: #ff6b6b;
            text-decoration: none;
            font-size: 1rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: color 0.3s ease;
            margin-bottom: 20px;
        }

        .back-link:hover {
            color: #ff5252;
        }

        .back-link i {
            font-size: 1.1rem;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="Admin_Dashboard.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
    </nav>

    <div class="container-fluid" style="max-width: 1200px;">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0">Manage Appointments</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Student</th>
                                <th>Counselor</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Status</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($appt = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $appt['id']; ?></td>
                                    <td><?php echo htmlspecialchars($appt['student_name'] ?? 'Unknown'); ?><br>
                                        <small><?php echo htmlspecialchars($appt['student_email'] ?? ''); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($appt['counselor_name'] ?? 'Not Assigned'); ?></td>
                                    <td><?php echo date('Y-m-d', strtotime($appt['appointment_date'])); ?></td>
                                    <td><?php echo htmlspecialchars($appt['appointment_time']); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo strtolower($appt['status']); ?>">
                                            <?php echo htmlspecialchars($appt['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars(substr($appt['notes'] ?? '', 0, 30)) . (strlen($appt['notes'] ?? '') > 30 ? '...' : ''); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js"></script>
</body>
</html>

