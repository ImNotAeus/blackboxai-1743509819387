<?php
header('Content-Type: application/json');
require_once '../includes/functions.inc.php';
require_once '../db/config.php';

session_start();

if (!isLoggedIn()) {
    http_response_code(401);
    die(json_encode(['error' => 'Unauthorized']));
}

$data = json_decode(file_get_contents('php://input'), true);
$userId = $_SESSION['user_id'];
$latitude = $data['latitude'] ?? null;
$longitude = $data['longitude'] ?? null;

if (!$latitude || !$longitude) {
    http_response_code(400);
    die(json_encode(['error' => 'Location data required']));
}

try {
    // Insert incident
    $stmt = $conn->prepare("INSERT INTO incidents (user_id, latitude, longitude) VALUES (?, ?, ?)");
    $stmt->bind_param("idd", $userId, $latitude, $longitude);
    $stmt->execute();
    
    // Get emergency contacts
    $contacts = $conn->query("SELECT contact_number FROM emergency_contacts WHERE user_id = $userId");
    
    // Prepare response
    $response = [
        'success' => true,
        'message' => 'Emergency alert processed',
        'location' => ['latitude' => $latitude, 'longitude' => $longitude],
        'contacts_notified' => $contacts->num_rows
    ];
    
    echo json_encode($response);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to process emergency: ' . $e->getMessage()]);
}
?>