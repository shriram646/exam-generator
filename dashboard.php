<?php
require_once 'includes/config.php';
include 'includes/header.php';

// Fetch stats
function getCount($pdo, $table) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
        return $stmt->fetchColumn();
    } catch(Exception $e) { return 0; }
}

$subjectsCount = getCount($pdo, 'subjects');
$questionsCount = getCount($pdo, 'questions');
$papersCount = getCount($pdo, 'generated_papers');
?>

<!-- Mega Premium Hero Banner (Lightweight Theme) -->
<div style="background: linear-gradient(135deg, #f0fdf4 0%, #e0e7ff 100%); border-radius: var(--radius-lg); padding: 40px; color: var(--secondary); margin-bottom: 40px; position: relative; overflow: hidden; box-shadow: 0 15px 35px -10px rgba(0, 0, 0, 0.05); border: 1px solid rgba(79, 70, 229, 0.1);">
    <div style="position: absolute; top: -50px; right: -50px; width: 200px; height: 200px; background: rgba(255,255,255,0.4); backdrop-filter: blur(10px); border-radius: 50%; border: 1px solid rgba(255,255,255,0.6); z-index: 1;"></div>
    <div style="position: absolute; bottom: -30px; left: 20%; width: 120px; height: 120px; background: rgba(255,255,255,0.3); backdrop-filter: blur(5px); border-radius: 50%; z-index: 1;"></div>
    
    <div style="position: relative; z-index: 2; display: flex; align-items: center; gap: 40px;">
        <div style="display: none; @media(min-width: 768px){ display: block; } flex-shrink: 0;">
            <style>
                @keyframes softFloat {
                    0%, 100% { transform: translateY(0); }
                    50% { transform: translateY(-15px); }
                }
            </style>
            <img src="https://cdn-icons-png.flaticon.com/512/4762/4762295.png" alt="AI Exam Engine" style="width: 160px; filter: drop-shadow(0 20px 30px rgba(79, 70, 229, 0.2)); animation: softFloat 6s ease-in-out infinite;">
        </div>
        
        <div style="flex: 1;">
            <span style="background: rgba(79, 70, 229, 0.1); color: var(--primary); padding: 6px 14px; border-radius: 30px; font-size: 0.85rem; font-weight: 800; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 15px; display: inline-block; backdrop-filter: blur(5px);">Core Engine Active</span>
            <h1 style="font-size: 2.8rem; font-weight: 800; margin-bottom: 10px; line-height: 1.2;">Welcome to Auto GenExam</h1>
            <p style="font-size: 1.1rem; color: var(--text-muted); max-width: 650px; font-weight: 500;">Your intelligent command center. Leverage our automated algorithm to dynamically blueprint, construct, and export sophisticated paper structures instantly directly from the core databank.</p>
        </div>
    </div>
</div>

<!-- Statistics -->
<div class="stat-cards">
    <a href="subjects.php" class="stat-card" style="text-decoration:none; color:inherit; display:flex;">
        <div class="stat-info">
            <h3><?= str_pad($subjectsCount, 2, '0', STR_PAD_LEFT) ?></h3>
            <p>Active Subjects</p>
        </div>
        <div class="stat-icon" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(4, 120, 87, 0.15)); color: #047857; box-shadow: inset 0 0 0 1px rgba(16, 185, 129, 0.2);">
            <i class="fa-solid fa-book-open-reader"></i>
        </div>
    </a>
    
    <a href="questions.php" class="stat-card" style="text-decoration:none; color:inherit; display:flex;">
        <div class="stat-info">
            <h3><?= str_pad($questionsCount, 3, '0', STR_PAD_LEFT) ?></h3>
            <p>Questions Compiled</p>
        </div>
        <div class="stat-icon" style="background: linear-gradient(135deg, rgba(244, 63, 94, 0.15), rgba(190, 18, 60, 0.15)); color: #E11D48; box-shadow: inset 0 0 0 1px rgba(244, 63, 94, 0.2);">
            <i class="fa-solid fa-database"></i>
        </div>
    </a>
    
    <a href="archive.php" class="stat-card" style="text-decoration:none; color:inherit; display:flex;">
        <div class="stat-info">
            <h3><?= str_pad($papersCount, 2, '0', STR_PAD_LEFT) ?></h3>
            <p>Papers Synthesized</p>
        </div>
        <div class="stat-icon" style="background: linear-gradient(135deg, #E0E7FF, #C7D2FE); color: var(--primary); box-shadow: inset 0 0 0 1px #A5B4FC;">
            <i class="fa-solid fa-wand-magic-sparkles"></i>
        </div>
    </a>
