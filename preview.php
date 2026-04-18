<?php
require_once 'includes/config.php';
include 'includes/header.php';

if(!isset($_GET['id'])) {
    echo "No paper ID provided.";
    exit;
}

$id = (int)$_GET['id'];
$stmt = $pdo->prepare("SELECT p.*, s.name as subject_name FROM generated_papers p JOIN subjects s ON p.subject_id = s.id WHERE p.id = ?");
$stmt->execute([$id]);
$paper = $stmt->fetch();

if(!$paper) {
    echo "Paper not found.";
    exit;
}

$data = json_decode($paper['paper_data'], true);
$title = $data['title'] ?? 'Examination Paper';
$exam_date = $data['exam_date'] ?? '_________________';
$exam_time = $data['exam_time'] ?? '';
$structure = $data['structure'] ?? [];

// Calculate total marks and time (dummy time based on marks, approx 1.5 min per mark)
$totalMarks = 0;
foreach($structure as $diff => $sec) {
    $req = $sec['settings']['req'];
    $marks = $sec['settings']['marks'];
    $totalMarks += ($req * $marks);
}
$hours = floor(($totalMarks * 1.5) / 60);
$mins = ($totalMarks * 1.5) % 60;
if($hours == 0) $timeStr = $mins . " Mins";
else $timeStr = $hours . " Hrs " . ($mins > 0 ? $mins." Mins" : "");

$exam_end_time = $data['exam_end_time'] ?? '';
$total_marks_input = $data['total_marks'] ?? $totalMarks;

if($exam_time && $exam_end_time) {
    $timingString = htmlspecialchars(date('h:i A', strtotime($exam_time))) . " to " . htmlspecialchars(date('h:i A', strtotime($exam_end_time)));
} elseif($exam_time) {
    $duration_minutes = $totalMarks * 1.5;
    $endTime = date("h:i A", strtotime("+$duration_minutes minutes", strtotime($exam_time)));
    $timingString = htmlspecialchars(date('h:i A', strtotime($exam_time))) . " to " . $endTime;
} else {
    $timingString = '_________ to _________';
}
?>

<div class="no-print" style="margin-bottom: 20px; display:flex; justify-content:space-between; align-items:center;">
    <h2 style="color: var(--secondary);"><i class="fa-solid fa-file-pdf"></i> Paper Preview</h2>
    <div>
        <a href="generate.php" class="btn btn-outline" style="width:auto; margin-right: 10px;"><i class="fa-solid fa-arrow-left"></i> Back to Engine</a>
        <button onclick="downloadPDF()" class="btn" style="width:auto; background:var(--success);"><i class="fa-solid fa-file-pdf"></i> Generate PDF File</button>
    </div>
</div>
<!-- PDF Library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<div class="paper-sheet">
    <div class="paper-header">
        <h1>Navsahyadri Group of Institutes , Pune</h1>
        <h2><?= htmlspecialchars($title) ?></h2>
        
        <div class="paper-meta">
            <span>Subject: <?= htmlspecialchars($paper['subject_name']) ?></span>
            <span>Date: <?= $exam_date !== '_________________' ? htmlspecialchars(date('d M Y', strtotime($exam_date))) : $exam_date ?></span>
        </div>
        <div class="paper-meta" style="margin-top: 5px;">
            <span>Exam Timing: <?= $timingString ?></span>
            <span>Total Marks: <?= $total_marks_input ?></span>
        </div>
    </div>
    
    <div style="font-weight: bold; margin-bottom: 20px; text-decoration: underline; text-align:center;">
        INSTRUCTIONS TO CANDIDATES:
    </div>
    <ul style="margin-bottom: 30px; padding-left: 40px; font-size:0.95rem;">
        <li>Read all questions carefully before attempting.</li>
        <li>Follow the choice structure mentioned in each section.</li>
        <li>Figures to the right indicate full marks.</li>
    </ul>

    <?php 
    $sectionLabels = ['easy' => 'SECTION A', 'medium' => 'SECTION B', 'hard' => 'SECTION C', 'auto' => 'EXAM QUESTIONS'];
    
    foreach(['auto', 'easy', 'medium', 'hard'] as $diff): 
        if(!isset($structure[$diff])) continue;
        
        $sec = $structure[$diff];
        $settings = $sec['settings'];
        $questions = $sec['questions'];
        $req = $settings['req'];
        $opt = $settings['opt'];
        $marks = $settings['marks'];
        $totalProvide = $req + $opt;
        
        if($totalProvide == 0 || count($questions) == 0) continue;
    ?>
    <div style="margin-bottom: 40px;">
        <h3 style="text-align: center; font-size: 1.1rem; margin-bottom: 10px;">
            <?= $sectionLabels[$diff] ?>
        </h3>
        <div style="text-align: center; font-style: italic; margin-bottom: 20px; font-size: 0.95rem;">
            <?php if($opt > 0): ?>
                (Attempt any <?= $req ?> question<?= $req > 1 ? 's' : '' ?> from the following. Each question carries <?= $marks ?> marks.)
            <?php elseif($diff !== 'auto'): ?>
                (Attempt all <?= $req ?> question<?= $req > 1 ? 's' : '' ?>. Each question carries <?= $marks ?> marks.)
            <?php else: ?>
                (Attempt all <?= $req ?> question<?= $req > 1 ? 's' : '' ?>.)
            <?php endif; ?>
        </div>
        
        <?php $qNum = 1; foreach($questions as $q): ?>
        <div class="question-block">
            <div class="question-text">
                <span style="flex:1; padding-right: 20px;">
                    <strong>Q<?= $qNum ?>.</strong> <?= nl2br(htmlspecialchars($q['question_text'])) ?>
                </span>
                <span style="font-weight: bold;">[<?= isset($q['marks']) ? $q['marks'] : $marks ?>]</span>
            </div>
        </div>
        <?php $qNum++; endforeach; ?>
    </div>
    <?php endforeach; ?>
    
    <div style="text-align:center; font-weight:bold; margin-top:50px;">
        *** END OF PAPER ***
    </div>
</div>

<script>
    function downloadPDF() {
        var element = document.querySelector('.paper-sheet');
        var opt = {
          margin:       0.5,
          filename:     'AutoGenerated_Exam_Paper_<?= $paper_id ?? time() ?>.pdf',
          image:        { type: 'jpeg', quality: 0.98 },
          html2canvas:  { scale: 2 },
          jsPDF:        { unit: 'in', format: 'a4', orientation: 'portrait' }
        };
        html2pdf().set(opt).from(element).save();
    }

    // Automatically trigger Real PDF download
    window.onload = function() {
        setTimeout(function() {
            downloadPDF();
        }, 1000);
    };
</script>

<?php include 'includes/footer.php'; ?>
