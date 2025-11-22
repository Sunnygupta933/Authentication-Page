<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header text-center">
                    <h3>Forgot Password</h3>
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
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        $email = trim($_POST['email'] ?? '');
                        if (!$email) {
                            $message = 'Please enter your email address.';
                        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            $message = 'Invalid email address.';
                        } else {
                            $stmt = $conn->prepare('SELECT id, full_name FROM users WHERE email = ?');
                            $stmt->bind_param('s', $email);
                            $stmt->execute();
                            $stmt->store_result();
                            if ($stmt->num_rows === 1) {
                                $stmt->bind_result($id, $full_name);
                                $stmt->fetch();
                                $reset_code = bin2hex(random_bytes(16));
                                $update = $conn->prepare('UPDATE users SET reset_code = ? WHERE id = ?');
                                $update->bind_param('si', $reset_code, $id);
                                $update->execute();
                                $reset_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reset_password.php?code=$reset_code";
                                
                                require_once __DIR__ . '/vendor/autoload.php';
                                $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
                                $dotenv->load();

                                $mail = new PHPMailer(true);
                                try {
                                    
                                    $mail->isSMTP();
                                    $mail->Host = 'smtp.gmail.com';
                                    $mail->Username = $_ENV['SMTP_EMAIL'];
                                    $mail->Password = $_ENV['SMTP_PASSWORD'];
                                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                                    $mail->Port = 587;

                                    $mail->setFrom($_ENV['SMTP_EMAIL'], 'Reset Password');
                                    $mail->addAddress($email, $full_name);
                                    $mail->Subject = 'Your Password Reset Instructions';
                                    $mail->Body = "Dear $full_name,\n\nWe received a request to reset your password for your account.\n\nTo proceed, please click the secure link below:\n$reset_link\n\nIf you did not request this change, please ignore this email or contact support.\n\nBest regards,\nAuthentication Team";

                                    $mail->send();
                                    $message = 'Reset Password Email Sent! Please check your inbox.';
                                } catch (Exception $e) {
                                    $message = 'Email could not be sent. Mailer Error: ' . $mail->ErrorInfo;
                                }
                            } else {
                                $message = 'Email not found.';
                            }
                        }
                    }
                    ?>
                    <?php if ($message): ?>
                        <div class="alert alert-info"> <?= htmlspecialchars($message) ?> </div>
                    <?php endif; ?>
                    <form method="POST" novalidate>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email address</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">Send Reset Link</button>
                    </form>
                    <div class="mt-3 text-center">
                        <a href="login.php">Back to Login</a>
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
    if (input.type === 'password') {
        input.type = 'text';
        btn.textContent = 'Hide';
    } else {
        input.type = 'password';
        btn.textContent = 'Show';
    }
}
</script>
</body>
</html>
