<?php
// API endpoint for updating booking status (Admin only)
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

require_once __DIR__ . '/auth.php';
requireAuth();

require_once __DIR__ . '/db.php';

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!$data) {
    $data = $_POST;
}

$bookingId = trim($data['booking_id'] ?? $data['id'] ?? '');
$status = trim($data['status'] ?? '');
$notes = isset($data['notes']) ? trim($data['notes']) : null;

if (empty($bookingId) || !in_array($status, ['pending', 'confirmed', 'completed', 'cancelled'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Valid booking_id and status (pending, confirmed, completed, cancelled) required.'
    ]);
    exit;
}

try {
    $pdo = getDBConnection();
    
    if ($notes !== null) {
        $stmt = $pdo->prepare("UPDATE `bookings` SET `status` = :st, `notes` = :n WHERE `booking_id` = :bid OR `id` = :id_num");
        $stmt->execute([
            ':st' => $status,
            ':n' => $notes,
            ':bid' => $bookingId,
            ':id_num' => is_numeric($bookingId) ? (int)$bookingId : 0
        ]);
    } else {
        $stmt = $pdo->prepare("UPDATE `bookings` SET `status` = :st WHERE `booking_id` = :bid OR `id` = :id_num");
        $stmt->execute([
            ':st' => $status,
            ':bid' => $bookingId,
            ':id_num' => is_numeric($bookingId) ? (int)$bookingId : 0
        ]);
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Status updated to ' . $status
    ]);
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
