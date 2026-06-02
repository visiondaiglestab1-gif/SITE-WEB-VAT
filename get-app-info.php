<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once '../config/database.php';

try {
    $stmt = $pdo->prepare("SELECT * FROM site_settings");
    $settings = $stmt->fetch();
    $appSettings = $settings['app'] ?? ['version' => '1.0.0', 'apk_path' => '', 'changelog' => '', 'last_update' => null];
    
    echo json_encode([
        'success' => true,
        'version' => $appSettings['version'],
        'apk_path' => $appSettings['apk_path'],
        'changelog' => $appSettings['changelog'],
        'last_update' => $appSettings['last_update']
    ]);
    
} catch(Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>