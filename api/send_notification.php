<?php
header('Content-Type: application/json');
require_once '../includes/functions.inc.php';
require_once '../db/config.php';

session_start();

if (!isLoggedIn() && !isAdmin()) {
    http_response_code(401);
    die(json_encode(['error' => 'Unauthorized']));
}

$data = json_decode(file_get_contents('php://input'), true);
$userId = $_SESSION['user_id'] ?? null;
$message = $data['message'] ?? null;
$recipients = $data['recipients'] ?? [];

if (!$message || empty($recipients)) {
    http_response_code(400);
    die(json_encode(['error' => 'Message and recipients required']));
}

try {
    // Log notification (in production, would send actual SMS/email)
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, recipients) VALUES (?, ?, ?)");
    $recipientsStr = implode(',', $recipients);
    $stmt->bind_param("iss", $userId, $message, $recipientsStr);
    $stmt->execute();

    $response = [
        'success' => true,
        'message' => 'Notifications processed',
        'recipients_count' => count($recipients),
        'notification_id' => $conn->insert_id
    ];
    
    echo json_encode($response);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Notification failed: ' . $e->getMessage()]);
}
?>