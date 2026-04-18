<?php
if(session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/config.php';

// If user is already logged in, redirect to dashboard
if(isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = '';
$success = '';

// Handle Registration
if(isset($_POST['register'])) {
    $fullName = trim($_POST['fullName']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $facultyCode = trim($_POST['facultyCode']);
    
    if($facultyCode !== 'FACULTY2026') {
        $error = "Invalid Faculty Access Code. Registration denied.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if($stmt->rowCount() > 0) {
                $error = "Email already registered.";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (fullName, email, phone, password) VALUES (?, ?, ?, ?)");
                $stmt->execute([$fullName, $email, $phone, $hashed_password]);
                $success = "Registration successful! You can now login.";
            }
        } catch(PDOException $e) {
            $error = "Registration failed: " . $e->getMessage();
            if(strpos($e->getMessage(), 'exam_generator') !== false) {
                 $error = "Database not set up. Please run <a href='setup.php'>setup.php</a>";
            }
        }
    }
}

// Handle Login
if(isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $user_otp = trim($_POST['otp']); 
    
    // Check if OTP matches backend session
    if (!isset($_SESSION['login_otp']) || !isset($_SESSION['login_otp_email'])) {
        $error = "Please request an OTP first.";
    } elseif ($email !== $_SESSION['login_otp_email']) {
        $error = "The email does not match the one the OTP was sent to.";
    } elseif ($user_otp != $_SESSION['login_otp']) { 
        $error = "Invalid OTP entered. Please try again.";
    } else {
        // Valid OTP scenario
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if($user && password_verify($password, $user['password'])) {
                // Clear OTP session logic post valid verification
                unset($_SESSION['login_otp']);
                unset($_SESSION['login_otp_time']);
                unset($_SESSION['login_otp_email']);
                
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['fullName'];
                $_SESSION['user_role'] = $user['role'];
                header("Location: dashboard.php");
                exit;
            } else {
                $error = "Invalid email or password.";
            }
        } catch(PDOException $e) {
            $error = "Login failed. Ensure DB is created via <a href='setup.php'>setup.php</a>.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Platform Authentication | Auto GenExam</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; color: #334155; overflow: hidden; }
        h1, h2, h3 { font-family: 'Outfit', sans-serif; }
        
        .split-layout {
            display: flex;
            height: 100vh;
            width: 100vw;
        }

        /* Left Branding Side */
        .brand-section {
            flex: 1.2;
            background: linear-gradient(135deg, rgba(79, 70, 229, 0.9) 0%, rgba(124, 58, 237, 0.9) 100%), url('https://images.unsplash.com/photo-1434030216411-0b793f4b4173?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80') center/cover;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px;
            color: white;
            position: relative;
        }

        .brand-content {
            z-index: 2;
            text-align: center;
            max-width: 500px;
        }

        .brand-content i {
            font-size: 5rem;
            margin-bottom: 20px;
            filter: drop-shadow(0 10px 20px rgba(0,0,0,0.2));
        }

        .brand-content h1 {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 15px;
            letter-spacing: -1px;
            line-height: 1.1;
        }

        .brand-content p {
            font-size: 1.1rem;
            line-height: 1.6;
            opacity: 0.9;
        }

        /* Floating Glass Orbs Animation Elements */
        .glass-orb {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(8px);
            border-radius: 50%;
            border: 1px solid rgba(255, 255, 255, 0.2);
            animation: float 8s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) scale(1); }
            50% { transform: translateY(-30px) scale(1.05); }
        }

        /* Right Form Side */
        .form-section {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #ffffff;
            position: relative;
            box-shadow: -10px 0 30px rgba(0,0,0,0.05);
            z-index: 5;
        }

        .slider-container {
            width: 100%;
            max-width: 500px;
            overflow: hidden;
            padding: 40px;
        }

        /* THE SLIDING MECHANISM */
        .slider-track {
            display: flex;
            width: 200%;
            transition: transform 0.7s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        .form-panel {
            width: 50%;
            padding: 0 10px;
            transition: opacity 0.4s ease;
        }

        /* Form Visual Elements */
        .form-title {
            font-size: 2.2rem;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 5px;
        }

        .form-subtitle {
            color: #64748b;
            font-size: 0.95rem;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 0.85rem;
            color: #334155;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-control {
            width: 100%;
            padding: 14px 18px;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            font-size: 1rem;
            font-family: inherit;
            transition: all 0.3s ease;
            background: #f8fafc;
            color: #1e293b;
        }

        .form-control:focus {
            outline: none;
            border-color: #4F46E5;
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
        }

        .btn-primary {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(79, 70, 229, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(79, 70, 229, 0.4);
        }

        .btn-outline {
            padding: 14px;
            background: transparent;
            border: 2px solid #4F46E5;
            color: #4F46E5;
            border-radius: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-outline:hover {
            background: #4F46E5;
            color: white;
        }

        .toggle-link {
            text-align: center;
            margin-top: 25px;
            font-size: 0.95rem;
            color: #64748b;
        }

        .toggle-link span {
            color: #4F46E5;
            font-weight: 700;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .toggle-link span:hover {
            color: #7C3AED;
        }

        /* Custom Hackathon Level Alerts */
        .alert {
            padding: 14px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .alert-error { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
        .alert-success { background: #d1fae5; color: #047857; border: 1px solid #a7f3d0; }
        .alert-info { background: #dbeafe; color: #1d4ed8; border: 1px solid #bfdbfe; }

        @media (max-width: 900px) {
            .brand-section { display: none; }
            body { overflow: auto; }
            .split-layout { height: auto; min-height: 100vh; }
        }
    </style>
</head>
<body>

<div class="split-layout">
    <!-- Visual Thematic Background -->
    <div class="brand-section">
        <!-- Floating Abstract Glass Orbs -->
        <div class="glass-orb" style="width: 350px; height: 350px; top: -100px; left: -100px; animation-delay: 0s;"></div>
        <div class="glass-orb" style="width: 250px; height: 250px; bottom: 50px; right: 80px; animation-delay: -4s;"></div>
        
        <div class="brand-content">
            <i class="fa-solid fa-wand-magic-sparkles"></i>
            <h1>Auto GenExam</h1>
            <p>The next-generation framework for educators. Automatically process, compile, and validate highly optimized examination papers directly from your question databanks within seconds.</p>
        </div>
    </div>

    <!-- Interactive Sliding Form Base -->
    <div class="form-section">
        <div class="slider-container">
            
            <?php if($error): ?>
                <div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i> <?= $error ?></div>
            <?php endif; ?>
            <?php if($success): ?>
                <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> <?= $success ?></div>
            <?php endif; ?>
            <div id="otpMessage" style="display:none; text-align:center; margin-bottom: 15px; font-weight:700;"></div>

            <div class="slider-track" id="sliderTrack">
                
                <!-- LOGIN PANEL (Visible Default) -->
                <div class="form-panel" id="loginPanel">
                    <h2 class="form-title">Welcome Back</h2>
                    <p class="form-subtitle">Enter your credentials to access the Engine capabilities.</p>
                    
                    <form action="" method="POST">
                        <div class="form-group">
                            <label class="form-label">Email Address</label>
                            <input type="email" id="loginEmail" name="email" class="form-control" required placeholder="faculty@university.edu" autocomplete="email">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required placeholder="••••••••">
                        </div>
                        <div class="form-group">
                            <label class="form-label">2FA Live Verification</label>
                            <div style="display:flex; gap:10px;">
                                <input type="text" name="otp" class="form-control" placeholder="6-Digit Code" required>
                                <button type="button" class="btn-outline" style="white-space:nowrap;" onclick="requestOTP()" id="otpBtn">Request OTP</button>
                            </div>
                        </div>
                        
                        <button type="submit" name="login" class="btn-primary" style="margin-top:10px;">
                            Secure Authenticate <i class="fa-solid fa-arrow-right-to-bracket"></i>
                        </button>
                    </form>

                    <div class="toggle-link">
                        New system operator? <span onclick="slideTo('register')">Create an account</span>
                    </div>
                </div>

                <!-- REGISTER PANEL (Hidden Initially) -->
                <div class="form-panel" id="registerPanel" style="opacity: 0; pointer-events: none;">
                    <h2 class="form-title">Join Platform</h2>
                    <p class="form-subtitle">Register explicitly to unlock automatic compilation.</p>
                    
                    <form action="" method="POST">
                        <div class="form-group">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="fullName" class="form-control" required placeholder="Dr. John Doe">
                        </div>
                        <div class="form-group" style="display:flex; gap:15px;">
                            <div style="flex:1;">
                                <label class="form-label">Email Address</label>
                                <input type="email" name="email" class="form-control" required placeholder="faculty@edu.com">
                            </div>
                            <div style="flex:1;">
                                <label class="form-label">Mobile</label>
                                <input type="text" name="phone" class="form-control" required placeholder="+1 234 xxxx">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Faculty Access Code</label>
                            <input type="text" name="facultyCode" class="form-control" required placeholder="FACULTY2026">
                        </div>
                        <div class="form-group">
                            <label class="form-label">System Password</label>
                            <input type="password" name="password" class="form-control" required placeholder="••••••••">
                        </div>
                        
                        <button type="submit" name="register" class="btn-primary" style="margin-top:5px; background: #1E293B; box-shadow: 0 4px 15px rgba(30, 41, 59, 0.3);">
                            Register Identity <i class="fa-solid fa-user-plus"></i>
                        </button>
                    </form>

                    <div class="toggle-link">
                        Already explicitly registered? <span onclick="slideTo('login')">Return to Login</span>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
    // Handles the breathtaking Hackathon Slide Animation
    function slideTo(target) {
        const track = document.getElementById('sliderTrack');
        const loginPanel = document.getElementById('loginPanel');
        const registerPanel = document.getElementById('registerPanel');
        
        if(target === 'register') {
            track.style.transform = 'translateX(-50%)';
            loginPanel.style.opacity = '0';
            loginPanel.style.pointerEvents = 'none';
            setTimeout(() => {
                registerPanel.style.opacity = '1';
                registerPanel.style.pointerEvents = 'auto';
            }, 250);
        } else {
            track.style.transform = 'translateX(0%)';
            registerPanel.style.opacity = '0';
            registerPanel.style.pointerEvents = 'none';
            setTimeout(() => {
                loginPanel.style.opacity = '1';
                loginPanel.style.pointerEvents = 'auto';
            }, 250);
        }
    }

    function requestOTP() {
        const emailInput = document.getElementById('loginEmail').value;
        const msgBox = document.getElementById('otpMessage');
        const btn = document.getElementById('otpBtn');
        
        if(!emailInput) {
            msgBox.style.display = 'block';
            msgBox.className = 'alert alert-error';
            msgBox.innerHTML = '<i class="fa-solid fa-circle-exclamation"></i> Please enter your email first to receive OTP.';
            return;
        }

        btn.disabled = true;
        btn.innerHTML = 'Sending...';
        msgBox.style.display = 'block';
        msgBox.className = 'alert alert-info';
        msgBox.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Triggering SMTP Interface...';

        const formData = new FormData();
        formData.append('email', emailInput);

        fetch('send_otp.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = 'Request OTP';
            
            if(data.status === 'success') {
                msgBox.className = 'alert alert-success';
                let outputMsg = '<i class="fa-solid fa-circle-check"></i> ' + data.message;
                
                if(data.fallback_otp) {
                    outputMsg += ' <br><br><span style="padding:4px 8px; background:rgba(255,255,255,0.5); border-radius:4px; font-size:0.85rem; color:#1E293B;">Demo Output: <strong>' + data.fallback_otp + '</strong></span>';
                }
                msgBox.innerHTML = outputMsg;
            } else {
                msgBox.className = 'alert alert-error';
                msgBox.innerHTML = '<i class="fa-solid fa-circle-exclamation"></i> ' + data.message;
            }
        })
        .catch(err => {
            console.error(err);
            btn.disabled = false;
            btn.innerHTML = 'Request OTP';
            msgBox.className = 'alert alert-error';
            msgBox.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i> Server execution collapsed. Check console logs.';
        });
    }
</script>
</body>
</html>
