<?php
// API endpoint for receiving and storing new bookings,
// then forwarding emails (from dr.vet@pawhooves.co.ke to careywangaretracy@gmail.com)
// and notifying WhatsApp.

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

// 1. Insert into Database
$savedToDb = false;
$insertedId = null;

try {
    $pdo = getDBConnection();
    
    // Auto-create table if not exists for resilience
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
    $insertedId = $pdo->lastInsertId();
} catch (\Exception $e) {
    error_log("DB Insert Error: " . $e->getMessage());
}

// 2. Forward Booking Email from dr.vet@pawhooves.co.ke to careywangaretracy@gmail.com
$doctorEmail = 'careywangaretracy@gmail.com';
$fromEmail = 'dr.vet@pawhooves.co.ke';
$emailSubject = "🐾 New Mobile Vet Booking: {$petName} - {$ownerName} ({$appointmentDate} @ {$appointmentTime})";

$emailBody = "
<html>
<head>
  <style>
    body { font-family: 'Segoe UI', Arial, sans-serif; line-height: 1.6; color: #333; background: #f9fbfb; margin: 0; padding: 20px; }
    .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; border: 1px solid #e1eeec; box-shadow: 0 4px 12px rgba(0,0,0,0.06); }
    .header { background: linear-gradient(135deg, #55a39a, #3d7e76); color: white; padding: 25px 20px; text-align: center; }
    .header h1 { margin: 0; font-size: 24px; }
    .header p { margin: 5px 0 0 0; opacity: 0.9; }
    .content { padding: 25px 30px; }
    .badge { display: inline-block; background: #e6f6f4; color: #2d736a; font-weight: bold; padding: 6px 14px; border-radius: 20px; font-size: 13px; margin-bottom: 15px; }
    table { width: 100%; border-collapse: collapse; margin-top: 15px; }
    td { padding: 10px 12px; border-bottom: 1px solid #f0f0f0; }
    td.label { font-weight: bold; color: #555; width: 38%; }
    td.value { color: #111; }
    .btn { display: inline-block; background: #25D366; color: white !important; text-decoration: none; padding: 12px 20px; border-radius: 8px; font-weight: bold; margin-top: 20px; }
    .footer { background: #f5f5f5; padding: 15px; text-align: center; font-size: 12px; color: #777; }
  </style>
</head>
<body>
  <div class='container'>
    <div class='header'>
      <h1>Paws & Hooves Mobile Vet</h1>
      <p>New Appointment Request</p>
    </div>
    <div class='content'>
      <div class='badge'>Booking ID: {$bookingId}</div>
      <p>A new mobile veterinary consultation has been scheduled:</p>
      <table>
        <tr><td class='label'>Client Name:</td><td class='value'><strong>" . htmlspecialchars($ownerName) . "</strong></td></tr>
        <tr><td class='label'>Client Phone:</td><td class='value'><a href='tel:{$ownerPhone}'>" . htmlspecialchars($ownerPhone) . "</a></td></tr>
        <tr><td class='label'>Client Email:</td><td class='value'>" . htmlspecialchars($ownerEmail ?: 'Not provided') . "</td></tr>
        <tr><td class='label'>Client Location:</td><td class='value'>" . htmlspecialchars($ownerLocation) . "</td></tr>
        <tr><td class='label'>Pet / Animal:</td><td class='value'><strong>" . htmlspecialchars($petName) . "</strong> (" . htmlspecialchars($petType) . ")</td></tr>
        <tr><td class='label'>Service Type:</td><td class='value'>" . htmlspecialchars($serviceType) . "</td></tr>
        <tr><td class='label'>Requested Date:</td><td class='value'><strong>" . htmlspecialchars($appointmentDate) . "</strong></td></tr>
        <tr><td class='label'>Requested Time:</td><td class='value'><strong>" . htmlspecialchars($appointmentTime) . "</strong></td></tr>
        <tr><td class='label'>Notes / Condition:</td><td class='value'>" . nl2br(htmlspecialchars($notes ?: 'No special notes')) . "</td></tr>
      </table>
      
      <p style='margin-top: 25px;'>
        <a href='https://wa.me/" . preg_replace('/[^0-9]/', '', $ownerPhone) . "' class='btn'>💬 WhatsApp Client</a>
        <a href='tel:{$ownerPhone}' style='display:inline-block; background:#55a39a; color:white; text-decoration:none; padding:12px 20px; border-radius:8px; font-weight:bold; margin-left:10px;'>📞 Call Client</a>
      </p>
    </div>
    <div class='footer'>
      Sent via Paws & Hooves Mobile Vet Booking System &bull; <a href='https://pawhooves.co.ke/admin/dashboard.html'>Open Admin Dashboard</a>
    </div>
  </div>
</body>
</html>
";

$headers = [
    'MIME-Version: 1.0',
    'Content-type: text/html; charset=utf-8',
    'From: Paws & Hooves Booking <' . $fromEmail . '>',
    'Reply-To: ' . ($ownerEmail ?: $fromEmail),
    'X-Mailer: PHP/' . phpversion()
];

$mailSent = @mail($doctorEmail, $emailSubject, $emailBody, implode("\r\n", $headers));

// 3. WhatsApp Direct URL Construction for Dr. Wangari (+254759396531)
$waDoctorNumber = '254759396531';
$waText = "🐾 *New Mobile Vet Booking*\n"
        . "*ID:* {$bookingId}\n"
        . "*Client:* {$ownerName} ({$ownerPhone})\n"
        . "*Location:* {$ownerLocation}\n"
        . "*Pet:* {$petName} ({$petType})\n"
        . "*Service:* {$serviceType}\n"
        . "*Date:* {$appointmentDate} at {$appointmentTime}\n"
        . "*Notes:* " . ($notes ?: 'None');

$waDoctorUrl = "https://wa.me/{$waDoctorNumber}?text=" . urlencode($waText);

// Also try notifying the node backend server if online
try {
    $ch = curl_init('https://paws-hooves.onrender.com/book');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
    curl_exec($ch);
    curl_close($ch);
} catch (\Exception $e) {}

echo json_encode([
    'success' => true,
    'booking_id' => $bookingId,
    'saved_to_db' => $savedToDb,
    'email_forwarded' => $mailSent,
    'whatsapp_url' => $waDoctorUrl,
    'message' => 'Booking successfully recorded and forwarded to Dr. Wangari.'
]);
