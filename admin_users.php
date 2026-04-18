<?php
require_once 'includes/config.php';
include 'includes/header.php';

$message = '';

// Handle Adming inserting data (Adding new Faculty/User)
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_user'])) {
    $fullName = trim($_POST['fullName']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $role = $_POST['role'];
    $password = $_POST['password'];

    // Basic Redundancy Check
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if($stmt->rowCount() > 0) {
        $message = "<div class='alert alert-error'><strong>Error:</strong> Email is already registered in the system!</div>";
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (fullName, email, phone, password, role) VALUES (?, ?, ?, ?, ?)");
        if($stmt->execute([$fullName, $email, $phone, $hashed, $role])) {
            $message = "<div class='alert alert-success'><strong>Success:</strong> Backend Data Stored Successfully! User display updated below.</div>";
        } else {
            $message = "<div class='alert alert-error'><strong>Failed:</strong> Server execution issue.</div>";
        }
    }
}

// Fetch all Backend stored User data
$usersData = $pdo->query("SELECT * FROM users ORDER BY id DESC")->fetchAll();
?>

<?= $message ?>

<div class="panel">
    <div class="panel-header">
        <h2 class="panel-title"><i class="fa-solid fa-user-shield"></i> Admin Panel: Enter & Store User Data</h2>
    </div>
    
    <div style="background: rgba(78,84,200,0.05); padding: 25px; border-radius: 12px; margin-bottom: 30px; border: 1px solid var(--border);">
        <form action="" method="POST">
            <div class="form-grid">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="fullName" class="form-control" required placeholder="Enter full name...">
                </div>
                
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" class="form-control" required placeholder="Enter login email...">
                </div>
                
                <div class="form-group">
                    <label>Mobile Contact</label>
                    <input type="text" name="phone" class="form-control" required placeholder="e.g. +1 555-0100">
                </div>
                
                <div class="form-group">
                    <label>System Role</label>
                    <select name="role" class="form-control" required>
                        <option value="faculty">Faculty Member</option>
                        <option value="admin">System Administrator</option>
                    </select>
                </div>
                
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label>Temporary Auth Password</label>
                    <input type="password" name="password" class="form-control" required placeholder="Assign a secure password...">
                </div>
            </div>
            
            <div style="display:flex; justify-content:space-between; align-items:center; margin-top:20px;">
                <button type="submit" name="add_user" class="btn" style="width: auto;"><i class="fa-solid fa-cloud-arrow-up"></i> Store Data to Backend</button>
                
                <!-- Instant Dedicated Logout as requested -->
                <a href="logout.php" class="btn btn-danger" style="width:auto; border-radius:8px; padding:12px 25px; text-decoration:none;"><i class="fa-solid fa-power-off"></i> Secure Logout</a>
            </div>
        </form>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <h2 class="panel-title"><i class="fa-solid fa-display"></i> Live Screen Display: Backend Stored Data</h2>
    </div>
    
    <div class="table-responsive">
        <table style="box-shadow: 0 4px 15px rgba(0,0,0,0.05); border-radius: 8px; overflow: hidden;">
            <thead>
                <tr style="background: var(--primary); color: white;">
                    <th style="color: white;">ID</th>
                    <th style="color: white;">Display Name</th>
                    <th style="color: white;">Contact / Email</th>
                    <th style="color: white;">Role Mapping</th>
                    <th style="color: white;">Date Stored</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($usersData as $u): ?>
                <tr style="transition: all 0.2s ease;">
                    <td><strong>#<?= $u['id'] ?></strong></td>
                    <td style="font-weight: 500; font-size: 1.05rem;"><?= htmlspecialchars($u['fullName']) ?></td>
                    <td>
                        <div><i class="fa-solid fa-envelope" style="color:var(--text-muted); font-size:0.8rem; margin-right:5px;"></i> <?= htmlspecialchars($u['email']) ?></div>
                        <div style="font-size:0.85rem; color:var(--text-muted); margin-top:3px;"><i class="fa-solid fa-phone" style="font-size:0.8rem; margin-right:5px;"></i> <?= htmlspecialchars($u['phone']) ?></div>
                    </td>
                    <td>
                        <?php if($u['role'] === 'admin'): ?>
                            <span class="badge" style="background:var(--secondary); color:white;">Administrator</span>
                        <?php else: ?>
                            <span class="badge" style="background:#e3f2fd; color:#1976d2;">Faculty User</span>
                        <?php endif; ?>
                    </td>
                    <td style="color:var(--text-muted); font-size:0.9rem;">
                        <?= date('M d, Y', strtotime($u['created_at'])) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($usersData)): ?>
                <tr>
                    <td colspan="5" style="text-align:center; padding: 20px;">No backend data found.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
