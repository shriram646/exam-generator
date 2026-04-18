<?php
require_once 'includes/config.php';
include 'includes/header.php';

$message = '';

// Handle Undo/Delete Operation
if(isset($_GET['delete_id'])) {
    $del_id = (int)$_GET['delete_id'];
    $pdo->query("DELETE FROM questions WHERE id = $del_id");
    header("Location: questions.php?msg=deleted");
    exit;
}
if(isset($_GET['msg']) && $_GET['msg'] == 'deleted') {
    $message = "<div class='alert alert-info'>Undo successful! Question removed.</div>";
}

// Add Question
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_question'])) {
    $subject_id = (int)$_POST['subject_id'];
    $unit_id = (int)$_POST['unit_id'];
    $difficulty = $_POST['difficulty'];
    $marks = (int)$_POST['marks'];
    $question_text = trim($_POST['question_text']);
    
    // Data Redundancy Check
    $stmt = $pdo->prepare("SELECT id FROM questions WHERE subject_id = ? AND question_text = ?");
    $stmt->execute([$subject_id, $question_text]);
    
    if($stmt->rowCount() > 0) {
        $message = "<div class='alert alert-error'><strong>Data Redundancy Detected:</strong> This question already exists in this subject.</div>";
    } else {
        $stmt = $pdo->prepare("INSERT INTO questions (subject_id, unit_id, question_text, difficulty, marks) VALUES (?, ?, ?, ?, ?)");
        if($stmt->execute([$subject_id, $unit_id, $question_text, $difficulty, $marks])) {
            $last_id = $pdo->lastInsertId();
            $message = "<div class='alert alert-success' style='display:flex; justify-content:space-between;'>
                <span>Question added to bank successfully!</span>
                <a href='questions.php?delete_id=$last_id' style='color:#155724; font-weight:bold; text-decoration:underline;'><i class='fa-solid fa-rotate-left'></i> Undo</a>
            </div>";
        }
    }
}

// Fetch all subjects for dropdown
$subjects = $pdo->query("SELECT * FROM subjects ORDER BY name ASC")->fetchAll();

// Fetch filter parameters if any
$filter_subject = isset($_GET['filter_subject']) ? (int)$_GET['filter_subject'] : '';

// Query Questions for Bank View
$q_sql = "SELECT q.*, s.name as subject_name, u.unit_name 
          FROM questions q 
          JOIN subjects s ON q.subject_id = s.id 
          JOIN units u ON q.unit_id = u.id ";
if($filter_subject) {
    $q_sql .= " WHERE q.subject_id = $filter_subject ";
}
$q_sql .= " ORDER BY q.id DESC";
$questions = $pdo->query($q_sql)->fetchAll();
?>

<?= $message ?>

<div class="panel">
    <div class="panel-header">
        <h2 class="panel-title"><i class="fa-solid fa-folder-plus"></i> Append Question Bank</h2>
    </div>
    
    <form action="questions.php" method="POST">
        <div class="form-grid">
            <div class="form-group">
                <label>Select Subject</label>
                <select name="subject_id" id="subjectSelect" class="form-control" required onchange="fetchUnits(this.value)">
                    <option value="">-- Choose Subject --</option>
                    <?php foreach($subjects as $sub): ?>
                        <option value="<?= $sub['id'] ?>"><?= htmlspecialchars($sub['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Mapped Unit / Topic</label>
                <select name="unit_id" id="unitSelect" class="form-control" required>
                    <option value="">-- Select Subject First --</option>
                </select>
            </div>

            <div class="form-group">
                <label>Difficulty Metric</label>
                <select name="difficulty" class="form-control" required>
                    <option value="easy">Easy (Low Complexity)</option>
                    <option value="medium">Medium (Standard)</option>
                    <option value="hard">Hard (Analytical/Advanced)</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Mark Weightage</label>
                <input type="number" name="marks" class="form-control" min="1" max="100" required placeholder="e.g. 5">
            </div>

            <div class="form-group" style="grid-column: 1 / -1;">
                <label>Question Statement</label>
                <textarea name="question_text" class="form-control" required placeholder="Enter the exact question text here..."></textarea>
            </div>
        </div>
        <button type="submit" name="add_question" class="btn" style="width: auto;"><i class="fa-solid fa-plus"></i> Inject Question into Bank</button>
    </form>
</div>

<div class="panel">
    <div class="panel-header" style="flex-wrap: wrap; gap: 15px;">
        <h2 class="panel-title"><i class="fa-solid fa-database"></i> Curated Question Bank</h2>
        
        <form method="GET" style="display:flex; gap:10px; align-items:center;">
            <select name="filter_subject" class="form-control" style="width: 250px; padding: 8px;">
                <option value="">All Subjects</option>
                <?php foreach($subjects as $sub): ?>
                    <option value="<?= $sub['id'] ?>" <?= $filter_subject == $sub['id'] ? 'selected' : '' ?>><?= htmlspecialchars($sub['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-outline btn-sm">Filter</button>
            <a href="generate.php" class="btn btn-secondary btn-sm" style="margin-left: 15px;"><i class="fa-solid fa-wand-magic-sparkles"></i> Generate Paper</a>
        </form>
    </div>
    
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Question</th>
                    <th>Subject & Unit</th>
                    <th>Weight</th>
                    <th>Level</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($questions as $q): ?>
                <tr>
                    <td style="max-width: 300px; line-height:1.4;">
                        <span style="font-weight: 500;"><?= htmlspecialchars(substr($q['question_text'], 0, 80)) ?><?= strlen($q['question_text']) > 80 ? '...' : '' ?></span>
                    </td>
                    <td style="font-size: 0.9rem;">
                        <strong><?= htmlspecialchars($q['subject_name']) ?></strong><br>
                        <span style="color:var(--text-muted);"><i class="fa-solid fa-bookmark" style="font-size:0.8rem;"></i> <?= htmlspecialchars($q['unit_name']) ?></span>
                    </td>
                    <td><span style="font-weight:bold; color:var(--primary);"><?= $q['marks'] ?> M</span></td>
                    <td>
                        <span class="badge <?= $q['difficulty'] ?>"><?= ucfirst($q['difficulty']) ?></span>
                    </td>
                    <td>
                        <a href="questions.php?delete_id=<?= $q['id'] ?>" class="btn-icon btn-danger" title="Delete / Redo"><i class="fa-solid fa-trash"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($questions)): ?>
                <tr><td colspan="5" style="text-align:center; padding: 20px;">No questions mapped yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function fetchUnits(subjId) {
    const unitSelect = document.getElementById('unitSelect');
    unitSelect.innerHTML = '<option value="">Loading...</option>';
    
    if(!subjId) {
        unitSelect.innerHTML = '<option value="">-- Select Subject First --</option>';
        return;
    }
    
    fetch(`get_units.php?subject_id=${subjId}`)
        .then(response => response.json())
        .then(data => {
            unitSelect.innerHTML = '<option value="">-- Choose Mapped Unit --</option>';
            data.forEach(unit => {
                const opt = document.createElement('option');
                opt.value = unit.id;
                opt.textContent = unit.unit_name;
                unitSelect.appendChild(opt);
            });
        })
        .catch(err => {
            console.error(err);
            unitSelect.innerHTML = '<option value="">Error Loading Units</option>';
        });
}
</script>

<?php include 'includes/footer.php'; ?>
