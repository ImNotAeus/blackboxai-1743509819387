<?php
require_once '../../includes/functions.inc.php';
require_once '../../db/config.php';

session_start();

if (!isAdmin()) {
    redirect('../pages/admin-login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $incidentId = sanitizeInput($_POST['incident_id']);
    
    $stmt = $conn->prepare("UPDATE incidents SET status = 'resolved' WHERE id = ?");
    $stmt->bind_param("i", $incidentId);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Incident marked as resolved";
    } else {
        $_SESSION['error'] = "Failed to resolve incident";
    }
}

header("Location: incidents.php");
exit();
?>