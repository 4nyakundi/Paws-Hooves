<?php
// One-click appointment confirmation & rejection endpoint
// Accessed directly from Dr. Wangari's email notification link

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/smtp_mailer.php';

$action = strtolower(trim($_GET['action'] ?? 'confirm')); // confirm or cancel
$bookingId = trim($_GET['id'] ?? '');
$token = trim($_GET['token'] ?? '');

$expectedToken = substr(hash_hmac('sha256', $bookingId, 'Dr.Vet 0759396531'), 0, 16);

$valid = false;
$booking = null;
$message = '';
$statusChanged = false;

if (!empty($bookingId) && $token === $expectedToken) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT * FROM `bookings` WHERE `booking_id` = :id LIMIT 1");
        $stmt->execute([':id' => $bookingId]);
        $booking = $stmt->fetch();
        
        if ($booking) {
            $valid = true;
            $newStatus = ($action === 'confirm') ? 'confirmed' : 'cancelled';
            
            // Update status in database
            $upd = $pdo->prepare("UPDATE `bookings` SET `status` = :st WHERE `booking_id` = :id");
            $upd->execute([':st' => $newStatus, ':id' => $bookingId]);
            $statusChanged = true;
            
            // Revert / Send confirmation email to client if client email is provided
            if (!empty($booking['owner_email'])) {
                $fromEmail = 'dr.vet@pawhooves.co.ke';
                $clientEmail = $booking['owner_email'];
                $ownerName = htmlspecialchars($booking['owner_name']);
                $petName = htmlspecialchars($booking['pet_name']);
                $service = htmlspecialchars($booking['service_type']);
                $date = htmlspecialchars($booking['appointment_date']);
                $time = htmlspecialchars($booking['appointment_time']);
                $location = htmlspecialchars($booking['owner_location'] ?: 'Your Provided Location');
                
                if ($action === 'confirm') {
                    $subject = "✅ Confirmed: Mobile Vet Visit for {$petName} with Dr. Wangari";
                    $body = "
                    <!DOCTYPE html>
                    <html>
                    <head>
                      <meta charset='utf-8'>
                      <style>
                        body { font-family: 'Segoe UI', Arial, sans-serif; background-color: #f4fbfb; margin: 0; padding: 25px; color: #2d3748; }
                        .card { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.08); border: 1px solid #d1eae6; }
                        .header { background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #ffffff; padding: 35px 25px; text-align: center; }
                        .header h1 { margin: 0; font-size: 26px; font-weight: 700; letter-spacing: -0.5px; }
                        .header p { margin: 8px 0 0; opacity: 0.95; font-size: 16px; }
                        .badge { display: inline-block; background: #d1fae5; color: #065f46; padding: 6px 14px; border-radius: 20px; font-size: 13px; font-weight: bold; margin-top: 15px; }
                        .content { padding: 30px; }
                        .greeting { font-size: 17px; margin-bottom: 20px; }
                        .details-table { width: 100%; border-collapse: collapse; margin: 20px 0; background: #fdfefe; border-radius: 10px; overflow: hidden; border: 1px solid #e2e8f0; }
                        .details-table td { padding: 12px 16px; border-bottom: 1px solid #edf2f7; font-size: 14px; }
                        .details-table tr:last-child td { border-bottom: none; }
                        .details-table td.label { font-weight: bold; color: #4a5568; width: 38%; background: #f8fafc; }
                        .details-table td.val { color: #1a202c; }
                        .highlight-box { background: #f0fdf4; border-left: 4px solid #10b981; padding: 15px; border-radius: 0 8px 8px 0; margin: 20px 0; font-size: 14px; line-height: 1.6; }
                        .btn-wa { display: inline-block; background: #25D366; color: #ffffff !important; text-decoration: none; padding: 12px 24px; border-radius: 8px; font-weight: bold; margin-right: 10px; font-size: 14px; }
                        .btn-call { display: inline-block; background: #55a39a; color: #ffffff !important; text-decoration: none; padding: 12px 24px; border-radius: 8px; font-weight: bold; font-size: 14px; }
                        .footer { background: #f8fafc; padding: 20px; text-align: center; font-size: 12px; color: #718096; border-top: 1px solid #e2e8f0; }
                      </style>
                    </head>
                    <body>
                      <div class='card'>
                        <div class='header'>
                          <h1>Paws & Hooves Mobile Vet</h1>
                          <p>Your Appointment has been Confirmed!</p>
                          <div class='badge'>Status: Confirmed by Dr. Wangari</div>
                        </div>
                        <div class='content'>
                          <div class='greeting'>Dear <strong>{$ownerName}</strong>,</div>
                          <p>Great news! Dr. Wangari has reviewed and <strong>confirmed</strong> your mobile veterinary appointment for <strong>{$petName}</strong>.</p>
                          
                          <table class='details-table'>
                            <tr><td class='label'>Booking ID:</td><td class='val'><strong>{$bookingId}</strong></td></tr>
                            <tr><td class='label'>Patient:</td><td class='val'><strong>{$petName}</strong> ({$booking['pet_type']})</td></tr>
                            <tr><td class='label'>Service:</td><td class='val'>{$service}</td></tr>
                            <tr><td class='label'>Confirmed Date:</td><td class='val'><strong>{$date}</strong></td></tr>
                            <tr><td class='label'>Confirmed Time:</td><td class='val'><strong>{$time}</strong></td></tr>
                            <tr><td class='label'>Visit Location:</td><td class='val'>{$location}</td></tr>
                          </table>
                          
                          <div class='highlight-box'>
                            🚐 <strong>What to Expect on Visit Day:</strong><br>
                            &bull; Dr. Wangari will travel directly to your location with diagnostic supplies and mobile equipment.<br>
                            &bull; Please keep your animal accessible and calm prior to arrival.<br>
                            &bull; If your location or schedule changes, please contact us immediately.
                          </div>
                          
                          <p style='margin-top: 25px;'>
                            <a href='https://wa.me/254759396531?text=" . urlencode("Hi Dr. Wangari, regarding confirmed appointment {$bookingId} for {$petName}") . "' class='btn-wa'>💬 Chat on WhatsApp</a>
                            <a href='tel:0759396531' class='btn-call'>📞 Call Dr. Wangari (0759396531)</a>
                          </p>
                        </div>
                        <div class='footer'>
                          &copy; 2026 Paws & Hooves by Wangari &bull; 100% Mobile Veterinary Care across Nairobi & Kiambu<br>
                          Emergency / Inquiries: 0759396531 &bull; careywangaretracy@gmail.com
                        </div>
                      </div>
                    </body>
                    </html>
                    ";
                } else {
                    $subject = "Update on your Paws & Hooves Appointment Request ({$bookingId})";
                    $body = "
                    <!DOCTYPE html>
                    <html>
                    <body style='font-family: Arial, sans-serif; padding: 20px; color: #333;'>
                      <div style='max-width: 550px; margin: 0 auto; background: white; border: 1px solid #eee; border-radius: 10px; padding: 25px;'>
                        <h2 style='color: #c53030;'>Appointment Update</h2>
                        <p>Dear {$ownerName},</p>
                        <p>Regarding your requested appointment on <strong>{$date} at {$time}</strong> for <strong>{$petName}</strong>: this time slot cannot be accommodated.</p>
                        <p>Please call or WhatsApp Dr. Wangari directly at <a href='tel:0759396531'>0759396531</a> to reschedule a convenient time for your mobile visit.</p>
                        <p>Thank you,<br><strong>Dr. Wangari &bull; Paws & Hooves Mobile Vet</strong></p>
                      </div>
                    </body>
                    </html>
                    ";
                }
                
                sendPawsEmail($clientEmail, $subject, $body, "Dr. Wangari - Paws & Hooves", 'careywangaretracy@gmail.com');
            }
        }
    } catch (\Exception $e) {
        $message = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paws & Hooves | Booking Confirmation</title>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Quicksand', sans-serif;
            background: linear-gradient(135deg, #e6f6f4 0%, #fffdf9 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            margin: 0;
        }
        .box {
            background: white;
            max-width: 520px;
            width: 100%;
            padding: 40px 30px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.08);
            text-align: center;
            border: 1px solid #d4eae6;
        }
        .icon {
            font-size: 50px;
            margin-bottom: 15px;
            display: inline-block;
        }
        h1 {
            color: #2d736a;
            font-size: 24px;
            margin-bottom: 10px;
        }
        p {
            color: #4a5568;
            font-size: 15px;
            line-height: 1.6;
        }
        .info-pill {
            background: #f0fdf4;
            color: #166534;
            padding: 10px 16px;
            border-radius: 10px;
            font-weight: bold;
            font-size: 14px;
            margin: 20px 0;
            display: inline-block;
        }
        .btn {
            display: inline-block;
            background: #55a39a;
            color: white;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 700;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="box">
        <?php if ($valid && $statusChanged): ?>
            <div class="icon"><?= $action === 'confirm' ? '✅' : '❌' ?></div>
            <h1>Appointment <?= $action === 'confirm' ? 'Confirmed!' : 'Cancelled' ?></h1>
            <div class="info-pill">Booking ID: <?= htmlspecialchars($bookingId) ?></div>
            <p>
                The appointment for <strong><?= htmlspecialchars($booking['pet_name']) ?></strong> on 
                <strong><?= htmlspecialchars($booking['appointment_date']) ?> at <?= htmlspecialchars($booking['appointment_time']) ?></strong> 
                has been marked as <strong><?= ucfirst($action === 'confirm' ? 'confirmed' : 'cancelled') ?></strong> in the system.
            </p>
            <?php if (!empty($booking['owner_email'])): ?>
                <p style="color:#047857; font-weight:600;">
                    ✉️ An automated confirmation email has also been dispatched from <strong>dr.vet@pawhooves.co.ke</strong> to the client (<?= htmlspecialchars($booking['owner_email']) ?>).
                </p>
            <?php else: ?>
                <p style="color:#b45309;">
                    ℹ️ The client did not provide an email address, but you can reach them via phone/WhatsApp at <strong><?= htmlspecialchars($booking['owner_phone']) ?></strong>.
                </p>
            <?php endif; ?>
            <a href="../admin/dashboard.html" class="btn">Open Staff Dashboard</a>
        <?php else: ?>
            <div class="icon">⚠️</div>
            <h1>Invalid or Expired Link</h1>
            <p>This confirmation link could not be verified or has already expired.</p>
            <a href="../admin/dashboard.html" class="btn">Go to Dashboard</a>
        <?php endif; ?>
    </div>
</body>
</html>
