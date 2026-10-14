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
require_once __DIR__ . '/smtp_mailer.php';

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
    
    // If status is updated to confirmed or cancelled, notify client if email exists
    if ($status === 'confirmed' || $status === 'cancelled') {
        $getStmt = $pdo->prepare("SELECT * FROM `bookings` WHERE `booking_id` = :bid OR `id` = :id_num LIMIT 1");
        $getStmt->execute([':bid' => $bookingId, ':id_num' => is_numeric($bookingId) ? (int)$bookingId : 0]);
        $booking = $getStmt->fetch();
        
        if ($booking && !empty($booking['owner_email'])) {
            $fromEmail = 'dr.vet@pawhooves.co.ke';
            $clientEmail = $booking['owner_email'];
            $ownerName = htmlspecialchars($booking['owner_name']);
            $petName = htmlspecialchars($booking['pet_name']);
            $date = htmlspecialchars($booking['appointment_date']);
            $time = htmlspecialchars($booking['appointment_time']);
            
            if ($status === 'confirmed') {
                $subject = "✅ Confirmed: Mobile Vet Visit for {$petName} with Dr. Wangari";
                $body = "
                <!DOCTYPE html>
                <html>
                <body style='font-family: Arial, sans-serif; background:#f4fbfb; padding:20px; color:#2d3748;'>
                  <div style='max-width:550px; margin:0 auto; background:white; border-radius:12px; padding:25px; border:1px solid #d1eae6;'>
                    <h2 style='color:#10b981; margin-top:0;'>Appointment Confirmed!</h2>
                    <p>Dear <strong>{$ownerName}</strong>,</p>
                    <p>Your mobile veterinary visit for <strong>{$petName}</strong> on <strong>{$date} at {$time}</strong> has been confirmed by Dr. Wangari.</p>
                    <p style='background:#f0fdf4; padding:12px; border-left:4px solid #10b981;'>
                      🚐 Dr. Wangari will travel directly to your location. For any urgent questions, reach us at <a href='tel:0759396531'>0759396531</a>.
                    </p>
                    <p>Best regards,<br><strong>Dr. Wangari &bull; Paws & Hooves Mobile Vet</strong></p>
                  </div>
                </body>
                </html>";
            } else {
                $subject = "Update on your Paws & Hooves Appointment Request ({$bookingId})";
                $body = "
                <!DOCTYPE html>
                <html>
                <body style='font-family: Arial, sans-serif; padding:20px; color:#333;'>
                  <div style='max-width:550px; margin:0 auto; background:white; padding:25px; border:1px solid #eee; border-radius:10px;'>
                    <h2 style='color:#c53030; margin-top:0;'>Appointment Notice</h2>
                    <p>Dear {$ownerName},</p>
                    <p>Your requested appointment on <strong>{$date} at {$time}</strong> could not be accommodated.</p>
                    <p>Please call or WhatsApp us directly at <a href='tel:0759396531'>0759396531</a> to reschedule.</p>
                    <p>Best regards,<br><strong>Dr. Wangari &bull; Paws & Hooves</strong></p>
                  </div>
                </body>
                </html>";
            }
            
                sendPawsEmail($clientEmail, $subject, $body, "Dr. Wangari - Paws & Hooves", 'careywangaretracy@gmail.com');
        }
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
