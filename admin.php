<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database Configuration
$host = "localhost";
$username = "root";
$password = "";
$database = "mrkit";

// Create Database Connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8mb4");

// Get all data from database
$departments = $conn->query("SELECT * FROM departments ORDER BY name");

$staff_data = $conn->query("
    SELECT s.*, d.name as department_name 
    FROM staff s 
    LEFT JOIN departments d ON s.department_id = d.id 
    ORDER BY s.id DESC
");

$student_data = $conn->query("
    SELECT st.*, d.name as department_name 
    FROM students st 
    LEFT JOIN departments d ON st.department_id = d.id 
    ORDER BY st.id DESC
");

$material_data = $conn->query("
    SELECT m.*, d.name as department_name 
    FROM materials m 
    LEFT JOIN departments d ON m.department_id = d.id 
    ORDER BY m.id DESC
");

// Get statistics
$total_staff = $conn->query("SELECT COUNT(*) as count FROM staff")->fetch_assoc()['count'];
$total_students = $conn->query("SELECT COUNT(*) as count FROM students")->fetch_assoc()['count'];
$total_materials = $conn->query("SELECT COUNT(*) as count FROM materials")->fetch_assoc()['count'];
$total_departments = $conn->query("SELECT COUNT(*) as count FROM departments")->fetch_assoc()['count'];

// Get current view
$view = isset($_GET['view']) ? $_GET['view'] : 'dashboard';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Viewer - College Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --success-color: #27ae60;
            --info-color: #17a2b8;
            --light-bg: #f8f9fa;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f9;
        }
        
        .navbar {
            background: var(--primary-color);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .navbar-brand {
            color: white !important;
            font-weight: 600;
        }
        
        .nav-link {
            color: rgba(255,255,255,0.8) !important;
            margin: 0 5px;
            border-radius: 5px;
            transition: all 0.3s;
        }
        
        .nav-link:hover, .nav-link.active {
            background: var(--secondary-color);
            color: white !important;
        }
        
        .nav-link i {
            margin-right: 5px;
        }
        
        .main-content {
            padding: 20px;
            margin-top: 20px;
        }
        
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            transition: transform 0.3s;
            border-left: 4px solid var(--secondary-color);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        
        .stat-card .icon {
            font-size: 2.5rem;
            color: var(--secondary-color);
            margin-bottom: 10px;
        }
        
        .stat-card .number {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary-color);
        }
        
        .stat-card .label {
            color: #6c757d;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .table-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .table-header {
            border-bottom: 2px solid #dee2e6;
            padding-bottom: 15px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .table-header h3 {
            margin: 0;
            color: var(--primary-color);
            font-size: 1.5rem;
        }
        
        .table-header .badge {
            font-size: 1rem;
            padding: 8px 15px;
        }
        
        .table thead th {
            background: var(--light-bg);
            border: none;
            font-weight: 600;
            color: var(--primary-color);
            padding: 15px;
        }
        
        .table tbody td {
            padding: 12px 15px;
            vertical-align: middle;
        }
        
        .table tbody tr:hover {
            background-color: #f8f9fa;
        }
        
        .badge-type {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: normal;
        }
        
        .badge-book { background: #27ae60; color: white; }
        .badge-labmanual { background: #9b59b6; color: white; }
        .badge-notes { background: #e67e22; color: white; }
        .badge-questionbank { background: #3498db; color: white; }
        
        .department-section {
            margin-bottom: 30px;
        }
        
        .department-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .department-title {
            color: var(--primary-color);
            border-bottom: 2px solid #dee2e6;
            padding-bottom: 10px;
            margin-bottom: 15px;
            font-weight: 600;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .info-item {
            text-align: center;
            padding: 15px;
            background: var(--light-bg);
            border-radius: 8px;
        }
        
        .info-item .number {
            font-size: 1.8rem;
            font-weight: bold;
            color: var(--secondary-color);
        }
        
        .info-item .label {
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .footer {
            background: white;
            padding: 20px;
            text-align: center;
            color: #6c757d;
            border-top: 1px solid #dee2e6;
            margin-top: 30px;
        }
        
        .search-box {
            margin-bottom: 20px;
        }
        
        .record-count {
            background: var(--light-bg);
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        
        .record-count i {
            color: var(--secondary-color);
            margin-right: 5px;
        }
        
        .text-muted-small {
            color: #6c757d;
            font-size: 0.85rem;
            font-style: italic;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="?view=dashboard">
                <i class="fas fa-database"></i> Database Viewer
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $view == 'dashboard' ? 'active' : ''; ?>" href="?view=dashboard">
                            <i class="fas fa-home"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $view == 'staff' ? 'active' : ''; ?>" href="?view=staff">
                            <i class="fas fa-user-tie"></i> Staff (<?php echo $total_staff; ?>)
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $view == 'students' ? 'active' : ''; ?>" href="?view=students">
                            <i class="fas fa-user-graduate"></i> Students (<?php echo $total_students; ?>)
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $view == 'materials' ? 'active' : ''; ?>" href="?view=materials">
                            <i class="fas fa-book"></i> Materials (<?php echo $total_materials; ?>)
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $view == 'departments' ? 'active' : ''; ?>" href="?view=departments">
                            <i class="fas fa-building"></i> Departments (<?php echo $total_departments; ?>)
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container-fluid main-content">
        <!-- Dashboard View -->
        <?php if ($view == 'dashboard'): ?>
            <h2 class="mb-4"><i class="fas fa-chart-pie"></i> Database Overview</h2>
            
            <!-- Statistics Cards -->
            <div class="row">
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="icon"><i class="fas fa-building"></i></div>
                        <div class="number"><?php echo $total_departments; ?></div>
                        <div class="label">Total Departments</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card" style="border-left-color: #27ae60;">
                        <div class="icon" style="color: #27ae60;"><i class="fas fa-user-tie"></i></div>
                        <div class="number"><?php echo $total_staff; ?></div>
                        <div class="label">Total Staff</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card" style="border-left-color: #e67e22;">
                        <div class="icon" style="color: #e67e22;"><i class="fas fa-user-graduate"></i></div>
                        <div class="number"><?php echo $total_students; ?></div>
                        <div class="label">Total Students</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card" style="border-left-color: #9b59b6;">
                        <div class="icon" style="color: #9b59b6;"><i class="fas fa-book"></i></div>
                        <div class="number"><?php echo $total_materials; ?></div>
                        <div class="label">Total Materials</div>
                    </div>
                </div>
            </div>

            <!-- Department-wise Summary -->
            <div class="department-section">
                <h3 class="mb-3"><i class="fas fa-chart-bar"></i> Department-wise Summary</h3>
                <div class="row">
                    <?php
                    $departments->data_seek(0);
                    while ($dept = $departments->fetch_assoc()):
                        $dept_id = $dept['id'];
                        $staff_count = $conn->query("SELECT COUNT(*) as count FROM staff WHERE department_id = $dept_id")->fetch_assoc()['count'];
                        $student_count = $conn->query("SELECT COUNT(*) as count FROM students WHERE department_id = $dept_id")->fetch_assoc()['count'];
                        $material_count = $conn->query("SELECT COUNT(*) as count FROM materials WHERE department_id = $dept_id")->fetch_assoc()['count'];
                    ?>
                    <div class="col-md-4">
                        <div class="department-card">
                            <h5 class="department-title">
                                <i class="fas fa-building"></i> <?php echo htmlspecialchars($dept['name']); ?>
                            </h5>
                            <div class="info-grid">
                                <div class="info-item">
                                    <div class="number"><?php echo $staff_count; ?></div>
                                    <div class="label">Staff</div>
                                </div>
                                <div class="info-item">
                                    <div class="number"><?php echo $student_count; ?></div>
                                    <div class="label">Students</div>
                                </div>
                                <div class="info-item">
                                    <div class="number"><?php echo $material_count; ?></div>
                                    <div class="label">Materials</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>

            <!-- Quick Previews -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="table-container">
                        <div class="table-header">
                            <h3><i class="fas fa-user-tie"></i> Recent Staff</h3>
                            <a href="?view=staff" class="btn btn-sm btn-outline-primary">View All</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Department</th>
                                        <th>Designation</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $recent_staff = $conn->query("
                                        SELECT s.*, d.name as dept_name 
                                        FROM staff s 
                                        LEFT JOIN departments d ON s.department_id = d.id 
                                        ORDER BY s.id DESC LIMIT 5
                                    ");
                                    while ($row = $recent_staff->fetch_assoc()):
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['dept_name'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($row['designation'] ?? 'N/A'); ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="table-container">
                        <div class="table-header">
                            <h3><i class="fas fa-user-graduate"></i> Recent Students</h3>
                            <a href="?view=students" class="btn btn-sm btn-outline-primary">View All</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Department</th>
                                        <th>Year</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $recent_students = $conn->query("
                                        SELECT s.*, d.name as dept_name 
                                        FROM students s 
                                        LEFT JOIN departments d ON s.department_id = d.id 
                                        ORDER BY s.id DESC LIMIT 5
                                    ");
                                    while ($row = $recent_students->fetch_assoc()):
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['dept_name'] ?? 'N/A'); ?></td>
                                        <td><?php echo $row['year']; ?> Year</td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Staff View -->
        <?php if ($view == 'staff'): ?>
            <div class="table-container">
                <div class="table-header">
                    <h3><i class="fas fa-user-tie"></i> Staff Records</h3>
                    <span class="badge bg-primary">Total: <?php echo $total_staff; ?></span>
                </div>
                <div class="record-count">
                    <i class="fas fa-database"></i> Showing all staff records from database
                </div>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Department</th>
                                <th>Designation</th>
                                <th>Joined Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($staff_data->num_rows > 0): ?>
                                <?php $staff_data->data_seek(0); while ($row = $staff_data->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $row['id']; ?></td>
                                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td><?php echo htmlspecialchars($row['phone'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($row['department_name'] ?? 'Not Assigned'); ?></td>
                                    <td><?php echo htmlspecialchars($row['designation'] ?? 'N/A'); ?></td>
                                    <td>
                                        <?php 
                                        if (isset($row['created_at']) && !empty($row['created_at'])) {
                                            echo date('d M Y', strtotime($row['created_at']));
                                        } else {
                                            echo '<span class="text-muted-small">Not recorded</span>';
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">No staff records found in database</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <!-- Students View -->
        <?php if ($view == 'students'): ?>
            <div class="table-container">
                <div class="table-header">
                    <h3><i class="fas fa-user-graduate"></i> Student Records</h3>
                    <span class="badge bg-primary">Total: <?php echo $total_students; ?></span>
                </div>
                <div class="record-count">
                    <i class="fas fa-database"></i> Showing all student records from database
                </div>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Department</th>
                                <th>Year</th>
                                <th>Enrolled Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($student_data->num_rows > 0): ?>
                                <?php $student_data->data_seek(0); while ($row = $student_data->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $row['id']; ?></td>
                                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td><?php echo htmlspecialchars($row['phone'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($row['department_name'] ?? 'Not Assigned'); ?></td>
                                    <td><?php echo $row['year']; ?> Year</td>
                                    <td>
                                        <?php 
                                        if (isset($row['created_at']) && !empty($row['created_at'])) {
                                            echo date('d M Y', strtotime($row['created_at']));
                                        } else {
                                            echo '<span class="text-muted-small">Not recorded</span>';
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">No student records found in database</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <!-- Materials View -->
        <?php if ($view == 'materials'): ?>
            <div class="table-container">
                <div class="table-header">
                    <h3><i class="fas fa-book"></i> Material Records</h3>
                    <span class="badge bg-primary">Total: <?php echo $total_materials; ?></span>
                </div>
                <div class="record-count">
                    <i class="fas fa-database"></i> Showing all material records from database
                </div>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Type</th>
                                <th>Department</th>
                                <th>Description</th>
                                <th>Added Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($material_data->num_rows > 0): ?>
                                <?php $material_data->data_seek(0); while ($row = $material_data->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $row['id']; ?></td>
                                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                                    <td>
                                        <?php 
                                        $badge_class = '';
                                        $type_label = '';
                                        switch($row['type']) {
                                            case 'book': 
                                                $badge_class = 'badge-book'; 
                                                $type_label = 'Book';
                                                break;
                                            case 'labmanual': 
                                                $badge_class = 'badge-labmanual'; 
                                                $type_label = 'Lab Manual';
                                                break;
                                            case 'notes': 
                                                $badge_class = 'badge-notes'; 
                                                $type_label = 'Notes';
                                                break;
                                            case 'questionbank': 
                                                $badge_class = 'badge-questionbank'; 
                                                $type_label = 'Question Bank';
                                                break;
                                        }
                                        ?>
                                        <span class="badge badge-type <?php echo $badge_class; ?>">
                                            <?php echo $type_label; ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['department_name'] ?? 'Not Assigned'); ?></td>
                                    <td><?php echo htmlspecialchars(substr($row['description'] ?? '', 0, 100)) . (strlen($row['description'] ?? '') > 100 ? '...' : ''); ?></td>
                                    <td>
                                        <?php 
                                        if (isset($row['created_at']) && !empty($row['created_at'])) {
                                            echo date('d M Y', strtotime($row['created_at']));
                                        } else {
                                            echo '<span class="text-muted-small">Not recorded</span>';
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">No material records found in database</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <!-- Departments View -->
        <?php if ($view == 'departments'): ?>
            <div class="table-container">
                <div class="table-header">
                    <h3><i class="fas fa-building"></i> Department Records</h3>
                    <span class="badge bg-primary">Total: <?php echo $total_departments; ?></span>
                </div>
                <div class="record-count">
                    <i class="fas fa-database"></i> Showing all department records from database
                </div>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Department Name</th>
                                <th>Staff Count</th>
                                <th>Student Count</th>
                                <th>Material Count</th>
                                <th>Created Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $departments->data_seek(0);
                            while ($dept = $departments->fetch_assoc()): 
                                $dept_id = $dept['id'];
                                $staff_count = $conn->query("SELECT COUNT(*) as count FROM staff WHERE department_id = $dept_id")->fetch_assoc()['count'];
                                $student_count = $conn->query("SELECT COUNT(*) as count FROM students WHERE department_id = $dept_id")->fetch_assoc()['count'];
                                $material_count = $conn->query("SELECT COUNT(*) as count FROM materials WHERE department_id = $dept_id")->fetch_assoc()['count'];
                            ?>
                            <tr>
                                <td><?php echo $dept['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($dept['name']); ?></strong></td>
                                <td><span class="badge bg-info"><?php echo $staff_count; ?></span></td>
                                <td><span class="badge bg-success"><?php echo $student_count; ?></span></td>
                                <td><span class="badge bg-warning"><?php echo $material_count; ?></span></td>
                                <td>
                                    <?php 
                                    // Check if created_at exists and is not null
                                    if (isset($dept['created_at']) && !empty($dept['created_at'])) {
                                        echo date('d M Y', strtotime($dept['created_at']));
                                    } else {
                                        echo '<span class="text-muted-small">Not recorded</span>';
                                    }
                                    ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <div class="footer">
        <div class="container">
            <p class="mb-0">
                <i class="fas fa-database"></i> Database Viewer - College Management System<br>
                <small>Showing live data from 'mrkit' database</small>
            </p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>