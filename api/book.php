<?php
// API endpoint for receiving and storing new bookings,
// sending internal stylized emails to BOTH client and Dr. Wangari,
// and providing 1-click confirmation links for Dr. Wangari.

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

require_once __DIR__ . '/db.php';

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!$data) {
    $data = $_POST;
}

$bookingId = trim($data['bookingId'] ?? $data['booking_id'] ?? ('PAH-' . time() . '-' . rand(100, 999)));
$ownerName = trim($data['ownerName'] ?? $data['owner_name'] ?? '');
$ownerPhone = trim($data['ownerPhone'] ?? $data['owner_phone'] ?? '');
$ownerEmail = trim($data['ownerEmail'] ?? $data['owner_email'] ?? '');
$ownerLocation = trim($data['ownerLocation'] ?? $data['owner_location'] ?? $data['location'] ?? 'Nairobi & Kiambu (Mobile Visit)');
$petName = trim($data['petName'] ?? $data['pet_name'] ?? '');
$petType = trim($data['petType'] ?? $data['pet_type'] ?? '');
$serviceType = trim($data['appointmentType'] ?? $data['service_type'] ?? '');
$appointmentDate = trim($data['appointmentDate'] ?? $data['appointment_date'] ?? '');
$appointmentTime = trim($data['appointmentTime'] ?? $data['appointment_time'] ?? '');
$notes = trim($data['appointmentNotes'] ?? $data['notes'] ?? '');

if (empty($ownerName) || empty($ownerPhone) || empty($petName) || empty($appointmentDate) || empty($appointmentTime)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Missing required booking fields (Name, Phone, Pet Name, Date, Time).'
    ]);
    exit;
}

// 1. Insert into MySQL Database
$savedToDb = false;

try {
    $pdo = getDBConnection();
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS `bookings` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `booking_id` VARCHAR(64) NOT NULL UNIQUE,
      `owner_name` VARCHAR(150) NOT NULL,
      `owner_phone` VARCHAR(50) NOT NULL,
      `owner_email` VARCHAR(150) DEFAULT NULL,
      `owner_location` VARCHAR(255) DEFAULT NULL,
      `pet_name` VARCHAR(100) NOT NULL,
      `pet_type` VARCHAR(50) NOT NULL,
      `service_type` VARCHAR(100) NOT NULL,
      `appointment_date` DATE NOT NULL,
      `appointment_time` VARCHAR(20) NOT NULL,
      `notes` TEXT DEFAULT NULL,
      `status` ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending',
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      INDEX (`appointment_date`),
      INDEX (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    
    $sql = "INSERT INTO `bookings` 
            (`booking_id`, `owner_name`, `owner_phone`, `owner_email`, `owner_location`, `pet_name`, `pet_type`, `service_type`, `appointment_date`, `appointment_time`, `notes`, `status`)
            VALUES (:bid, :oname, :ophone, :oemail, :oloc, :pname, :ptype, :stype, :adate, :atime, :notes, 'pending')
            ON DUPLICATE KEY UPDATE 
            `owner_name` = VALUES(`owner_name`),
            `owner_phone` = VALUES(`owner_phone`),
            `notes` = VALUES(`notes`)";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':bid' => $bookingId,
        ':oname' => $ownerName,
        ':ophone' => $ownerPhone,
        ':oemail' => $ownerEmail ?: null,
        ':oloc' => $ownerLocation,
        ':pname' => $petName,
        ':ptype' => $petType,
        ':stype' => $serviceType,
        ':adate' => $appointmentDate,
        ':atime' => $appointmentTime,
        ':notes' => $notes ?: null
    ]);
    
    $savedToDb = true;
} catch (\Exception $e) {
    error_log("DB Insert Error: " . $e->getMessage());
}

// 2. Generate Secret Token & One-Click URLs for Dr. Wangari
$token = substr(hash_hmac('sha256', $bookingId, 'Dr.Vet 0759396531'), 0, 16);

// Base URL for host
$host = $_SERVER['HTTP_HOST'] ?? 'pawhooves.co.ke';
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "https://";
$baseUrl = $protocol . $host;
$confirmUrl = "{$baseUrl}/api/confirm.php?action=confirm&id=" . urlencode($bookingId) . "&token={$token}";
$cancelUrl = "{$baseUrl}/api/confirm.php?action=cancel&id=" . urlencode($bookingId) . "&token={$token}";

