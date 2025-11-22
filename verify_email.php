<?php

require_once 'db.php';

$verified = false;
$message = '';
$show_otp_form = false;


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['otp'])) {
    $otp = trim($_POST['otp']);
    if (strlen($otp) === 6 && ctype_digit($otp)) {
        $stmt = $conn->prepare('SELECT id FROM users WHERE verification_code = ? AND email_verified = 0');
        $stmt->bind_param('s', $otp);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows === 1) {
            $stmt->bind_result($id);
            $stmt->fetch();
            $update = $conn->prepare('UPDATE users SET email_verified = 1, verification_code = NULL WHERE id = ?');
            $update->bind_param('i', $id);
            $update->execute();
            $verified = true;
            $message = 'Email verified successfully! You can now log in.';
        } else {
            $message = 'Invalid OTP code. Please check and try again.';
            $show_otp_form = true;
        }
    } else {
        $message = 'Please enter a valid 6-digit OTP code.';
        $show_otp_form = true;
    }
}

elseif (isset($_GET['code'])) {
    $code = $_GET['code'];
    $stmt = $conn->prepare('SELECT id FROM users WHERE verification_code = ? AND email_verified = 0');
    $stmt->bind_param('s', $code);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows === 1) {
        $stmt->bind_result($id);
        $stmt->fetch();
        $update = $conn->prepare('UPDATE users SET email_verified = 1, verification_code = NULL WHERE id = ?');
        $update->bind_param('i', $id);
        $update->execute();
        $verified = true;
        $message = 'Email verified successfully! You can now log in.';
    } else {
        $message = 'Invalid or expired verification code.';
        $show_otp_form = true;
    }
} else {
    $show_otp_form = true;
    if (!$message) {
        $message = 'Please enter the OTP code sent to your email.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header text-center">
                    <h3><i class="bi bi-shield-check"></i> Email Verification</h3>
                    <small class="text-muted">Enter your OTP code to verify your email</small>
                </div>
                <div class="card-body">
                    <?php if ($verified): ?>
                        <div class="alert alert-success text-center"> <?= htmlspecialchars($message) ?> </div>
                        <div class="text-center">
                            <a href="login.php" class="btn btn-primary">Login</a>
                        </div>
                    <?php else: ?>
                        <?php if ($message): ?>
                            <div class="alert <?= strpos($message, 'successfully') !== false ? 'alert-success' : 'alert-danger' ?>"> <?= htmlspecialchars($message) ?> </div>
                        <?php endif; ?>
                        <?php if ($show_otp_form): ?>
                            <form method="POST" novalidate>
                                <div class="mb-3">
                                    <label for="otp" class="form-label">Enter OTP Code</label>
                                    <input type="text" class="form-control text-center" id="otp" name="otp" maxlength="6" pattern="[0-9]{6}" placeholder="000000" required style="font-size: 1.5rem; letter-spacing: 0.5rem; font-weight: bold;">
                                    <small class="form-text text-muted">Enter the 6-digit code sent to your email</small>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">Verify OTP</button>
                            </form>
                            <div class="mt-3 text-center">
                                <small class="text-muted">Didn't receive the code? <a href="signup.php">Sign up again</a></small>
                            </div>
                        <?php endif; ?>
                        <div class="mt-3 text-center">
                            <a href="signup.php" class="btn btn-secondary">Back to Sign Up</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>

document.addEventListener('DOMContentLoaded', function() {
    const otpInput = document.getElementById('otp');
    if (otpInput) {
        otpInput.focus();
        
        otpInput.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
        
        otpInput.addEventListener('input', function(e) {
            if (this.value.length === 6) {
                
                
            }
        });
    }
});
</script>
</body>
</html>