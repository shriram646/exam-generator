<?php
require_once 'includes/config.php';
include 'includes/header.php';

$message = '';

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_subject'])) {
    $name = trim($_POST['subject_name']);
    $syllabus = trim($_POST['syllabus']);
    $units = isset($_POST['units']) ? $_POST['units'] : [];
    
    try {
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("INSERT INTO subjects (name, syllabus) VALUES (?, ?)");
        $stmt->execute([$name, $syllabus]);
        $subject_id = $pdo->lastInsertId();
        
        if(!empty($units)) {
            $stmt_unit = $pdo->prepare("INSERT INTO units (subject_id, unit_name) VALUES (?, ?)");
            foreach($units as $unit) {
                if(!empty(trim($unit))) {
                    $stmt_unit->execute([$subject_id, trim($unit)]);
                }
            }
        }
        
        $pdo->commit();
        $message = "<div class='alert alert-success'>Subject & Units added successfully!</div>";
    } catch(PDOException $e) {
        $pdo->rollBack();
        $message = "<div class='alert alert-error'>Failed to add: " . $e->getMessage() . "</div>";
    }
}

// Fetch all subjects
$subjects = [];
try {
    $stmt = $pdo->query("SELECT s.*, GROUP_CONCAT(u.unit_name SEPARATOR ' | ') as all_units 
                         FROM subjects s 
                         LEFT JOIN units u ON s.id = u.subject_id 
                         GROUP BY s.id ORDER BY s.id DESC");
    $subjects = $stmt->fetchAll();
} catch(Exception $e) {}
?>

<?= $message ?>

<div class="panel">
    <div class="panel-header">
        <h2 class="panel-title"><i class="fa-solid fa-plus-circle"></i> Add New Subject</h2>
    </div>
    
    <form action="" method="POST">
        <div class="form-grid">
            <div class="form-group" style="grid-column: 1 / -1;">
                <label>Subject Name</label>
                <input type="text" name="subject_name" class="form-control" required placeholder="e.g. Data Structures and Algorithms">
            </div>
            
            <div class="form-group" style="grid-column: 1 / -1;">
                <label>Syllabus Detail (Optional)</label>
                <textarea name="syllabus" class="form-control" placeholder="Brief syllabus description or topics..."></textarea>
            </div>
            
            <div class="form-group" style="grid-column: 1 / -1;">
                <label>Units / Topics Mapping</label>
                <div class="dynamic-list" id="unitsList">
                    <div class="list-item">
                        <input type="text" name="units[]" class="form-control" placeholder="Unit 1: Introduction">
                        <button type="button" class="btn-icon btn-danger" onclick="this.parentElement.remove()"><i class="fa-solid fa-trash"></i></button>
                    </div>
                </div>
                <button type="button" class="btn btn-outline btn-sm" onclick="addUnitRow()" style="margin-top:10px; width:auto;"><i class="fa-solid fa-plus"></i> Add Another Unit</button>
            </div>
        </div>
        <hr style="border:0; border-top:1px solid var(--border); margin:20px 0;">
        <button type="submit" name="add_subject" class="btn" style="width: auto;"><i class="fa-solid fa-save"></i> Save Subject to DB</button>
    </form>
</div>

<div class="panel">
    <div class="panel-header">
        <h2 class="panel-title"><i class="fa-solid fa-list-check"></i> Manage Subjects</h2>
    </div>
    
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Subject Name</th>
                    <th>Mapped Units</th>
                    <th>Syllabus Info</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($subjects as $sub): ?>
                <tr>
                    <td><?= $sub['id'] ?></td>
                    <td style="font-weight: 600; color: var(--primary);"><?= htmlspecialchars($sub['name']) ?></td>
                    <td style="font-size: 0.9rem; color: var(--text-muted);"><?= htmlspecialchars($sub['all_units']) ?: 'No units mapped' ?></td>
                    <td style="font-size: 0.9rem;"><?= htmlspecialchars(substr($sub['syllabus'] ?? '', 0, 50)) ?>...</td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($subjects)): ?>
                <tr>
                    <td colspan="4" style="text-align:center; padding: 20px; color: var(--text-muted);">No subjects found in the database. Add one above.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function addUnitRow() {
    const list = document.getElementById('unitsList');
    const div = document.createElement('div');
    div.className = 'list-item';
    div.innerHTML = `
        <input type="text" name="units[]" class="form-control" placeholder="New Unit / Topic">
        <button type="button" class="btn-icon btn-danger" onclick="this.parentElement.remove()"><i class="fa-solid fa-trash"></i></button>
    `;
    list.appendChild(div);
}
</script>

<?php include 'includes/footer.php'; ?>
