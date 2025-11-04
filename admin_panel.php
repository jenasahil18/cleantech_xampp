<?php
require_once 'db_config.php';

// Simple authentication (In production, use proper session management)
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    // Simple login form
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        
        // Simple check (In production, verify against database with hashed password)
        if ($username === 'admin' && $password === 'admin123') {
            $_SESSION['admin_logged_in'] = true;
            header('Location: admin_panel.php');
            exit;
        } else {
            $login_error = "Invalid credentials";
        }
    }
    
    // Show login form
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Login - CleanTech</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body { 
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: 100vh;
            }
            .login-container {
                background: white;
                padding: 40px;
                border-radius: 15px;
                box-shadow: 0 10px 40px rgba(0,0,0,0.2);
                width: 400px;
            }
            h2 { color: #1a0b5e; margin-bottom: 30px; text-align: center; }
            .form-group { margin-bottom: 20px; }
            input {
                width: 100%;
                padding: 12px;
                border: 1px solid #ddd;
                border-radius: 8px;
                font-size: 14px;
            }
            button {
                width: 100%;
                padding: 12px;
                background: #1a0b5e;
                color: white;
                border: none;
                border-radius: 8px;
                font-size: 16px;
                cursor: pointer;
                transition: background 0.3s;
            }
            button:hover { background: #2a1b6e; }
            .error { color: #e74c3c; margin-bottom: 15px; text-align: center; }
        </style>
    </head>
    <body>
        <div class="login-container">
            <h2>Admin Login</h2>
            <?php if (isset($login_error)): ?>
                <p class="error"><?php echo $login_error; ?></p>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <input type="text" name="username" placeholder="Username" required>
                </div>
                <div class="form-group">
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                <button type="submit" name="login">Login</button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Logout functionality
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin_panel.php');
    exit;
}

// Get database connection
$pdo = getDBConnection();

// Handle status update
if (isset($_POST['update_status'])) {
    $id = $_POST['request_id'];
    $status = $_POST['status'];
    
    $stmt = $pdo->prepare("UPDATE contact_requests SET status = :status WHERE id = :id");
    $stmt->execute(['status' => $status, 'id' => $id]);
    
    header('Location: admin_panel.php?success=Status updated');
    exit;
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM contact_requests WHERE id = :id");
    $stmt->execute(['id' => $id]);
    
    header('Location: admin_panel.php?success=Request deleted');
    exit;
}

// Get filter parameters
$status_filter = $_GET['status'] ?? 'all';
$search = $_GET['search'] ?? '';

// Build query
$sql = "SELECT * FROM contact_requests WHERE 1=1";
$params = [];

if ($status_filter !== 'all') {
    $sql .= " AND status = :status";
    $params['status'] = $status_filter;
}

if (!empty($search)) {
    $sql .= " AND (name LIKE :search OR email LIKE :search OR contact_no LIKE :search)";
    $params['search'] = "%$search%";
}

$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$requests = $stmt->fetchAll();

// Get statistics
$stats_sql = "SELECT * FROM contact_statistics";
$stats = $pdo->query($stats_sql)->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - CleanTech</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
        }
        .header {
            background: #1a0b5e;
            color: white;
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 { font-size: 24px; }
        .logout-btn {
            background: white;
            color: #1a0b5e;
            padding: 8px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
        }
        .container { max-width: 1400px; margin: 0 auto; padding: 30px 40px; }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .stat-card h3 { font-size: 32px; color: #1a0b5e; margin-bottom: 5px; }
        .stat-card p { color: #666; font-size: 14px; }
        .filters {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
            align-items: center;
        }
        .filters select, .filters input {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        .filters button {
            padding: 10px 20px;
            background: #1a0b5e;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .success-msg {
            background: #4caf50;
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #1a0b5e; color: white; font-weight: 600; }
        tr:hover { background: #f9fafb; }
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-new { background: #e3f2fd; color: #1976d2; }
        .status-in_progress { background: #fff3e0; color: #f57c00; }
        .status-completed { background: #e8f5e9; color: #388e3c; }
        .status-cancelled { background: #ffebee; color: #d32f2f; }
        .actions {
            display: flex;
            gap: 10px;
        }
        .btn {
            padding: 6px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
            text-decoration: none;
            display: inline-block;
        }
        .btn-view { background: #2196f3; color: white; }
        .btn-delete { background: #f44336; color: white; }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
        }
        .modal-content {
            background: white;
            width: 600px;
            margin: 50px auto;
            padding: 30px;
            border-radius: 15px;
        }
        .modal-header { display: flex; justify-content: space-between; margin-bottom: 20px; }
        .close { font-size: 28px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="header">
        <h1>🧹 CleanTech - Admin Panel</h1>
        <a href="?logout" class="logout-btn">Logout</a>
    </div>

    <div class="container">
        <?php if (isset($_GET['success'])): ?>
            <div class="success-msg"><?php echo htmlspecialchars($_GET['success']); ?></div>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="stat-card">
                <h3><?php echo $stats['total_requests']; ?></h3>
                <p>Total Requests</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $stats['new_requests']; ?></h3>
                <p>New Requests</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $stats['today_requests']; ?></h3>
                <p>Today's Requests</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $stats['this_month_requests']; ?></h3>
                <p>This Month</p>
            </div>
        </div>

        <form class="filters" method="GET">
            <select name="status">
                <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Status</option>
                <option value="new" <?php echo $status_filter === 'new' ? 'selected' : ''; ?>>New</option>
                <option value="in_progress" <?php echo $status_filter === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
            </select>
            <input type="text" name="search" placeholder="Search by name, email, phone..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit">Filter</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Service</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($requests as $request): ?>
                <tr>
                    <td><?php echo $request['id']; ?></td>
                    <td><?php echo htmlspecialchars($request['name']); ?></td>
                    <td><?php echo htmlspecialchars($request['email']); ?></td>
                    <td><?php echo htmlspecialchars($request['contact_no']); ?></td>
                    <td><?php echo htmlspecialchars($request['service']); ?></td>
                    <td>
                        <span class="status-badge status-<?php echo $request['status']; ?>">
                            <?php echo ucfirst(str_replace('_', ' ', $request['status'])); ?>
                        </span>
                    </td>
                    <td><?php echo date('d M Y', strtotime($request['created_at'])); ?></td>
                    <td class="actions">
                        <button class="btn btn-view" onclick="viewRequest(<?php echo htmlspecialchars(json_encode($request)); ?>)">View</button>
                        <a href="?delete=<?php echo $request['id']; ?>" class="btn btn-delete" onclick="return confirm('Are you sure?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($requests)): ?>
                <tr>
                    <td colspan="8" style="text-align: center; padding: 40px;">No requests found</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal -->
    <div id="requestModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Request Details</h2>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <div id="modalBody"></div>
        </div>
    </div>

    <script>
        function viewRequest(request) {
            const modal = document.getElementById('requestModal');
            const modalBody = document.getElementById('modalBody');
            
            modalBody.innerHTML = `
                <p><strong>Name:</strong> ${request.name}</p>
                <p><strong>Email:</strong> ${request.email}</p>
                <p><strong>Phone:</strong> ${request.contact_no}</p>
                <p><strong>Service:</strong> ${request.service}</p>
                <p><strong>Message:</strong></p>
                <p>${request.message}</p>
                <p><strong>Date:</strong> ${new Date(request.created_at).toLocaleString()}</p>
                <form method="POST" style="margin-top: 20px;">
                    <input type="hidden" name="request_id" value="${request.id}">
                    <label><strong>Update Status:</strong></label>
                    <select name="status" style="width: 100%; padding: 10px; margin: 10px 0;">
                        <option value="new" ${request.status === 'new' ? 'selected' : ''}>New</option>
                        <option value="in_progress" ${request.status === 'in_progress' ? 'selected' : ''}>In Progress</option>
                        <option value="completed" ${request.status === 'completed' ? 'selected' : ''}>Completed</option>
                        <option value="cancelled" ${request.status === 'cancelled' ? 'selected' : ''}>Cancelled</option>
                    </select>
                    <button type="submit" name="update_status" class="btn" style="background: #1a0b5e; color: white; width: 100%; padding: 12px;">Update Status</button>
                </form>
            `;
            
            modal.style.display = 'block';
        }

        function closeModal() {
            document.getElementById('requestModal').style.display = 'none';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('requestModal');
            if (event.target === modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>