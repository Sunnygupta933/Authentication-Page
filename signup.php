<?php
require_once 'db.php';
require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header text-center">
                    <h3>Sign Up</h3>
                </div>
                <div class="card-body">
                    <?php
                    require_once 'db.php';
                    require_once __DIR__ . '/vendor/autoload.php';
                    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
                    $dotenv->load();
                    $error = '';
                    $success = '';
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        $full_name = trim($_POST['full_name'] ?? '');
                        $email = trim($_POST['email'] ?? '');
                        $password = $_POST['password'] ?? '';
                        $confirm_password = $_POST['confirm_password'] ?? '';
                        $terms = isset($_POST['terms']);

                        
                        $old_full_name = $full_name;
                        $old_email = $email;
                        $old_terms = $terms;
                        
                        $old_password = '';
                        $old_confirm_password = '';

                        
                        if (!$full_name || !$email || !$password || !$confirm_password) {
                            $error = 'All fields are required.';
                        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            $error = 'Invalid email address.';
                        } elseif ($password !== $confirm_password) {
                            $error = 'Passwords do not match.';
                        } elseif (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
                            $error = 'Password must be at least 8 characters, include a number and an uppercase letter.';
                            $old_password = '';
                            $old_confirm_password = '';
                        } elseif (!$terms) {
                            $error = 'You must accept the terms & conditions.';
                        } else {
                            
                            $stmt = $conn->prepare('SELECT id FROM users WHERE email = ?');
                            $stmt->bind_param('s', $email);
                            $stmt->execute();
                            $stmt->store_result();
                            if ($stmt->num_rows > 0) {
                                $error = 'Email already exists.';
                            } else {
                                
                                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                                
                                $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                                $verification_code = $otp; 
                                $stmt = $conn->prepare('INSERT INTO users (full_name, email, password, email_verified, verification_code) VALUES (?, ?, ?, 0, ?)');
                                $stmt->bind_param('ssss', $full_name, $email, $hashed_password, $verification_code);
                                if ($stmt->execute()) {
                                    
                                    $verify_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/verify_email.php?code=$verification_code";
                                    $subject = "Verify your email";
                                    $message = "Hi $full_name,\n\nThank you for signing up!\n\nYour One-Time Password (OTP) is: $otp\n\nYou can verify your email by:\n1. Using the OTP code: $otp\n2. Or clicking this link: $verify_link\n\nThe OTP is valid for 10 minutes.\n\nBest Regards\nAuthentication Page";

                                    $mail = new PHPMailer(true);
                                    try {
                                        $mail->isSMTP();
                                        $mail->Host = 'smtp.gmail.com';
                                        $mail->SMTPAuth = true;
                                        $mail->Username = $_ENV['SMTP_EMAIL']; 
                                        $mail->Password = $_ENV['SMTP_PASSWORD'];    
                                        $mail->SMTPSecure = 'tls';
                                        $mail->Port = 587;

                                        $mail->setFrom($_ENV['SMTP_EMAIL'], 'Verify-Email');
                                        $mail->addAddress($email, $full_name);
                                        $mail->Subject = $subject;
                                        $mail->Body    = $message;

                                        $mail->send();
                                        $success = 'Account created! Please check your email for the OTP code to verify your account.';
                                        
                                        if (session_status() === PHP_SESSION_NONE) {
                                            session_start();
                                        }
                                        $_SESSION['pending_verification_email'] = $email;
                                    } catch (Exception $e) {
                                        $error = 'Email could not be sent. Mailer Error: ' . $mail->ErrorInfo;
                                    }
                                } else {
                                    $error = 'Registration failed. Please try again.';
                                }
                            }
                        }
                    }
                    ?>
                    <?php if ($error): ?>
                        <div class="alert alert-danger"> <?= htmlspecialchars($error) ?> </div>
                    <?php elseif ($success): ?>
                        <div class="alert alert-success">
                            <?= htmlspecialchars($success) ?>
                            <div class="mt-3">
                                <a href="verify_email.php" class="btn btn-primary btn-sm">Enter OTP Code</a>
                            </div>
                        </div>
                    <?php endif; ?>
                    <form method="POST" novalidate>
                        <div class="mb-3">
                            <label for="full_name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" required value="<?= htmlspecialchars($old_full_name ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email address</label>
                            <input type="email" class="form-control" id="email" name="email" required value="<?= htmlspecialchars($old_email ?? '') ?>">
                        </div>
                        <div class="mb-3 position-relative">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password" name="password" required value="<?= htmlspecialchars($old_password ?? '') ?>">
                                <button type="button" class="btn btn-outline-secondary" tabindex="-1" onclick="togglePassword('password', this)" style="border-left: 0; padding: 0.375rem 0.75rem;">
                                    <i class="bi bi-eye" id="icon-password" style="font-size: 1.1rem;"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mb-3 position-relative">
                            <label for="confirm_password" class="form-label">Confirm Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required value="<?= htmlspecialchars($old_confirm_password ?? '') ?>" autocomplete="off">
                                <button type="button" class="btn btn-outline-secondary" tabindex="-1" onclick="togglePassword('confirm_password', this)" style="border-left: 0; padding: 0.375rem 0.75rem;">
                                    <i class="bi bi-eye" id="icon-confirm_password" style="font-size: 1.1rem;"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="terms" name="terms" required <?= !empty($old_terms) ? 'checked' : '' ?> >
                            <label class="form-check-label" for="terms">I accept the <a href="#">terms & conditions</a></label>
                        </div>
                        <button type="submit" class="btn btn-success w-100">Sign Up</button>
                    </form>
                    <div class="mt-3 text-center">
                        <a href="login.php">Already have an account? Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function togglePassword(fieldId, btn) {
    const input = document.getElementById(fieldId);
    const icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
    
    setTimeout(function() {
        input.focus();
        input.setSelectionRange(input.value.length, input.value.length);
    }, 0);
}
</script>
</body>
</html>