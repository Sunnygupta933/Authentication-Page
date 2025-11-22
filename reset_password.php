<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header text-center">
                    <h3>Reset Password</h3>
                </div>
                <div class="card-body">
                    <?php
                    require_once 'db.php';
                    require 'phpmailer/src/PHPMailer.php';
                    require 'phpmailer/src/SMTP.php';
                    require 'phpmailer/src/Exception.php';
                    use PHPMailer\PHPMailer\PHPMailer;
                    use PHPMailer\PHPMailer\Exception;
                    $message = '';
                    $show_form = true;
                    if (isset($_GET['code'])) {
                        $code = $_GET['code'];
                        $stmt = $conn->prepare('SELECT id FROM users WHERE reset_code = ?');
                        $stmt->bind_param('s', $code);
                        $stmt->execute();
                        $stmt->store_result();
                        if ($stmt->num_rows === 1) {
                            $stmt->bind_result($id);
                            $stmt->fetch();
                            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                                $password = $_POST['password'] ?? '';
                                $confirm_password = $_POST['confirm_password'] ?? '';
                                if (!$password || !$confirm_password) {
                                    $message = 'All fields are required.';
                                } elseif ($password !== $confirm_password) {
                                    $message = 'Passwords do not match.';
                                } elseif (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
                                    $message = 'Password must be at least 8 characters, include a number and an uppercase letter.';
                                } else {
                                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                                    $update = $conn->prepare('UPDATE users SET password = ?, reset_code = NULL WHERE id = ?');
                                    $update->bind_param('si', $hashed_password, $id);
                                    if ($update->execute()) {
                                        // Get user email and name
                                        $user_stmt = $conn->prepare('SELECT email, full_name FROM users WHERE id = ?');
                                        $user_stmt->bind_param('i', $id);
                                        $user_stmt->execute();
                                        $user_stmt->store_result();
                                        $user_email = '';
                                        $user_name = '';
                                        if ($user_stmt->num_rows === 1) {
                                            $user_stmt->bind_result($user_email, $user_name);
                                            $user_stmt->fetch();
                                        }
                                        $user_stmt->close();

                                        $mail = new PHPMailer(true);
                                        try {
                                            $mail->isSMTP();
                                            $mail->Host = 'smtp.gmail.com';
                                            $mail->SMTPAuth = true;
                                            $mail->Username = 'sunnygupta.coder@gmail.com';
                                            $mail->Password = 'qsdsibaickntlhll';
                                            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                                            $mail->Port = 587;

                                            $mail->setFrom('your_gmail_address@gmail.com', 'Account Security Team');
                                            $mail->addAddress($user_email, $user_name);
                                            $mail->Subject = 'Password Successfully Reset';
                                            $reset_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reset_password.php?code=$code";
                                            $mail->Body = "Dear $user_name,\n\nYour password has been successfully reset. If you did not perform this action, please change your password immediately using the link below:\n$reset_link\n\nIf you recognize this activity, no further action is needed.\n\nBest regards,\nAccount Security Team";

                                            $mail->send();
                                        } catch (Exception $e) {
                                            // Optionally log or handle email error
                                        }
                                        $message = 'Password reset successful! You can now log in.';
                                        $show_form = false;
                                    } else {
                                        $message = 'Failed to reset password.';
                                    }
                                }
                            }
                        } else {
                            $message = 'Invalid or expired reset link.';
                            $show_form = false;
                        }
                    } else {
                        $message = 'No reset code provided.';
                        $show_form = false;
                    }
                    ?>
                    <?php if ($message): ?>
                        <div class="alert alert-info"> <?= htmlspecialchars($message) ?> </div>
                    <?php endif; ?>
                    <?php if ($show_form): ?>
                    <form method="POST" novalidate>
                        <div class="mb-3 position-relative">
                            <label for="password" class="form-label">New Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password" name="password" required>
                                <button type="button" class="btn btn-outline-secondary" tabindex="-1" onclick="togglePassword('password', this)" style="border-left: 0; padding: 0.375rem 0.75rem;">
                                    <i class="bi bi-eye" id="icon-password" style="font-size: 1.1rem;"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mb-3 position-relative">
                            <label for="confirm_password" class="form-label">Confirm Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                <button type="button" class="btn btn-outline-secondary" tabindex="-1" onclick="togglePassword('confirm_password', this)" style="border-left: 0; padding: 0.375rem 0.75rem;">
                                    <i class="bi bi-eye" id="icon-confirm_password" style="font-size: 1.1rem;"></i>
                                </button>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success w-100">Reset Password</button>
                    </form>
                    <?php else: ?>
                        <div class="mt-3 text-center">
                            <a href="login.php" class="btn btn-primary">Back to Login</a>
                        </div>
                    <?php endif; ?>
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
    // Prevent button from taking focus and ensure input stays focused
    setTimeout(function() {
        input.focus();
        input.setSelectionRange(input.value.length, input.value.length);
    }, 0);
}
</script>
</body>
</html>