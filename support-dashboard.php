<?php
session_start();
require_once '../config/database.php';

if(!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

$data = $pdo->getData();

// Récupérer les messages de support non lus
$unreadChats = [];
foreach($data['notifications'] as $notif) {
    if($notif['type'] == 'chat' && $notif['is_read'] == 0) {
        $unreadChats[] = $notif;
    }
}

// Compter les messages par utilisateur
$messagesByUser = [];
foreach($data['notifications'] as $notif) {
    if($notif['type'] == 'chat') {
        $userId = $notif['from_user_id'];
        if(!isset($messagesByUser[$userId])) {
            $messagesByUser[$userId] = 0;
        }
        $messagesByUser[$userId]++;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Dashboard - Vision d'Aigles</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f0f2f5; }
        
        .admin-header { background: linear-gradient(135deg, #0a2f44, #1a4a6e); color: white; padding: 20px; display: flex; justify-content: space-between; align-items: center; }
        .admin-header a { color: white; text-decoration: none; background: rgba(255,255,255,0.2); padding: 8px 15px; border-radius: 5px; }
        
        .container { max-width: 1200px; margin: 20px auto; padding: 0 20px; }
        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 25px; border-radius: 15px; text-align: center; }
        .stat-number { font-size: 2rem; font-weight: bold; color: #f4c542; }
        
        .section { background: white; border-radius: 15px; padding: 25px; margin-bottom: 30px; }
        .section h2 { margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #f4c542; }
        
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; }
        
        .btn { display: inline-block; padding: 8px 15px; background: #f4c542; color: #0a2f44; text-decoration: none; border-radius: 5px; }
        
        @media (max-width: 768px) {
            .stats-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <h1><i class="fas fa-headset"></i> Support - Administration</h1>
        <div>
            <a href="dashboard.php"><i class="fas fa-arrow-left"></i> Retour</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
        </div>
    </div>
    
    <div class="container">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo count($unreadChats); ?></div>
                <div><i class="fas fa-envelope"></i> Messages non lus</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo count($data['notifications']); ?></div>
                <div><i class="fas fa-comments"></i> Messages totaux</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><a href="chat.php" class="btn"><i class="fas fa-comment-dots"></i> Accéder au chat</a></div>
                <div>Chat en direct</div>
            </div>
        </div>
        
        <div class="section">
            <h2><i class="fas fa-users"></i> Activité du support</h2>
            <table>
                <thead>
                    <tr><th>Utilisateur</th><th>Messages</th><th>Dernier message</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach($data['subscribers'] as $sub): 
                        $userMessages = 0;
                        $lastMessage = '';
                        foreach($data['notifications'] as $notif) {
                            if($notif['type'] == 'chat' && $notif['from_user_id'] == $sub['id']) {
                                $userMessages++;
                                $lastMessage = $notif['created_at'];
                            }
                        }
                        if($userMessages > 0):
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($sub['first_name'] . ' ' . $sub['last_name']); ?></td>
                        <td><?php echo $userMessages; ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($lastMessage)); ?></td>
                        <td><a href="chat.php?user=<?php echo $sub['id']; ?>" class="btn">Répondre</a></td>
                    </tr>
                    <?php endif; endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>