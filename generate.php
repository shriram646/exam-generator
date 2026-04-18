<?php
require_once 'includes/config.php';
include 'includes/header.php';

$error = '';

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['generate'])) {
    $subject_id = (int)$_POST['subject_id'];
    $paper_title = trim($_POST['paper_title']);
    $exam_date = trim($_POST['exam_date']);
    $exam_time = trim($_POST['exam_time']);
    $exam_end_time = trim($_POST['exam_end_time']);
    $total_marks_input = (int)$_POST['total_marks'];
    
    // Config: [difficulty] => [required, optional, marks]
    $config = [
        'easy' => ['req' => (int)($_POST['easy_req'] ?? 0), 'opt' => (int)($_POST['easy_opt'] ?? 0), 'marks' => (int)($_POST['easy_marks'] ?? 2)],
        'medium' => ['req' => (int)($_POST['med_req'] ?? 0), 'opt' => (int)($_POST['med_opt'] ?? 0), 'marks' => (int)($_POST['med_marks'] ?? 5)],
        'hard' => ['req' => (int)($_POST['hard_req'] ?? 0), 'opt' => (int)($_POST['hard_opt'] ?? 0), 'marks' => (int)($_POST['hard_marks'] ?? 10)]
    ];
    
    $total_grid_reqs = $config['easy']['req'] + $config['medium']['req'] + $config['hard']['req'];
    $generatedStructure = [];
    
    if($total_grid_reqs > 0) {
        // Blueprint Mode (Allows Optional Questions)
        foreach($config as $diff => $settings) {
            $totalRequested = $settings['req'] + $settings['opt'];
            if($totalRequested > 0) {
                $stmt = $pdo->prepare("SELECT id, question_text, marks FROM questions WHERE subject_id = ? AND difficulty = ? ORDER BY RAND() LIMIT ?");
                $stmt->bindValue(1, $subject_id, PDO::PARAM_INT);
                $stmt->bindValue(2, $diff, PDO::PARAM_STR);
                $stmt->bindValue(3, $totalRequested, PDO::PARAM_INT);
                $stmt->execute();
                $fetched = $stmt->fetchAll();
                
                if(count($fetched) < $totalRequested) {
                    $error .= "Not enough $diff questions. Found " . count($fetched) . ", needed $totalRequested.<br>";
                }
                $generatedStructure[$diff] = ['settings' => $settings, 'questions' => $fetched];
            }
        }
    } else {
        // Pure Auto Mode (Fallback based strictly on Total Marks limit natively)
        $stmt = $pdo->prepare("SELECT id, question_text, marks, difficulty FROM questions WHERE subject_id = ? ORDER BY RAND()");
        $stmt->execute([$subject_id]);
        $allQ = $stmt->fetchAll();
        
        $picked_questions = [];
        $current_marks = 0;
        
        foreach($allQ as $q) {
            if(($current_marks + (int)$q['marks']) <= $total_marks_input) {
                $picked_questions[] = $q;
                $current_marks += (int)$q['marks'];
            }
            if($current_marks == $total_marks_input) break;
        }
        
        if($current_marks < $total_marks_input) {
            $error = "Not enough questions in DB to perfectly match $total_marks_input marks. Reached $current_marks marks.";
        }
        $generatedStructure['auto'] = [
            'settings' => ['req' => count($picked_questions), 'opt' => 0, 'marks' => 0],
            'questions' => $picked_questions
        ];
    }
        
    if(!$error) {
        // Save to mapped JSON structure
        $paperData = json_encode([
            'title' => $paper_title,
            'exam_date' => $exam_date,
            'exam_time' => $exam_time,
            'exam_end_time' => $exam_end_time,
            'total_marks' => $total_marks_input,
            'structure' => $generatedStructure
        ]);
        
        $stmt = $pdo->prepare("INSERT INTO generated_papers (subject_id, paper_name, paper_data) VALUES (?, ?, ?)");
        $stmt->execute([$subject_id, $paper_title, $paperData]);
        $paper_id = $pdo->lastInsertId();
        
        header("Location: preview.php?id=$paper_id");
        exit;
    }
}

$subjects = $pdo->query("SELECT * FROM subjects ORDER BY name ASC")->fetchAll();
?>

