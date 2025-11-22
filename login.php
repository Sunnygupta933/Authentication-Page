<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header text-center">
                    <h3>Login</h3>
                </div>
                <div class="card-body">
                    <?php
                    session_start();
                    require_once 'auth.php';
                    $error = '';
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        $email = $_POST['email'] ?? '';
                        $password = $_POST['password'] ?? '';
                        
                        $ip = $_SERVER['REMOTE_ADDR'];
                        $lockFile = __DIR__ . "/login_lock_" . md5($ip);
                        
                        if (file_exists($lockFile)) {
                            unlink($lockFile); 
                        }
                        
                        $result = login($email, $password);
                        if ($result['success']) {
                            header('Location: dashboard.php');
                            exit;
                        } else {
                            $error = $result['error'];
                        }
                    }
                    ?>
                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <?= htmlspecialchars($error) ?>
                            <?php if (strpos($error, 'verify your email') !== false): ?>
                                <div class="mt-2">
                                    <a href="verify_email.php" class="btn btn-outline-primary btn-sm">Verify Email with OTP</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    <form method="POST" novalidate>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email address</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3 position-relative">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password" name="password" required>
                                <button type="button" class="btn btn-outline-secondary" tabindex="-1" onclick="togglePassword('password', this)" style="border-left: 0; padding: 0.375rem 0.75rem;">
                                    <i class="bi bi-eye" id="icon-password" style="font-size: 1.1rem;"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember">Remember me</label>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Login</button>
                    </form>
                    <div class="mt-3 d-flex justify-content-between">
                        <a href="forgot_password.php">Forgot Password?</a>
                        <a href="signup.php">Create Account / Sign Up</a>
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