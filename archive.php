<?php
require_once 'includes/config.php';
include 'includes/header.php';

$message = '';

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_paper'])) {
    $delete_id = (int)$_POST['paper_id'];
    try {
        $stmtDel = $pdo->prepare("DELETE FROM generated_papers WHERE id = ?");
        if($stmtDel->execute([$delete_id])) {
            $message = "<div class='alert alert-success'>Successfully deleted Paper #".str_pad($delete_id, 5, '0', STR_PAD_LEFT)." cleanly from the archive.</div>";
        }
    } catch(PDOException $e) {
        $message = "<div class='alert alert-error'>Failed to delete paper. Security bounds hit.</div>";
    }
}

$stmt = $pdo->query("SELECT p.*, s.name as subject_name 
                     FROM generated_papers p 
                     JOIN subjects s ON p.subject_id = s.id 
                     ORDER BY p.id DESC");
$papers = $stmt->fetchAll();
?>

<?= $message ?>

<div class="panel">
    <div class="panel-header">
        <h2 class="panel-title"><i class="fa-solid fa-folder-open"></i> Generated Papers Archive</h2>
    </div>
    
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Paper ID</th>
                    <th>Assessment Title</th>
                    <th>Subject</th>
                    <th>Date Generated</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($papers as $p): ?>
                <tr>
                    <td style="font-weight:bold;">#<?= str_pad($p['id'], 5, '0', STR_PAD_LEFT) ?></td>
                    <td style="color:var(--primary); font-weight:600;"><?= htmlspecialchars($p['paper_name']) ?></td>
                    <td><?= htmlspecialchars($p['subject_name']) ?></td>
                    <td style="color:var(--text-muted); font-size:0.9rem;"><?= date('M d, Y h:i A', strtotime($p['created_at'])) ?></td>
                    <td style="display: flex; gap: 8px;">
                        <a href="preview.php?id=<?= $p['id'] ?>" class="btn btn-outline btn-sm" style="width:auto;"><i class="fa-solid fa-eye"></i> View & Print</a>
                        <form action="" method="POST" onsubmit="return confirm('WARNING: Are you absolutely sure you want to permanently delete this generated paper? This action cannot be natively undone.');" style="margin:0;">
                            <input type="hidden" name="paper_id" value="<?= $p['id'] ?>">
                            <button type="submit" name="delete_paper" class="btn btn-sm" style="background:var(--accent); width:auto; border:none; color:white;"><i class="fa-solid fa-trash-can"></i> Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($papers)): ?>
                <tr>
                    <td colspan="5" style="text-align:center; padding: 20px;">No papers generated yet.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
