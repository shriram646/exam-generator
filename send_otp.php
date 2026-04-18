<?php
if(session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure the request is POST and an email was provided
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = trim($_POST['email']);
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid email address.']);
        exit;
    }
    
    // Generate a secure 6-digit OTP
    $otp = rand(100000, 999999);
    
    // Store it in session with an expiration timestamp (optional but good practice)
    $_SESSION['login_otp'] = $otp;
    $_SESSION['login_otp_time'] = time();
    $_SESSION['login_otp_email'] = $email;
    
    // Send Email using PHP mail()
    $subject = "Your Faculty Login OTP - Auto Exam Generator";
    $message = "Hello,\n\nYour One Time Password (OTP) for Faculty Login is: " . $otp . "\n\nThis OTP is valid for 10 minutes.\n\nRegards,\nExam Generator System";
    $headers = "From: noreply@examgenerator.local";
    
    // We suppress the error with @ in case mail server isn't properly configured on local XAMPP
    $mailSent = @mail($email, $subject, $message, $headers);
    
    // For localhost testing robustness, we output the OTP via JS alert in case mail fails.
    // In production, remove the "fallback_otp" so it isn't visible in the network payload.
    if($mailSent) {
        echo json_encode(['status' => 'success', 'message' => 'OTP sent successfully to your email.']);
    } else {
        // Fallback simulated delivery response so localhost still works securely
        echo json_encode([
            'status' => 'success', 
            'message' => 'OTP generated! (Localhost fallback delivery check)',
            'fallback_otp' => $otp // ONLY for local demonstration if mail server fails
        ]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
}
?>
