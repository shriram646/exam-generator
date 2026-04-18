<?php
require_once 'includes/config.php';
include 'includes/header.php';

$message = '';
$user_id = $_SESSION['user_id'];

// Run DB update gracefully to ensure column exists
try {
    $pdo->exec("ALTER TABLE users ADD COLUMN profile_image VARCHAR(255) DEFAULT NULL");
} catch(PDOException $e) {}

// Handle Profile Updates
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $fullName = trim($_POST['fullName']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    
    try {
        // Redundancy check for email logic (don't overwrite someone else's email)
        $stmtCheck = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmtCheck->execute([$email, $user_id]);
        
        if($stmtCheck->rowCount() > 0) {
            $message = "<div class='alert alert-error'><strong>Update Failed:</strong> Email is already mapped to another user!</div>";
        } else {
            // Handle Profile Image Upload safely
            $image_query_append = "";
            $params = [$fullName, $email, $phone];
            
            if(isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
                $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                $ext = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
                if(in_array($ext, $allowed)) {
                    if(!is_dir('uploads')) mkdir('uploads', 0777, true);
                    $newFilename = 'uploads/user_' . $user_id . '_' . time() . '.' . $ext;
                    if(move_uploaded_file($_FILES['profile_image']['tmp_name'], $newFilename)) {
                        $image_query_append = ", profile_image = ?";
                        $params[] = $newFilename;
                    }
                } else {
                    $message .= "<div class='alert alert-error'>Invalid Image Type.</div>";
                }
            }
            
            $params[] = $user_id; // For the WHERE clause
            
            $stmt = $pdo->prepare("UPDATE users SET fullName = ?, email = ?, phone = ? $image_query_append WHERE id = ?");
            if($stmt->execute($params)) {
                $_SESSION['user_name'] = $fullName; // Update session name globally
                $message .= "<div class='alert alert-success'><strong>Success:</strong> Profile securely updated!</div>";
            }
        }
    } catch(PDOException $e) {
        $message = "<div class='alert alert-error'>Error updating profile.</div>";
    }
}

// Fetch user's current data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Profile Analytics / Stats
$myPapersCountStr = "N/A (System Engine level mapping)";
?>

<?= $message ?>

<div class="panel" style="background: transparent; box-shadow: none; padding: 0;">
    <div style="display: flex; gap: 30px; flex-wrap: wrap;">
        
        <!-- Left Side: Visual Profile Card -->
        <div style="flex: 1; min-width: 300px;">
            <div style="background: var(--white); border-radius: var(--radius); box-shadow: var(--shadow); overflow: hidden; position: relative;">
                
                <!-- Cool Banner Background -->
                <div style="height: 120px; background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);"></div>
                
                <!-- Floating Avatar -->
                <div style="text-align: center; margin-top: -50px; position: relative; z-index: 5;">
                    <?php if(!empty($user['profile_image']) && file_exists($user['profile_image'])): ?>
                        <div style="width: 100px; height: 100px; border-radius: 50%; background: var(--white); border: 4px solid var(--white); box-shadow: 0 4px 15px rgba(0,0,0,0.1); display: inline-block; overflow: hidden;">
                            <img src="<?= htmlspecialchars($user['profile_image']) ?>" style="width: 100%; height: 100%; object-fit: cover;" alt="Profile Picture">
                        </div>
                    <?php else: ?>
                        <div style="width: 100px; height: 100px; border-radius: 50%; background: var(--white); border: 4px solid var(--white); box-shadow: 0 4px 15px rgba(0,0,0,0.1); display: inline-flex; align-items: center; justify-content: center; font-size: 3rem; font-weight: 700; color: var(--primary);">
                            <?= strtoupper(substr($user['fullName'], 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Details -->
                <div style="padding: 20px 30px 40px; text-align: center;">
                    <h2 style="color: var(--secondary); margin-bottom: 5px; font-weight: 700; font-size: 1.5rem;"><?= htmlspecialchars($user['fullName']) ?></h2>
                    
                    <?php if($user['role'] === 'admin'): ?>
                        <span class="badge" style="background:var(--secondary); color:white; font-size: 0.85rem; display:inline-block; margin-bottom: 20px;">System Administrator</span>
                    <?php else: ?>
                        <span class="badge" style="background:#e3f2fd; color:#1976d2; font-size: 0.85rem; display:inline-block; margin-bottom: 20px;">Faculty Level Standard</span>
                    <?php endif; ?>
                    
                    <div style="display:flex; flex-direction:column; gap:12px; align-items:center; color: var(--text-muted); font-size: 0.95rem;">
                        <div>
                            <i class="fa-solid fa-envelope" style="margin-right: 8px; color: var(--primary);"></i>
                            <?= htmlspecialchars($user['email']) ?>
                        </div>
                        <div>
                            <i class="fa-solid fa-phone" style="margin-right: 8px; color: var(--success);"></i>
                            <?= htmlspecialchars($user['phone']) ?>
                        </div>
                        <div>
                            <i class="fa-solid fa-calendar-check" style="margin-right: 8px; color: var(--accent);"></i>
                            Registered since <?= date('M Y', strtotime($user['created_at'])) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side: Edit / Security Panel -->
        <div style="flex: 2; min-width: 300px;">
            <div class="panel" style="margin-bottom: 0;">
                <div class="panel-header">
                    <h2 class="panel-title"><i class="fa-solid fa-user-pen"></i> Update Secure Information</h2>
                </div>
                
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="form-grid">
                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label>Profile Picture</label>
                            <input type="file" name="profile_image" class="form-control" accept="image/*">
                        </div>

                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label>Display Name</label>
                            <input type="text" name="fullName" class="form-control" value="<?= htmlspecialchars($user['fullName']) ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Security Email ID</label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Mobile Protocol</label>
                            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone']) ?>" required>
                        </div>
                    </div>

                    <div style="margin-top: 15px; border-top: 1px solid var(--border); padding-top: 20px; display:flex; justify-content:space-between; align-items:center;">
                        <button type="submit" name="update_profile" class="btn" style="width: auto;"><i class="fa-solid fa-floppy-disk"></i> Save Digital Identity</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<?php include 'includes/footer.php'; ?>
