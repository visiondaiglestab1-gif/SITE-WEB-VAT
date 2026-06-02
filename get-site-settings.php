<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once '../config/database.php';

try {
    $stmt = $pdo->prepare("SELECT * FROM site_settings");
    $settings = $stmt->fetch();
    
    echo json_encode([
        'success' => true,
        'settings' => $settings
    ]);
    
} catch(Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>