</div>

<!-- Main Body Display of Working of Paper Generation -->
<div class="panel" style="border:none; box-shadow: var(--shadow-lg);">
    <div class="panel-header" style="border-bottom: 1px solid var(--border);">
        <h2 class="panel-title"><i class="fa-solid fa-code-branch" style="background: var(--primary-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"></i> Algorithmic Generation Framework</h2>
    </div>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 25px; margin-top: 15px;">
        <div style="padding: 25px; border-radius: var(--radius-lg); background: #ffffff; border: 1px solid rgba(79, 70, 229, 0.15); box-shadow: 0 10px 25px -5px rgba(79, 70, 229, 0.05); position: relative; overflow: hidden; transition: var(--transition);" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 20px 30px -5px rgba(79, 70, 229, 0.15)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 10px 25px -5px rgba(79, 70, 229, 0.05)';">
            <div style="width: 40px; height: 40px; background: rgba(79, 70, 229, 0.1); color: var(--primary); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 1.2rem; margin-bottom: 15px;">1</div>
            <h3 style="margin-bottom: 10px; color: var(--secondary); font-size: 1.25rem; font-weight: 800;">Configure Syllabus</h3>
            <p style="color: var(--text-muted); font-size: 0.95rem;">Integrate your academic subjects and fragment their syllabus into highly targeted, granular discrete units using our secure Subjects Engine.</p>
        </div>
        
        <div style="padding: 25px; border-radius: var(--radius-lg); background: #ffffff; border: 1px solid rgba(16, 185, 129, 0.15); box-shadow: 0 10px 25px -5px rgba(16, 185, 129, 0.05); position: relative; overflow: hidden; transition: var(--transition);" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 20px 30px -5px rgba(16, 185, 129, 0.15)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 10px 25px -5px rgba(16, 185, 129, 0.05)';">
            <div style="width: 40px; height: 40px; background: rgba(16, 185, 129, 0.1); color: #10B981; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 1.2rem; margin-bottom: 15px;">2</div>
            <h3 style="margin-bottom: 10px; color: var(--secondary); font-size: 1.25rem; font-weight: 800;">Feed Question Databank</h3>
            <p style="color: var(--text-muted); font-size: 0.95rem;">Populate the robust repository with high-quality strings tied to specific boundaries. Grade complexity and assign weightage natively.</p>
        </div>
        
        <div style="padding: 25px; border-radius: var(--radius-lg); background: #ffffff; border: 1px solid rgba(244, 63, 94, 0.15); box-shadow: 0 10px 25px -5px rgba(244, 63, 94, 0.05); position: relative; overflow: hidden; transition: var(--transition);" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 20px 30px -5px rgba(244, 63, 94, 0.15)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 10px 25px -5px rgba(244, 63, 94, 0.05)';">
            <div style="width: 40px; height: 40px; background: rgba(244, 63, 94, 0.1); color: #F43F5E; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 1.2rem; margin-bottom: 15px;">3</div>
            <h3 style="margin-bottom: 10px; color: var(--secondary); font-size: 1.25rem; font-weight: 800;">Algorithmic Synthesis</h3>
            <p style="color: var(--text-muted); font-size: 0.95rem;">Leverage our autonomous Blueprint Picker. The Engine mathematically processes explicit marks constraints to output perfectly balanced randomized blueprints securely.</p>
        </div>
        
        <div style="padding: 25px; border-radius: var(--radius-lg); background: #ffffff; border: 1px solid rgba(245, 158, 11, 0.15); box-shadow: 0 10px 25px -5px rgba(245, 158, 11, 0.05); position: relative; overflow: hidden; transition: var(--transition);" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 20px 30px -5px rgba(245, 158, 11, 0.15)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 10px 25px -5px rgba(245, 158, 11, 0.05)';">
            <div style="width: 40px; height: 40px; background: rgba(245, 158, 11, 0.1); color: #F59E0B; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 1.2rem; margin-bottom: 15px;">4</div>
            <h3 style="margin-bottom: 10px; color: var(--secondary); font-size: 1.25rem; font-weight: 800;">Realtime Extractor</h3>
            <p style="color: var(--text-muted); font-size: 0.95rem;">Review the finalized digital layout instantly. Validate, adjust structure interactively, and export physical standalone .PDFs exactly calibrated to your demands natively.</p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
