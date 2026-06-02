<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once '../config/database.php';

try {
    // Récupérer les statistiques
    $stmt = $pdo->prepare("SELECT * FROM stats WHERE id = 1");
    $stats = $stmt->fetch();
    
    // Nombre d'abonnés approuvés (uniquement ceux avec status approved et non bloqués)
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM subscribers WHERE status = 'approved' AND is_blocked = 0");
    $subscribers = $stmt->fetch();
    
    echo json_encode([
        'success' => true,
        'visitors' => $stats['visitors_count'] ?? 0,
        'subscribers' => $subscribers['count'] ?? 0,
        'sermons_mega' => $stats['sermons_count_mega_manual'] ?? 0,
        'sermons_degoo' => $stats['sermons_count_degoo_manual'] ?? 0,
        'total_sermons' => ($stats['sermons_count_mega_manual'] ?? 0) + ($stats['sermons_count_degoo_manual'] ?? 0)
    ]);
    
} catch(Exception $e) {
    echo json_encode([
        'success' => false, 
        'visitors' => 0,
        'subscribers' => 0,
        'sermons_mega' => 0,
        'sermons_degoo' => 0,
        'total_sermons' => 0,
        'message' => $e->getMessage()
    ]);
}
?>