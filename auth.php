<?php
// auth.php - Authentication helper functions
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db.php';

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function login($email, $password) {
    global $conn;
    $stmt = $conn->prepare('SELECT id, password, email_verified FROM users WHERE email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows === 1) {
        $stmt->bind_result($id, $hashed_password, $email_verified);
        $stmt->fetch();
        if (!$email_verified) {
            return ['success' => false, 'error' => 'Please verify your email before logging in.'];
        }
        if ($hashed_password !== null && password_verify($password, $hashed_password)) {
            session_regenerate_id(true); // Session security
            $_SESSION['user_id'] = $id;
            return ['success' => true];
        } else {
            return ['success' => false, 'error' => 'Incorrect password.'];
        }
    } else {
        return ['success' => false, 'error' => 'Email not found.'];
    }
}

function logout() {
    session_unset();
    session_destroy();
}

function get_user() {
    global $conn;
    if (!is_logged_in()) return null;
    $stmt = $conn->prepare('SELECT id, full_name, email FROM users WHERE id = ?');
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}
?>