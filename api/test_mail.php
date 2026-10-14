<?php
// Live Diagnostics: Test cPanel Authenticated SMTP Sending (Port 465 SSL)
header('Content-Type: application/json');

require_once __DIR__ . '/smtp_mailer.php';

$to = 'careywangaretracy@gmail.com';
$subject = "🐾 Paws & Hooves SMTP Direct Test - " . date('Y-m-d H:i:s');
$html = "
<html>
<body style='font-family: Arial, sans-serif; padding: 25px; background: #e6f6f4;'>
  <div style='background: white; border-radius: 12px; padding: 25px; max-width: 550px; margin: 0 auto; border: 2px solid #55a39a;'>
    <h2 style='color: #2d736a; margin-top: 0;'>🎉 Authenticated SMTP is Working!</h2>
    <p>This email was transmitted through <strong>mail.pawhooves.co.ke:465 (SSL)</strong> using the official mailbox <code>dr.vet@pawhooves.co.ke</code>.</p>
    <p>Because it is authenticated with SPF and DKIM through your server, it delivers straight to your Gmail inbox.</p>
    <hr style='border: none; border-top: 1px solid #eee; margin: 20px 0;'>
    <p style='font-size: 13px; color: #777;'>Paws & Hooves Mobile Vet System &bull; Nairobi & Kiambu</p>
  </div>
</body>
</html>
";

$result = sendPawsEmail($to, $subject, $html, "Dr. Wangari - Paws & Hooves", "dr.vet@pawhooves.co.ke");

echo json_encode([
    'timestamp' => date('Y-m-d H:i:s'),
    'recipient' => $to,
    'smtp_server' => 'mail.pawhooves.co.ke:465 (SSL)',
    'result' => $result
], JSON_PRETTY_PRINT);
