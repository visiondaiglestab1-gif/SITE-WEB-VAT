<?php
session_start();
header('Content-Type: application/json');
require_once '../config/database.php');

// Récupérer la géolocalisation du visiteur via IP (approximative)
function getGeoLocation($ip) {
    // API gratuite pour la géolocalisation par IP
    $url = "http://ip-api.com/json/{$ip}";
    $response = @file_get_contents($url);
    
    if($response) {
        $data = json_decode($response, true);
        if($data && $data['status'] == 'success') {
            return [
                'city' => $data['city'],
                'region' => $data['regionName'],
                'country' => $data['country'],
                'lat' => $data['lat'],
                'lon' => $data['lon']
            ];
        }
    }
    return null;
}

$ip = $_SERVER['REMOTE_ADDR'];
$location = getGeoLocation($ip);

// Enregistrer la visite avec géolocalisation
$data = $pdo->getData();
$data['visitors'][] = [
    'ip' => $ip,
    'date' => date('Y-m-d H:i:s'),
    'location' => $location
];
$pdo->setData($data);

echo json_encode(['success' => true, 'location' => $location]);
?>