<?php if($error): ?>
    <div class="alert alert-error"><?= $error ?></div>
<?php endif; ?>

<div class="panel">
    <div class="panel-header">
        <h2 class="panel-title"><i class="fa-solid fa-gears"></i> Engine Configuration: Generate Paper</h2>
    </div>
    
    <form action="" method="POST">
        <div class="form-grid">
            <div class="form-group" style="grid-column: 1 / -1;">
                <label>Paper Title / Assessment Name</label>
                <input type="text" name="paper_title" class="form-control" required placeholder="e.g. End Semester Examination - Fall 2026">
            </div>
            
            <div class="form-group">
                <label>Exam Date</label>
                <input type="date" name="exam_date" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label>Exam Start Time</label>
                <input type="time" name="exam_time" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label>Exam End Time</label>
                <input type="time" name="exam_end_time" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Total Marks (For Output)</label>
                <input type="number" name="total_marks" class="form-control" required>
            </div>
            
            <div class="form-group" style="grid-column: 1 / -1;">
                <label>Target Subject</label>
                <select name="subject_id" class="form-control" required>
                    <option value="">-- Choose Subject --</option>
                    <?php foreach($subjects as $sub): ?>
                        <option value="<?= $sub['id'] ?>"><?= htmlspecialchars($sub['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <h3 style="margin: 25px 0 15px; color: var(--secondary); font-size: 1.1rem; border-bottom: 2px solid var(--border); padding-bottom: 10px;">
            Advanced Choices Mapping (Optional)
            <span style="display:block; font-size:0.85rem; color:var(--text-muted); margin-top:5px; font-weight:normal;">Leave all required values at '0' to auto-generate purely utilizing Total Marks. Fill below to define optional questions (e.g., Attempt any 2).</span>
        </h3>
        
        <!-- Easy -->
        <div style="background: rgba(46,204,113,0.05); padding: 15px; border-radius: 8px; margin-bottom: 15px; border-left: 4px solid var(--success);">
            <strong style="display:block; margin-bottom:10px; color:var(--text-main);">Section A: Easy Complexity</strong>
            <div class="form-grid">
                <div class="form-group"><label>Questions to Attempt</label><input type="number" name="easy_req" class="form-control" value="0" min="0"></div>
                <div class="form-group"><label>Optional Questions (Choices)</label><input type="number" name="easy_opt" class="form-control" value="0" min="0"></div>
                <div class="form-group"><label>Marks Per Question</label><input type="number" name="easy_marks" class="form-control" value="2" min="1"></div>
            </div>
        </div>
        
        <!-- Medium -->
        <div style="background: rgba(243,156,18,0.05); padding: 15px; border-radius: 8px; margin-bottom: 15px; border-left: 4px solid #f39c12;">
            <strong style="display:block; margin-bottom:10px; color:var(--text-main);">Section B: Medium Standard</strong>
            <div class="form-grid">
                <div class="form-group"><label>Questions to Attempt</label><input type="number" name="med_req" class="form-control" value="0" min="0"></div>
                <div class="form-group"><label>Optional Questions (Choices)</label><input type="number" name="med_opt" class="form-control" value="0" min="0"></div>
                <div class="form-group"><label>Marks Per Question</label><input type="number" name="med_marks" class="form-control" value="5" min="1"></div>
            </div>
        </div>

        <!-- Hard -->
        <div style="background: rgba(231,76,60,0.05); padding: 15px; border-radius: 8px; margin-bottom: 25px; border-left: 4px solid var(--accent);">
            <strong style="display:block; margin-bottom:10px; color:var(--text-main);">Section C: High Difficulty / Analytical</strong>
            <div class="form-grid">
                <div class="form-group"><label>Questions to Attempt</label><input type="number" name="hard_req" class="form-control" value="0" min="0"></div>
                <div class="form-group"><label>Optional Questions (Choices)</label><input type="number" name="hard_opt" class="form-control" value="0" min="0"></div>
                <div class="form-group"><label>Marks Per Question</label><input type="number" name="hard_marks" class="form-control" value="10" min="1"></div>
            </div>
        </div>

        <button type="submit" name="generate" class="btn" style="margin-top: 20px;"><i class="fa-solid fa-microchip"></i> Execute Automatic Generation</button>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
