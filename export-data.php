<?php
session_start();
require_once '../config/database.php';

if(!isset($_SESSION['admin'])) {
    die('Accès non autorisé');
}

$type = $_GET['type'] ?? 'visitors';
$data = $pdo->getData();

if($type == 'visitors') {
    $filename = "visiteurs_visiondaigles_" . date('Y-m-d') . ".csv";
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Date', 'IP']);
    foreach($data['visitors'] as $v) {
        fputcsv($output, [$v['date'], $v['ip']]);
    }
    fclose($output);
    
} elseif($type == 'subscribers') {
    $filename = "abonnes_visiondaigles_" . date('Y-m-d') . ".csv";
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Prénom', 'Nom', 'Email', 'Téléphone', 'Statut', "Date d'inscription"]);
    foreach($data['subscribers'] as $sub) {
        fputcsv($output, [
            $sub['id'], $sub['first_name'], $sub['last_name'],
            $sub['email'], $sub['phone'], $sub['status'], $sub['subscribed_at']
        ]);
    }
    fclose($output);
}
?>