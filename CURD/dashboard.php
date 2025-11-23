<?php
declare(strict_types=1);

require __DIR__ . '/config.php';
require_login();

$conn = get_db_connection();
$user = current_user();
$roles = ['Manager', 'Developer', 'Designer', 'HR', 'Intern'];
$errors = [];
$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);
$editingEmployee = null;

// Handle delete request first (GET with confirmation client-side)
if (isset($_GET['delete'])) {
    $empId = (int) $_GET['delete'];
    if ($empId > 0) {
        $stmt = $conn->prepare('DELETE FROM employee WHERE emp_id = ?');
        $stmt->bind_param('i', $empId);
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $_SESSION['flash'] = 'Employee removed successfully.';
            $stmt->close();
            header('Location: dashboard.php');
            exit;
        } else {
            $errors[] = 'Unable to delete employee. Please try again.';
        }
        $stmt->close();
    }
}

// Handle add / update submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $empId = isset($_POST['emp_id']) ? (int) $_POST['emp_id'] : 0;
    $firstname = trim($_POST['firstname'] ?? '');
    $lastname = trim($_POST['lastname'] ?? '');
    $role = trim($_POST['role'] ?? '');

    if ($firstname === '' || strlen($firstname) < 2) {
        $errors[] = 'First name must be at least 2 characters.';
    }
    if ($lastname === '' || strlen($lastname) < 2) {
        $errors[] = 'Last name must be at least 2 characters.';
    }
    if (!in_array($role, $roles, true)) {
        $errors[] = 'Please select a valid role.';
    }

    if (empty($errors)) {
        if ($action === 'create') {
            $stmt = $conn->prepare('INSERT INTO employee (firstname, lastname, role) VALUES (?, ?, ?)');
            $stmt->bind_param('sss', $firstname, $lastname, $role);
            if ($stmt->execute()) {
                $_SESSION['flash'] = 'New employee saved successfully.';
                $stmt->close();
                header('Location: dashboard.php');
                exit;
            } else {
                $errors[] = 'Failed to save employee.';
            }
            $stmt->close();
        } elseif ($action === 'update' && $empId > 0) {
            $stmt = $conn->prepare('UPDATE employee SET firstname = ?, lastname = ?, role = ? WHERE emp_id = ?');
            $stmt->bind_param('sssi', $firstname, $lastname, $role, $empId);
            if ($stmt->execute()) {
                $_SESSION['flash'] = 'Employee updated successfully.';
                $stmt->close();
                header('Location: dashboard.php');
                exit;
            } else {
                $errors[] = 'Failed to update employee.';
            }
            $stmt->close();
        } else {
            $errors[] = 'Unknown action requested.';
        }
    }
}

// Load selected employee for editing
if (isset($_GET['edit'])) {
    $empId = (int) $_GET['edit'];
    if ($empId > 0) {
        $stmt = $conn->prepare('SELECT * FROM employee WHERE emp_id = ?');
        $stmt->bind_param('i', $empId);
        $stmt->execute();
        $result = $stmt->get_result();
        $editingEmployee = $result->fetch_assoc() ?: null;
        $stmt->close();
        if (!$editingEmployee) {
            $errors[] = 'Employee not found for editing.';
        }
    }
}

// Fetch latest employees for table
$employees = [];
$result = mysqli_query($conn, 'SELECT * FROM employee ORDER BY emp_id DESC');
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $employees[] = $row;
    }
    mysqli_free_result($result);
}

// Basic report data
$totalEmployees = count($employees);
$roleBreakdown = [];
$reportResult = mysqli_query($conn, 'SELECT role, COUNT(*) AS total FROM employee GROUP BY role ORDER BY total DESC');
if ($reportResult) {
    while ($row = mysqli_fetch_assoc($reportResult)) {
        $roleBreakdown[] = $row;
    }
    mysqli_free_result($reportResult);
}

