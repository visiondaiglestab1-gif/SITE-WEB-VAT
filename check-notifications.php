<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once '../config/database.php';

try {
    $stmt = $pdo->prepare("SELECT * FROM announcements ORDER BY created_at DESC");
    $announcements = $stmt->fetchAll();
    
    // S'assurer qu'il y a au moins une annonce par défaut
    if(empty($announcements)) {
        $announcements = [
            [
                'id' => 1,
                'title' => 'Bienvenue sur notre site !',
                'content' => 'Nous sommes ravis de vous accueillir sur le site de Vision d\'Aigles Tabernacle. Restez connectés pour les dernières actualités.',
                'author' => 'Admin',
                'created_at' => date('Y-m-d H:i:s')
            ]
        ];
    }
    
    echo json_encode([
        'success' => true,
        'announcements' => $announcements
    ]);
    
} catch(Exception $e) {
    echo json_encode(['success' => false, 'announcements' => [], 'message' => $e->getMessage()]);
}
?>