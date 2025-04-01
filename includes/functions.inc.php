<?php
function sanitizeInput($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['admin_id']);
}

function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

function getLocationData() {
    return [
        'latitude' => $_POST['latitude'] ?? null,
        'longitude' => $_POST['longitude'] ?? null
    ];
}

function sendEmergencyNotification($userId, $location) {
    // In production, this would connect to SMS/email APIs
    error_log("Emergency alert triggered by user $userId at coordinates: " . 
             $location['latitude'] . "," . $location['longitude']);
    return true;
}
?>