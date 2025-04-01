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

if (empty($data)) {
    http_response_code(400);
    die(json_encode(['error' => 'No data provided']));
}

try {
    $conn->begin_transaction();
    $results = [];
    
    foreach ($data as $action) {
        switch ($action['type']) {
            case 'contact':
                $stmt = $conn->prepare("INSERT INTO emergency_contacts (user_id, contact_name, contact_number) VALUES (?, ?, ?)");
                $stmt->bind_param("iss", $userId, $action['name'], $action['number']);
                $stmt->execute();
                $results[] = ['type' => 'contact', 'id' => $conn->insert_id];
                break;
                
            case 'profile_update':
                $stmt = $conn->prepare("UPDATE users SET medical_info = ?, blood_type = ? WHERE id = ?");
                $stmt->bind_param("ssi", $action['medical_info'], $action['blood_type'], $userId);
                $stmt->execute();
                $results[] = ['type' => 'profile', 'rows' => $stmt->affected_rows];
                break;
                
            case 'incident':
                $stmt = $conn->prepare("INSERT INTO incidents (user_id, latitude, longitude, offline_created) VALUES (?, ?, ?, 1)");
                $stmt->bind_param("idd", $userId, $action['lat'], $action['lng']);
                $stmt->execute();
                $results[] = ['type' => 'incident', 'id' => $conn->insert_id];
                break;
                
            default:
                throw new Exception("Unknown action type: " . $action['type']);
        }
    }
    
    $conn->commit();
    echo json_encode(['success' => true, 'results' => $results]);
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['error' => 'Sync failed: ' . $e->getMessage()]);
}
?>