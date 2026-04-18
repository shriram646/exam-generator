<?php
require_once 'includes/config.php';

if(isset($_GET['subject_id'])) {
    $subject_id = (int)$_GET['subject_id'];
    $stmt = $pdo->prepare("SELECT id, unit_name FROM units WHERE subject_id = ?");
    $stmt->execute([$subject_id]);
    $units = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($units);
}
?>
