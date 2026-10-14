<?php
// API endpoint for fetching bookings (Admin only)
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/auth.php';
requireAuth();

require_once __DIR__ . '/db.php';

try {
    $pdo = getDBConnection();
    
    // Auto-create table if not yet created
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
    
    $status = $_GET['status'] ?? '';
    $search = $_GET['search'] ?? '';
    $date = $_GET['date'] ?? '';
    
    $query = "SELECT * FROM `bookings` WHERE 1=1";
    $params = [];
    
    if (!empty($status) && in_array($status, ['pending', 'confirmed', 'completed', 'cancelled'])) {
        $query .= " AND `status` = :status";
        $params[':status'] = $status;
    }
    
    if (!empty($date)) {
        $query .= " AND `appointment_date` = :date";
        $params[':date'] = $date;
    }
    
    if (!empty($search)) {
        $query .= " AND (`owner_name` LIKE :s OR `owner_phone` LIKE :s OR `pet_name` LIKE :s OR `booking_id` LIKE :s OR `owner_location` LIKE :s)";
        $params[':s'] = "%" . $search . "%";
    }
    
    $query .= " ORDER BY `appointment_date` DESC, `appointment_time` ASC, `created_at` DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $bookings = $stmt->fetchAll();
    
    // Count stats
    $statsStmt = $pdo->query("SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN `status` = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN `status` = 'confirmed' THEN 1 ELSE 0 END) as confirmed,
        SUM(CASE WHEN `status` = 'completed' THEN 1 ELSE 0 END) as completed,
        SUM(CASE WHEN `appointment_date` = CURDATE() THEN 1 ELSE 0 END) as today
        FROM `bookings`");
    $stats = $statsStmt->fetch() ?: [
        'total' => 0, 'pending' => 0, 'confirmed' => 0, 'completed' => 0, 'today' => 0
    ];
    
    echo json_encode([
        'success' => true,
        'bookings' => $bookings,
        'stats' => $stats
    ]);
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