$fromEmail = 'dr.vet@pawhooves.co.ke';
$doctorEmail = 'careywangaretracy@gmail.com';

// -------------------------------------------------------------
// 3. EMAIL 1: Styled Email to Dr. Wangari (careywangaretracy@gmail.com)
// Includes the One-Click "Confirm Booking" button to revert to client!
// -------------------------------------------------------------
$doctorSubject = "🐾 New Mobile Vet Booking: {$petName} ({$ownerName}) - Action Required";

$doctorEmailHtml = "
<!DOCTYPE html>
<html>
<head>
  <meta charset='utf-8'>
  <style>
    body { font-family: 'Segoe UI', Arial, sans-serif; background: #f0f7f6; margin: 0; padding: 20px; color: #2d3748; }
    .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.08); border: 1px solid #d4eae6; }
    .header { background: linear-gradient(135deg, #55a39a 0%, #2f6a62 100%); color: white; padding: 30px 25px; text-align: center; }
    .header h1 { margin: 0; font-size: 24px; font-weight: 700; }
    .header p { margin: 8px 0 0; opacity: 0.9; font-size: 15px; }
    .content { padding: 30px 25px; }
    .badge { display: inline-block; background: #e6f6f4; color: #23655d; font-weight: bold; padding: 6px 14px; border-radius: 20px; font-size: 13px; margin-bottom: 18px; }
    table { width: 100%; border-collapse: collapse; margin: 15px 0 25px; background: #fafdfc; border-radius: 10px; overflow: hidden; border: 1px solid #eef4f3; }
    td { padding: 12px 14px; border-bottom: 1px solid #edf2f7; font-size: 14px; }
    td.label { font-weight: bold; color: #4a5568; width: 38%; background: #f4f9f8; }
    td.value { color: #1a202c; }
    .action-box { background: #fdfaf0; border: 2px dashed #f59e0b; border-radius: 12px; padding: 20px; text-align: center; margin: 25px 0; }
    .action-title { font-size: 16px; font-weight: 700; color: #92400e; margin-bottom: 12px; }
    .btn-confirm { display: inline-block; background: #10b981; color: #ffffff !important; text-decoration: none; padding: 14px 28px; border-radius: 8px; font-weight: bold; font-size: 15px; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3); margin: 5px; }
    .btn-cancel { display: inline-block; background: #ef4444; color: #ffffff !important; text-decoration: none; padding: 14px 22px; border-radius: 8px; font-weight: bold; font-size: 14px; margin: 5px; }
    .btn-wa { display: inline-block; background: #25D366; color: #ffffff !important; text-decoration: none; padding: 12px 20px; border-radius: 8px; font-weight: bold; font-size: 14px; margin: 5px; }
    .btn-call { display: inline-block; background: #55a39a; color: #ffffff !important; text-decoration: none; padding: 12px 20px; border-radius: 8px; font-weight: bold; font-size: 14px; margin: 5px; }
    .footer { background: #f8fafc; padding: 18px; text-align: center; font-size: 12px; color: #718096; border-top: 1px solid #e2e8f0; }
  </style>
</head>
<body>
  <div class='container'>
    <div class='header'>
      <h1>Paws & Hooves Mobile Vet</h1>
      <p>New Consultation Booking Received</p>
    </div>
    <div class='content'>
      <div class='badge'>Booking ID: {$bookingId}</div>
      <p style='margin-top:0;'>A new client has scheduled a mobile veterinary appointment on the website:</p>
      
      <table>
        <tr><td class='label'>Client Name:</td><td class='value'><strong>" . htmlspecialchars($ownerName) . "</strong></td></tr>
        <tr><td class='label'>Client Phone:</td><td class='value'><a href='tel:{$ownerPhone}' style='color:#55a39a; font-weight:bold;'>" . htmlspecialchars($ownerPhone) . "</a></td></tr>
        <tr><td class='label'>Client Email:</td><td class='value'>" . htmlspecialchars($ownerEmail ?: 'Not provided') . "</td></tr>
        <tr><td class='label'>Client Location:</td><td class='value'><strong>" . htmlspecialchars($ownerLocation) . "</strong></td></tr>
        <tr><td class='label'>Pet / Animal:</td><td class='value'><strong>" . htmlspecialchars($petName) . "</strong> (" . htmlspecialchars($petType) . ")</td></tr>
        <tr><td class='label'>Requested Service:</td><td class='value'>" . htmlspecialchars($serviceType) . "</td></tr>
        <tr><td class='label'>Requested Date:</td><td class='value'><strong>" . htmlspecialchars($appointmentDate) . "</strong></td></tr>
        <tr><td class='label'>Requested Time:</td><td class='value'><strong>" . htmlspecialchars($appointmentTime) . "</strong></td></tr>
        <tr><td class='label'>Notes:</td><td class='value'>" . nl2br(htmlspecialchars($notes ?: 'No special notes')) . "</td></tr>
      </table>

      <!-- ONE-CLICK CONFIRMATION ACTION BOX -->
      <div class='action-box'>
        <div class='action-title'>⚡ Quick Action: Confirm & Revert to Client</div>
        <p style='margin: 0 0 15px; font-size: 13px; color: #78350f;'>
          Clicking <strong>'Confirm Booking'</strong> will automatically update the system and dispatch a confirmation email to the client:
        </p>
        <a href='{$confirmUrl}' class='btn-confirm'>✅ Confirm Booking & Email Client</a>
        <a href='{$cancelUrl}' class='btn-cancel'>❌ Decline</a>
      </div>

      <div style='text-align: center; margin-top: 20px;'>
        <a href='https://wa.me/" . preg_replace('/[^0-9]/', '', $ownerPhone) . "' class='btn-wa'>💬 WhatsApp Client</a>
        <a href='tel:{$ownerPhone}' class='btn-call'>📞 Call Client</a>
      </div>
    </div>
    <div class='footer'>
      Sent via Paws & Hooves Mobile Vet System &bull; <a href='{$baseUrl}/admin/dashboard.html' style='color:#55a39a; font-weight:bold;'>Open Staff Dashboard</a>
    </div>
  </div>
</body>
</html>
";

$docHeaders = [
    'MIME-Version: 1.0',
    'Content-type: text/html; charset=utf-8',
    'From: Paws & Hooves System <' . $fromEmail . '>',
    'Reply-To: ' . ($ownerEmail ?: $fromEmail),
    'X-Mailer: PHP/' . phpversion()
];

$docMailSent = @mail($doctorEmail, $doctorSubject, $doctorEmailHtml, implode("\r\n", $docHeaders));

// -------------------------------------------------------------
// 4. EMAIL 2: Stylized Immediate Booking Acknowledgment to CLIENT
// Sent internally from dr.vet@pawhooves.co.ke to client's keyed email
// -------------------------------------------------------------
$clientMailSent = false;

if (!empty($ownerEmail)) {
    $clientSubject = "🐾 Appointment Received: Dr. Wangari Mobile Vet ({$bookingId})";
    
    $clientEmailHtml = "
    <!DOCTYPE html>
    <html>
    <head>
      <meta charset='utf-8'>
      <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f0f7f6; margin: 0; padding: 20px; color: #2d3748; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.08); border: 1px solid #d4eae6; }
        .header { background: linear-gradient(135deg, #55a39a 0%, #3d7e76 100%); color: white; padding: 32px 25px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; font-weight: 700; }
        .header p { margin: 8px 0 0; opacity: 0.95; font-size: 15px; }
        .content { padding: 30px 25px; }
        .badge { display: inline-block; background: #fef3c7; color: #92400e; font-weight: bold; padding: 6px 14px; border-radius: 20px; font-size: 13px; margin-bottom: 18px; }
        .greeting { font-size: 16px; margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0 25px; background: #fafdfc; border-radius: 10px; overflow: hidden; border: 1px solid #eef4f3; }
        td { padding: 12px 14px; border-bottom: 1px solid #edf2f7; font-size: 14px; }
        td.label { font-weight: bold; color: #4a5568; width: 38%; background: #f4f9f8; }
        td.value { color: #1a202c; }
        .notice-box { background: #e6f6f4; border-left: 4px solid #55a39a; padding: 16px; border-radius: 0 10px 10px 0; margin: 20px 0; font-size: 14px; line-height: 1.6; color: #23655d; }
        .btn-call { display: inline-block; background: #25D366; color: #ffffff !important; text-decoration: none; padding: 12px 22px; border-radius: 8px; font-weight: bold; font-size: 14px; margin-right: 10px; }
        .footer { background: #f8fafc; padding: 18px; text-align: center; font-size: 12px; color: #718096; border-top: 1px solid #e2e8f0; }
      </style>
    </head>
    <body>
      <div class='container'>
        <div class='header'>
          <h1>Paws & Hooves Mobile Vet</h1>
          <p>We Have Received Your Appointment Request</p>
        </div>
        <div class='content'>
          <div class='badge'>Status: Received & Pending Doctor Review</div>
          <div class='greeting'>Hello <strong>" . htmlspecialchars($ownerName) . "</strong>,</div>
          <p>Thank you for booking with <strong>Paws & Hooves Mobile Veterinary Services</strong>. Dr. Wangari has received your request for <strong>" . htmlspecialchars($petName) . "</strong> and will confirm your time slot shortly.</p>
          
          <table>
            <tr><td class='label'>Booking Reference:</td><td class='value'><strong>{$bookingId}</strong></td></tr>
            <tr><td class='label'>Pet / Livestock:</td><td class='value'><strong>" . htmlspecialchars($petName) . "</strong> (" . htmlspecialchars($petType) . ")</td></tr>
            <tr><td class='label'>Service:</td><td class='value'>" . htmlspecialchars($serviceType) . "</td></tr>
            <tr><td class='label'>Requested Date:</td><td class='value'><strong>" . htmlspecialchars($appointmentDate) . "</strong></td></tr>
            <tr><td class='label'>Requested Time:</td><td class='value'><strong>" . htmlspecialchars($appointmentTime) . "</strong></td></tr>
            <tr><td class='label'>Your Location:</td><td class='value'>" . htmlspecialchars($ownerLocation) . "</td></tr>
          </table>

          <div class='notice-box'>
            🚐 <strong>100% Mobile Service Notice:</strong><br>
            Dr. Wangari travels directly to your home or farm across Nairobi & Kiambu. If this is an urgent emergency or sudden distress, please <strong>call us immediately</strong> rather than waiting:
          </div>

          <div style='text-align: center; margin-top: 25px;'>
            <a href='tel:0759396531' class='btn-call'>📞 Call Dr. Wangari: 0759396531</a>
            <a href='https://wa.me/254759396531?text=" . urlencode("Hi Dr. Wangari, I submitted booking {$bookingId} for {$petName}") . "' style='display:inline-block; background:#55a39a; color:white; text-decoration:none; padding:12px 22px; border-radius:8px; font-weight:bold; font-size:14px;'>💬 WhatsApp</a>
          </div>
        </div>
        <div class='footer'>
          &copy; 2026 Paws & Hooves by Wangari &bull; Professional Mobile Veterinary Care across Nairobi & Kiambu<br>
          Sent from <strong>dr.vet@pawhooves.co.ke</strong> &bull; Direct inquiries: careywangaretracy@gmail.com
        </div>
      </div>
    </body>
    </html>
    ";

    $clientHeaders = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=utf-8',
        'From: Dr. Wangari - Paws & Hooves <' . $fromEmail . '>',
        'Reply-To: ' . $doctorEmail,
        'X-Mailer: PHP/' . phpversion()
    ];

    $clientMailSent = @mail($ownerEmail, $clientSubject, $clientEmailHtml, implode("\r\n", $clientHeaders));
}

// 5. WhatsApp Direct URL Construction for Dr. Wangari (+254759396531)
$waDoctorNumber = '254759396531';
$waText = "🐾 *New Mobile Vet Booking*\n"
        . "*ID:* {$bookingId}\n"
        . "*Client:* {$ownerName} ({$ownerPhone})\n"
        . "*Location:* {$ownerLocation}\n"
        . "*Pet:* {$petName} ({$petType})\n"
        . "*Service:* {$serviceType}\n"
        . "*Date:* {$appointmentDate} at {$appointmentTime}\n"
        . "*Confirm Link:* {$confirmUrl}";

$waDoctorUrl = "https://wa.me/{$waDoctorNumber}?text=" . urlencode($waText);

echo json_encode([
    'success' => true,
    'booking_id' => $bookingId,
    'saved_to_db' => $savedToDb,
    'doctor_email_sent' => $docMailSent,
    'client_email_sent' => $clientMailSent,
    'confirm_url' => $confirmUrl,
    'whatsapp_url' => $waDoctorUrl,
    'message' => 'Booking successfully recorded and sent to both client and Dr. Wangari.'
]);