$formAction = $editingEmployee ? 'update' : 'create';
$formHeading = $editingEmployee ? 'Update Employee' : 'Add Employee';
$submitLabel = $editingEmployee ? 'Save Changes' : 'Create Employee';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Portal | Dashboard</title>
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1e40af;
            --success: #22c55e;
            --danger: #ef4444;
            --border: #e5e7eb;
            --bg: #f8fafc;
            --card-bg: #ffffff;
            --text: #0f172a;
            --muted: #475569;
        }
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--bg);
            color: var(--text);
        }
        header {
            background: #0f172a;
            color: #fff;
            padding: 24px 40px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        header h1 {
            margin: 0;
            font-size: 26px;
        }
        header .user {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        header a {
            color: #fff;
            text-decoration: none;
            padding: 8px 16px;
            border: 1px solid rgba(255,255,255,0.4);
            border-radius: 999px;
            transition: background 0.2s;
        }
        header a:hover {
            background: rgba(255,255,255,0.15);
        }
        main {
            padding: 32px 48px 48px;
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 32px;
        }
        @media (max-width: 960px) {
            main {
                grid-template-columns: 1fr;
                padding: 24px;
            }
        }
        .print-btn {
            border: 1px solid rgba(255,255,255,0.4);
            background: transparent;
            color: #fff;
            padding: 8px 16px;
            border-radius: 999px;
            cursor: pointer;
            transition: background 0.2s;
        }
        .print-btn:hover {
            background: rgba(255,255,255,0.15);
        }
        @media print {
            header .user a,
            header .user button,
            .link-btn,
            .card button {
                display: none !important;
            }
            body {
                background: #fff;
            }
            main {
                padding: 0;
                grid-template-columns: 1fr;
            }
            .card {
                box-shadow: none;
                border: none;
            }
        }
        .card {
            background: var(--card-bg);
            border-radius: 18px;
            padding: 24px;
            box-shadow: 0 20px 50px rgba(15, 23, 42, 0.08);
        }
        .card h2 {
            margin-top: 0;
        }
        form .field {
            margin-bottom: 14px;
        }
        label {
            font-weight: 600;
            display: block;
            margin-bottom: 6px;
        }
        input[type="text"],
        select {
            width: 100%;
            padding: 11px 12px;
            border-radius: 10px;
            border: 1px solid var(--border);
            font-size: 15px;
        }
        input:focus,
        select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        }
        button {
            width: 100%;
            padding: 12px 14px;
            border-radius: 10px;
            border: none;
            background: var(--primary);
            color: #fff;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        button:hover {
            background: var(--primary-dark);
        }
        .alerts {
            margin-bottom: 18px;
        }
        .alert {
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 10px;
        }
        .alert.success {
            background: #dcfce7;
            color: #166534;
        }
        .alert.error {
            background: #fee2e2;
            color: #b91c1c;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }
        th, td {
            text-align: left;
            padding: 14px 16px;
            border-bottom: 1px solid var(--border);
        }
        th {
            background: #eff4ff;
            color: #1d4ed8;
            font-size: 14px;
            letter-spacing: 0.02em;
            text-transform: uppercase;
        }
        tbody tr:hover {
            background: #f8fafc;
        }
        .table-actions {
            display: flex;
            gap: 8px;
        }
        .badge {
            display: inline-flex;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 13px;
            background: #e0e7ff;
            color: #3730a3;
        }
        .chip {
            display: inline-flex;
            padding: 4px 10px;
            border-radius: 999px;
            background: rgba(37, 99, 235, 0.1);
            color: var(--primary-dark);
            font-weight: 600;
            font-size: 13px;
        }
        .link-btn {
            text-decoration: none;
            background: rgba(37, 99, 235, 0.12);
            color: var(--primary-dark);
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 600;
        }
        .link-btn.danger {
            background: rgba(239, 68, 68, 0.12);
            color: var(--danger);
        }
        .report-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 16px;
            margin-top: 24px;
        }
        .stat {
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 16px;
        }
        .stat p {
            margin: 0;
            color: var(--muted);
        }
        .stat strong {
            display: block;
            font-size: 28px;
            margin-top: 6px;
        }
    </style>
</head>
<body>
    <header>
        <div>
            <h1>Company Dashboard</h1>
            <p style="margin:6px 0 0;color:#94a3b8;">Track employees, roles and quick reports.</p>
        </div>
        <div class="user">
            <span class="chip">Signed in as <?php echo e($user['name']); ?></span>
            <button class="print-btn" type="button" onclick="window.print()">Print report</button>
            <a href="logout.php">Logout</a>
        </div>
    </header>

    <main>
        <section class="card">
            <h2><?php echo e($formHeading); ?></h2>

            <div class="alerts">
                <?php if ($flash): ?>
                    <div class="alert success"><?php echo e($flash); ?></div>
                <?php endif; ?>
                <?php foreach ($errors as $error): ?>
                    <div class="alert error"><?php echo e($error); ?></div>
                <?php endforeach; ?>
            </div>

            <form method="post" novalidate>
                <?php if ($editingEmployee): ?>
                    <input type="hidden" name="emp_id" value="<?php echo (int) $editingEmployee['emp_id']; ?>">
                <?php endif; ?>
                <input type="hidden" name="action" value="<?php echo e($formAction); ?>">
                <div class="field">
                    <label for="firstname">First name</label>
                    <input type="text" id="firstname" name="firstname" value="<?php echo e($editingEmployee['firstname'] ?? ($_POST['firstname'] ?? '')); ?>" required>
                </div>
                <div class="field">
                    <label for="lastname">Last name</label>
                    <input type="text" id="lastname" name="lastname" value="<?php echo e($editingEmployee['lastname'] ?? ($_POST['lastname'] ?? '')); ?>" required>
                </div>
                <div class="field">
                    <label for="role">Role</label>
                    <select id="role" name="role" required>
                        <option value="">-- Select role --</option>
                        <?php foreach ($roles as $option): ?>
                            <option value="<?php echo e($option); ?>" <?php echo (($editingEmployee['role'] ?? ($_POST['role'] ?? '')) === $option) ? 'selected' : ''; ?>>
                                <?php echo e($option); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit"><?php echo e($submitLabel); ?></button>
            </form>
        </section>

        <section class="card">
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <div>
                    <h2>Employees</h2>
                    <p style="margin:0;color:var(--muted);">You have <?php echo $totalEmployees; ?> active team members.</p>
                </div>
                <span class="badge">Updated <?php echo date('M d, Y'); ?></span>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>First name</th>
                        <th>Last name</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($employees)): ?>
                        <tr>
                            <td colspan="5" style="text-align:center;color:var(--muted);">No employees added yet.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($employees as $employee): ?>
                            <tr>
                                <td><?php echo (int) $employee['emp_id']; ?></td>
                                <td><?php echo e($employee['firstname']); ?></td>
                                <td><?php echo e($employee['lastname']); ?></td>
                                <td><?php echo e($employee['role']); ?></td>
                                <td>
                                    <div class="table-actions">
                                        <a class="link-btn" href="?edit=<?php echo (int) $employee['emp_id']; ?>">Edit</a>
                                        <a class="link-btn danger" href="?delete=<?php echo (int) $employee['emp_id']; ?>" onclick="return confirm('Delete this employee?');">Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <div class="report-grid">
                <div class="stat">
                    <p>Total employees</p>
                    <strong><?php echo $totalEmployees; ?></strong>
                </div>
                <?php foreach ($roleBreakdown as $item): ?>
                    <div class="stat">
                        <p><?php echo e($item['role']); ?></p>
                        <strong><?php echo (int) $item['total']; ?></strong>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>
</body>
</html>

