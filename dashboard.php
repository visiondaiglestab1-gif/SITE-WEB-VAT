<?php
session_start();
require_once '../config/database.php';

// Vérifier si l'utilisateur est admin
$isAdmin = false;
if(isset($_SESSION['admin']) && $_SESSION['admin'] === true) {
    $isAdmin = true;
} elseif(isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
    $isAdmin = true;
    $_SESSION['admin'] = true;
}

if(!$isAdmin) {
    header('Location: login.php');
    exit;
}

$success = '';
$error = '';
$activeTab = $_GET['tab'] ?? 'dashboard';

$data = $pdo->getData();
$stats = $data['stats'];
$siteSettings = $data['site_settings'] ?? [];
$homepageSettings = $siteSettings['homepage'] ?? [];
$cultesSettings = $siteSettings['cultes'] ?? [];
$appSettings = $siteSettings['app'] ?? ['version' => '1.0.0', 'apk_path' => '', 'changelog' => ''];

// ========== GESTION DES STATISTIQUES ==========
if(isset($_POST['update_counts'])) {
    $mega = (int)$_POST['mega_count'];
    $degoo = (int)$_POST['degoo_count'];
    
    $data['stats']['sermons_count_mega_manual'] = $mega;
    $data['stats']['sermons_count_degoo_manual'] = $degoo;
    $pdo->setData($data);
    $success = "✅ Compteurs mis à jour !";
    $stats = $data['stats'];
}

// ========== GESTION DES ANNONCES ==========
if(isset($_POST['add_announcement'])) {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    
    if(empty($title) || empty($content)) {
        $error = "Veuillez remplir tous les champs";
    } else {
        $newId = count($data['announcements']) + 1;
        $data['announcements'][] = [
            'id' => $newId,
            'title' => $title,
            'content' => $content,
            'author' => $_SESSION['admin_name'] ?? 'Admin',
            'is_active' => 1,
            'is_notified' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];
        $pdo->setData($data);
        $success = "✅ Annonce ajoutée avec succès !";
    }
}

if(isset($_GET['delete_announcement'])) {
    $id = (int)$_GET['delete_announcement'];
    $newAnnouncements = [];
    foreach($data['announcements'] as $a) {
        if($a['id'] != $id) $newAnnouncements[] = $a;
    }
    $data['announcements'] = $newAnnouncements;
    $pdo->setData($data);
    $success = "✅ Annonce supprimée";
    header('Location: dashboard.php?tab=announcements');
    exit;
}

// ========== GESTION DES ABONNÉS ==========
if(isset($_GET['approve'])) {
    $id = (int)$_GET['approve'];
    $found = false;
    
    foreach($data['subscribers'] as &$sub) {
        if($sub['id'] == $id) {
            $sub['status'] = 'approved';
            $sub['approved_at'] = date('Y-m-d H:i:s');
            $found = true;
            break;
        }
    }
    
    if($found) {
        $pdo->setData($data);
        $success = "✅ Abonné approuvé avec succès !";
    } else {
        $error = "❌ Abonné non trouvé";
    }
    
    header('Location: dashboard.php?tab=subscribers');
    exit;
}

if(isset($_GET['reject'])) {
    $id = (int)$_GET['reject'];
    $found = false;
    
    foreach($data['subscribers'] as &$sub) {
        if($sub['id'] == $id) {
            $sub['status'] = 'rejected';
            $found = true;
            break;
        }
    }
    
    if($found) {
        $pdo->setData($data);
        $success = "❌ Abonné rejeté";
    } else {
        $error = "❌ Abonné non trouvé";
    }
    
    header('Location: dashboard.php?tab=subscribers');
    exit;
}

if(isset($_GET['block'])) {
    $id = (int)$_GET['block'];
    $found = false;
    
    foreach($data['subscribers'] as &$sub) {
        if($sub['id'] == $id) {
            $sub['is_blocked'] = $sub['is_blocked'] ? 0 : 1;
            $found = true;
            break;
        }
    }
    
    if($found) {
        $pdo->setData($data);
    }
    
    header('Location: dashboard.php?tab=subscribers');
    exit;
}

if(isset($_GET['delete_subscriber'])) {
    $id = (int)$_GET['delete_subscriber'];
    $newSubscribers = [];
    
    foreach($data['subscribers'] as $sub) {
        if($sub['id'] != $id) $newSubscribers[] = $sub;
    }
    
    $data['subscribers'] = $newSubscribers;
    $pdo->setData($data);
    $success = "🗑️ Abonné supprimé";
    
    header('Location: dashboard.php?tab=subscribers');
    exit;
}

// ========== GESTION DE L'APPLICATION ==========
if(isset($_POST['update_app'])) {
    $version = trim($_POST['app_version']);
    $changelog = trim($_POST['app_changelog']);
    $apk_path = $appSettings['apk_path'];
    
    if(isset($_FILES['apk_file']) && $_FILES['apk_file']['error'] == 0) {
        $upload_dir = '../uploads/apk/';
        if(!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        
        $file_name = 'visiondaigles_app_v' . preg_replace('/[^a-zA-Z0-9]/', '_', $version) . '.apk';
        $file_path = $upload_dir . $file_name;
        
        if(move_uploaded_file($_FILES['apk_file']['tmp_name'], $file_path)) {
            $apk_path = 'uploads/apk/' . $file_name;
            $success = "✅ APK téléchargée avec succès !";
        } else {
            $error = "Erreur lors de l'upload de l'APK";
        }
    }
    
    $data['site_settings']['app'] = [
        'version' => $version,
        'apk_path' => $apk_path,
        'changelog' => $changelog,
        'last_update' => date('Y-m-d H:i:s')
    ];
    $pdo->setData($data);
    
    if(empty($error)) {
        $success = "✅ Paramètres de l'application mis à jour !";
    }
}

// ========== PARAMÈTRES DU SITE ==========
if(isset($_POST['update_homepage'])) {
    $data['site_settings']['homepage'] = [
        'hero_title' => trim($_POST['hero_title']),
        'hero_text' => trim($_POST['hero_text']),
        'pastor_name' => trim($_POST['pastor_name']),
        'pastor_title' => trim($_POST['pastor_title']),
        'pastor_bio' => trim($_POST['pastor_bio']),
        'pastor_quote' => trim($_POST['pastor_quote']),
        'last_updated' => date('Y-m-d H:i:s')
    ];
    $pdo->setData($data);
    $success = "✅ Page d'accueil mise à jour !";
}

// ========== JOURS DE CULTES ==========
if(isset($_POST['update_cultes'])) {
    $cultes = [];
    for($i = 1; $i <= 3; $i++) {
        $cultes[] = [
            'day' => $_POST["culte_day_$i"],
            'time' => $_POST["culte_time_$i"],
            'description' => $_POST["culte_desc_$i"]
        ];
    }
    $data['site_settings']['cultes'] = $cultes;
    $pdo->setData($data);
    $success = "✅ Jours de cultes mis à jour !";
}

// ========== NEWSLETTER ==========
if(isset($_POST['send_newsletter'])) {
    $subject = trim($_POST['newsletter_subject']);
    $message = trim($_POST['newsletter_message']);
    
    if(empty($subject) || empty($message)) {
        $error = "Veuillez remplir le sujet et le message";
    } else {
        $subscribers = [];
        foreach($data['subscribers'] as $sub) {
            if($sub['is_newsletter'] == 1 && $sub['status'] == 'approved' && $sub['is_blocked'] != 1 && $sub['email_validated'] == 1) {
                $subscribers[] = $sub;
            }
        }
        
        if(count($subscribers) == 0) {
            $error = "❌ Aucun abonné newsletter validé.";
        } else {
            $sent = 0;
            foreach($subscribers as $sub) {
                $htmlMessage = "
                <html>
                <head><style>body{font-family:Arial;}</style></head>
                <body>
                    <h2>Vision d'Aigles Tabernacle</h2>
                    <p>Bonjour " . htmlspecialchars($sub['first_name']) . ",</p>
                    " . nl2br(htmlspecialchars($message)) . "
                    <p>Que Dieu vous bénisse !</p>
                </body>
                </html>
                ";
                
                $headers = "MIME-Version: 1.0\r\n";
                $headers .= "Content-type: text/html; charset=UTF-8\r\n";
                $headers .= "From: Vision d'Aigles Tabernacle <visiondaigles.tab1@gmail.com>\r\n";
                
                if(mail($sub['email'], $subject, $htmlMessage, $headers)) {
                    $sent++;
                }
            }
            $success = "✅ Newsletter envoyée à $sent abonné(s)";
        }
    }
}

// ========== RÉCUPÉRATION DES DONNÉES ==========
$allSubscribers = $data['subscribers'];
$pending = [];
$approved = [];
$blocked = [];
$rejected = [];

foreach($allSubscribers as $sub) {
    if($sub['is_blocked'] == 1) {
        $blocked[] = $sub;
    } elseif($sub['status'] == 'pending') {
        $pending[] = $sub;
    } elseif($sub['status'] == 'approved') {
        $approved[] = $sub;
    } elseif($sub['status'] == 'rejected') {
        $rejected[] = $sub;
    }
}

$announcements = array_reverse($data['announcements']);
$sermons = array_reverse($data['sermons']);

// Récupérer les messages de chat non lus
$unreadChats = [];
foreach($data['notifications'] as $notif) {
    if($notif['type'] == 'chat' && $notif['is_read'] == 0 && $notif['to_user_id'] == ($_SESSION['admin_id'] ?? 0)) {
        $unreadChats[] = $notif;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Vision d'Aigles</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f0f2f5; }
        
        .admin-header { background: linear-gradient(135deg, #0a2f44, #1a4a6e); color: white; padding: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; }
        .admin-header h1 { font-size: 1.3rem; display: flex; align-items: center; gap: 10px; }
        .admin-header h1 img { height: 40px; border-radius: 50%; }
        .admin-header a { color: white; text-decoration: none; background: rgba(255,255,255,0.2); padding: 8px 15px; border-radius: 5px; margin-left: 10px; }
        .admin-header a:hover { background: rgba(255,255,255,0.3); }
        
        .admin-container { display: flex; min-height: calc(100vh - 80px); }
        .sidebar { width: 260px; background: #1a1a2e; color: white; padding: 20px 0; }
        .sidebar-menu { list-style: none; }
        .sidebar-menu li { margin-bottom: 5px; }
        .sidebar-menu li a { display: flex; align-items: center; gap: 12px; padding: 12px 20px; color: #ccc; text-decoration: none; transition: all 0.3s; }
        .sidebar-menu li a:hover, .sidebar-menu li a.active { background: #f4c542; color: #0a2f44; }
        .sidebar-menu li a i { width: 25px; }
        .unread-badge { background: #e74c3c; color: white; border-radius: 50%; padding: 2px 6px; font-size: 0.7rem; margin-left: 5px; }
        
        .main-content { flex: 1; padding: 20px; }
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 25px; border-radius: 15px; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .stat-number { font-size: 2rem; font-weight: bold; color: #f4c542; }
        .section { background: white; border-radius: 15px; padding: 25px; margin-bottom: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .section h2 { margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #f4c542; display: flex; align-items: center; gap: 10px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 500; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; }
        .form-group input[type="file"] { padding: 5px; }
        .form-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; }
        .btn { padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; transition: all 0.3s; }
        .btn-primary { background: #f4c542; color: #0a2f44; }
        .btn-success { background: #28a745; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-warning { background: #ffc107; color: #333; }
        .btn-info { background: #17a2b8; color: white; }
        .btn-sm { padding: 5px 10px; font-size: 0.75rem; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; }
        .badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 0.7rem; font-weight: bold; }
        .badge-approved { background: #d4edda; color: #155724; }
        .badge-pending { background: #fff3cd; color: #856404; }
        .badge-blocked { background: #f8d7da; color: #721c24; }
        .badge-rejected { background: #e2e3e5; color: #383d41; }
        .alert-success { background: #d4edda; color: #155724; padding: 12px; border-radius: 8px; margin-bottom: 20px; }
        .alert-error { background: #f8d7da; color: #721c24; padding: 12px; border-radius: 8px; margin-bottom: 20px; }
        .action-buttons { display: flex; gap: 8px; flex-wrap: wrap; }
        
        /* Styles du chat intégré */
        .chat-container { display: flex; gap: 20px; flex-wrap: wrap; }
        .users-sidebar { width: 280px; background: #f8f9fa; border-radius: 15px; overflow: hidden; }
        .users-sidebar h3 { background: #0a2f44; color: white; padding: 15px; margin: 0; }
        .users-list { list-style: none; max-height: 400px; overflow-y: auto; }
        .users-list li { padding: 12px 15px; border-bottom: 1px solid #eee; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; gap: 10px; }
        .users-list li:hover { background: #e9ecef; }
        .users-list li.active { background: #fff3e0; border-left: 3px solid #f4c542; }
        .chat-main { flex: 1; background: #f8f9fa; border-radius: 15px; display: flex; flex-direction: column; overflow: hidden; }
        .chat-messages { height: 400px; padding: 20px; overflow-y: auto; display: flex; flex-direction: column; gap: 15px; }
        .message { display: flex; flex-direction: column; max-width: 70%; }
        .message.sent { align-self: flex-end; }
        .message.received { align-self: flex-start; }
        .message-bubble { padding: 10px 15px; border-radius: 18px; word-wrap: break-word; }
        .message.sent .message-bubble { background: #f4c542; color: #0a2f44; border-bottom-right-radius: 5px; }
        .message.received .message-bubble { background: white; color: #333; border-bottom-left-radius: 5px; }
        .message-time { font-size: 0.7rem; color: #999; margin-top: 5px; }
        .chat-input { padding: 15px; border-top: 1px solid #ddd; display: flex; gap: 10px; background: white; }
        .chat-input input { flex: 1; padding: 10px 15px; border: 1px solid #ddd; border-radius: 25px; outline: none; }
        .chat-input button { background: #f4c542; border: none; padding: 10px 20px; border-radius: 25px; cursor: pointer; font-weight: bold; }
        .no-conversation { text-align: center; padding: 50px; color: #999; }
        .user-status { width: 8px; height: 8px; border-radius: 50%; display: inline-block; }
        .status-online { background: #28a745; }
        
        @media (max-width: 992px) {
            .admin-container { flex-direction: column; }
            .sidebar { width: 100%; }
            .sidebar-menu { display: flex; flex-wrap: wrap; }
            .sidebar-menu li { flex: 1; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .chat-container { flex-direction: column; }
            .users-sidebar { width: 100%; }
        }
        @media (max-width: 768px) {
            .stats-grid { grid-template-columns: 1fr; }
            .form-row { grid-template-columns: 1fr; }
            table { font-size: 0.75rem; }
            th, td { padding: 8px; }
            .message { max-width: 85%; }
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <h1>
            <img src="../images/logo.png" alt="Logo" onerror="this.src='https://via.placeholder.com/40'">
            Vision d'Aigles Tabernacle - Administration
        </h1>
        <div>
            <span>👋 <?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?></span>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
        </div>
    </div>
    
    <div class="admin-container">
        <div class="sidebar">
            <ul class="sidebar-menu">
                <li><a href="?tab=dashboard" class="<?php echo $activeTab == 'dashboard' ? 'active' : ''; ?>"><i class="fas fa-tachometer-alt"></i> Tableau de bord</a></li>
                <li><a href="?tab=subscribers" class="<?php echo $activeTab == 'subscribers' ? 'active' : ''; ?>"><i class="fas fa-users"></i> Abonnés</a></li>
                <li><a href="?tab=support" class="<?php echo $activeTab == 'support' ? 'active' : ''; ?>"><i class="fas fa-headset"></i> Support 
                    <?php if(count($unreadChats) > 0): ?>
                        <span class="unread-badge"><?php echo count($unreadChats); ?></span>
                    <?php endif; ?>
                </a></li>
                <li><a href="?tab=stats" class="<?php echo $activeTab == 'stats' ? 'active' : ''; ?>"><i class="fas fa-chart-line"></i> Statistiques</a></li>
                <li><a href="?tab=app" class="<?php echo $activeTab == 'app' ? 'active' : ''; ?>"><i class="fas fa-mobile-alt"></i> Application</a></li>
                <li><a href="?tab=announcements" class="<?php echo $activeTab == 'announcements' ? 'active' : ''; ?>"><i class="fas fa-bullhorn"></i> Annonces</a></li>
                <li><a href="?tab=newsletter" class="<?php echo $activeTab == 'newsletter' ? 'active' : ''; ?>"><i class="fas fa-envelope"></i> Newsletter</a></li>
                <li><a href="?tab=settings" class="<?php echo $activeTab == 'settings' ? 'active' : ''; ?>"><i class="fas fa-cog"></i> Paramètres</a></li>
                <li><a href="?tab=cultes" class="<?php echo $activeTab == 'cultes' ? 'active' : ''; ?>"><i class="fas fa-calendar-alt"></i> Jours de cultes</a></li>
            </ul>
        </div>
        
        <div class="main-content">
            <?php if($success): ?>
                <div class="alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if($error): ?>
                <div class="alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <!-- ========== TABLEAU DE BORD ========== -->
            <?php if($activeTab == 'dashboard'): ?>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $stats['visitors_count']; ?></div>
                        <div><i class="fas fa-eye"></i> Visiteurs</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo count($approved); ?></div>
                        <div><i class="fas fa-user-check"></i> Abonnés actifs</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo ($stats['sermons_count_mega_manual'] ?? 0) + ($stats['sermons_count_degoo_manual'] ?? 0); ?></div>
                        <div><i class="fas fa-music"></i> Sermons</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo count($pending); ?></div>
                        <div><i class="fas fa-hourglass-half"></i> En attente</div>
                    </div>
                </div>
                
                <div class="section">
                    <h2><i class="fas fa-database"></i> Compteurs des bibliothèques</h2>
                    <form method="POST" class="form-row">
                        <div class="form-group">
                            <label>MEGA - Nombre de sermons</label>
                            <input type="number" name="mega_count" value="<?php echo $stats['sermons_count_mega_manual']; ?>">
                        </div>
                        <div class="form-group">
                            <label>DEGOO - Nombre de sermons</label>
                            <input type="number" name="degoo_count" value="<?php echo $stats['sermons_count_degoo_manual']; ?>">
                        </div>
                        <div class="form-group" style="display: flex; align-items: flex-end;">
                            <button type="submit" name="update_counts" class="btn btn-primary">Mettre à jour</button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
            
            <!-- ========== GESTION DES ABONNÉS ========== -->
            <?php if($activeTab == 'subscribers'): ?>
                <div class="section">
                    <h2><i class="fas fa-users"></i> Gestion des abonnés</h2>
                </div>
                
                <div class="section">
                    <h2>⏳ En attente (<?php echo count($pending); ?>)</h2>
                    <?php if(count($pending) > 0): ?>
                        <table>
                            <thead><tr><th>Nom</th><th>Email</th><th>Téléphone</th><th>Newsletter</th><th>Date</th><th>Actions</th></tr></thead>
                            <tbody>
                                <?php foreach($pending as $p): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($p['first_name'] . ' ' . $p['last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($p['email']); ?></td>
                                    <td><?php echo htmlspecialchars($p['phone']); ?></td>
                                    <td><?php echo $p['is_newsletter'] ? '✅ Oui' : '❌ Non'; ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($p['subscribed_at'])); ?></td>
                                    <td class="action-buttons">
                                        <a href="?approve=<?php echo $p['id']; ?>&tab=subscribers" class="btn btn-success btn-sm" onclick="return confirm('Approuver cet abonné ?')">Approuver</a>
                                        <a href="?reject=<?php echo $p['id']; ?>&tab=subscribers" class="btn btn-danger btn-sm" onclick="return confirm('Rejeter cet abonné ?')">Rejeter</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>Aucune demande en attente</p>
                    <?php endif; ?>
                </div>
                
                <div class="section">
                    <h2>✅ Approuvés (<?php echo count($approved); ?>)</h2>
                    <?php if(count($approved) > 0): ?>
                        <table>
                            <thead><tr><th>Nom</th><th>Email</th><th>Téléphone</th><th>Newsletter</th><th>Date</th><th>Actions</th></tr></thead>
                            <tbody>
                                <?php foreach($approved as $a): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($a['first_name'] . ' ' . $a['last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($a['email']); ?></td>
                                    <td><?php echo htmlspecialchars($a['phone']); ?></td>
                                    <td><?php echo $a['is_newsletter'] ? '✅ Oui' : '❌ Non'; ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($a['subscribed_at'])); ?></td>
                                    <td class="action-buttons">
                                        <a href="?block=<?php echo $a['id']; ?>&tab=subscribers" class="btn btn-warning btn-sm">Bloquer</a>
                                        <a href="?delete_subscriber=<?php echo $a['id']; ?>&tab=subscribers" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer ?')">Supprimer</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>Aucun abonné approuvé</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <!-- ========== SUPPORT (CHAT INTÉGRÉ) ========== -->
            <?php if($activeTab == 'support'): ?>
                <div class="section">
                    <h2><i class="fas fa-headset"></i> Support - Chat en direct</h2>
                    <p style="margin-bottom: 20px;">Répondez aux messages des utilisateurs en temps réel.</p>
                    
                    <div class="chat-container">
                        <div class="users-sidebar">
                            <h3><i class="fas fa-users"></i> Conversations</h3>
                            <ul class="users-list" id="usersList">
                                <li style="text-align: center; padding: 20px;">Chargement...</li>
                            </ul>
                        </div>
                        
                        <div class="chat-main">
                            <div class="chat-messages" id="chatMessages">
                                <div class="no-conversation">
                                    <i class="fas fa-comments" style="font-size: 3rem; color: #ccc;"></i>
                                    <p>Sélectionnez une conversation pour commencer à chatter</p>
                                </div>
                            </div>
                            <div class="chat-input" id="chatInput" style="display: none;">
                                <input type="text" id="messageInput" placeholder="Écrivez votre réponse..." autocomplete="off">
                                <button id="sendBtn"><i class="fas fa-paper-plane"></i> Envoyer</button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <script>
                    let currentUserId = null;
                    let currentUserName = '';
                    let myUserId = <?php echo $_SESSION['admin_id'] ?? 0; ?>;
                    let myName = '<?php echo addslashes($_SESSION['admin_name'] ?? 'Admin'); ?>';
                    
                    async function loadUsers() {
                        try {
                            const response = await fetch('../php/chat-get-users.php');
                            const data = await response.json();
                            const usersList = document.getElementById('usersList');
                            
                            if(data.success && data.users.length > 0) {
                                usersList.innerHTML = data.users.map(user => `
                                    <li onclick="selectUser(${user.id}, '${escapeHtml(user.name)}')" data-user-id="${user.id}" class="${currentUserId == user.id ? 'active' : ''}">
                                        <span class="user-status status-online"></span>
                                        <div style="flex:1">
                                            ${escapeHtml(user.name)}
                                            ${user.unread_count > 0 ? `<span class="unread-badge">${user.unread_count}</span>` : ''}
                                            <div style="font-size:0.7rem; color:#666;">${user.role === 'admin' ? 'Administrateur' : 'Membre'}</div>
                                        </div>
                                    </li>
                                `).join('');
                            } else {
                                usersList.innerHTML = '<li style="text-align: center; padding: 20px;">Aucune conversation</li>';
                            }
                        } catch(e) { console.error('Erreur:', e); }
                    }
                    
                    function selectUser(userId, userName) {
                        currentUserId = userId;
                        currentUserName = userName;
                        
                        document.querySelectorAll('.users-list li').forEach(li => li.classList.remove('active'));
                        document.querySelector(`.users-list li[data-user-id="${userId}"]`)?.classList.add('active');
                        document.getElementById('chatInput').style.display = 'flex';
                        loadMessages();
                        markAsRead(userId);
                    }
                    
                    async function loadMessages() {
                        if(!currentUserId) return;
                        try {
                            const response = await fetch(`../php/chat-get-messages.php?user_id=${currentUserId}`);
                            const data = await response.json();
                            const messagesContainer = document.getElementById('chatMessages');
                            
                            if(data.success && data.messages.length > 0) {
                                messagesContainer.innerHTML = data.messages.map(msg => `
                                    <div class="message ${msg.sender_id == myUserId ? 'sent' : 'received'}">
                                        <div class="message-bubble">${escapeHtml(msg.message)}</div>
                                        <div class="message-time">${new Date(msg.created_at).toLocaleString('fr-FR')}</div>
                                    </div>
                                `).join('');
                                messagesContainer.scrollTop = messagesContainer.scrollHeight;
                            } else {
                                messagesContainer.innerHTML = '<div class="no-conversation"><p>Aucun message. Commencez la conversation !</p></div>';
                            }
                        } catch(e) { console.error('Erreur:', e); }
                    }
                    
                    async function markAsRead(userId) {
                        try { await fetch(`../php/chat-mark-read.php?user_id=${userId}`); } catch(e) {}
                    }
                    
                    async function sendMessage() {
                        const input = document.getElementById('messageInput');
                        const message = input.value.trim();
                        if(!message || !currentUserId) return;
                        
                        try {
                            const response = await fetch('../php/chat-send.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ to_user_id: currentUserId, message: message })
                            });
                            const data = await response.json();
                            if(data.success) {
                                input.value = '';
                                loadMessages();
                                loadUsers();
                            }
                        } catch(e) { console.error('Erreur:', e); }
                    }
                    
                    function escapeHtml(text) {
                        const div = document.createElement('div');
                        div.textContent = text;
                        return div.innerHTML;
                    }
                    
                    document.getElementById('sendBtn')?.addEventListener('click', sendMessage);
                    document.getElementById('messageInput')?.addEventListener('keypress', (e) => { if(e.key === 'Enter') sendMessage(); });
                    
                    loadUsers();
                    setInterval(() => { loadUsers(); if(currentUserId) { loadMessages(); markAsRead(currentUserId); } }, 3000);
                </script>
            <?php endif; ?>
            
            <!-- ========== STATISTIQUES AVANCÉES ========== -->
            <?php if($activeTab == 'stats'): ?>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $stats['visitors_count']; ?></div>
                        <div>Visiteurs totaux</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo count($data['subscribers']); ?></div>
                        <div>Abonnés inscrits</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo count($data['announcements']); ?></div>
                        <div>Annonces</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo count($data['notifications']); ?></div>
                        <div>Notifications</div>
                    </div>
                </div>
                
                <div class="section">
                    <h2><i class="fas fa-chart-bar"></i> Graphiques</h2>
                    <canvas id="visitorsChart" style="max-height: 300px;"></canvas>
                    <div style="margin-top: 20px;">
                        <a href="export-data.php?type=visitors" class="btn btn-primary"><i class="fas fa-download"></i> Exporter les visiteurs</a>
                        <a href="export-data.php?type=subscribers" class="btn btn-primary"><i class="fas fa-download"></i> Exporter les abonnés</a>
                    </div>
                </div>
                
                <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                <script>
                    <?php
                    $visitorsByDay = [];
                    foreach($data['visitors'] as $v) {
                        $date = $v['date'];
                        if(!isset($visitorsByDay[$date])) $visitorsByDay[$date] = 0;
                        $visitorsByDay[$date]++;
                    }
                    $dates = array_keys($visitorsByDay);
                    $counts = array_values($visitorsByDay);
                    ?>
                    new Chart(document.getElementById('visitorsChart'), {
                        type: 'bar',
                        data: {
                            labels: <?php echo json_encode($dates); ?>,
                            datasets: [{ label: 'Visiteurs', data: <?php echo json_encode($counts); ?>, backgroundColor: '#f4c542' }]
                        },
                        options: { responsive: true, scales: { y: { beginAtZero: true } } }
                    });
                </script>
            <?php endif; ?>
            
            <!-- ========== GESTION DE L'APPLICATION ========== -->
            <?php if($activeTab == 'app'): ?>
                <div class="section">
                    <h2><i class="fas fa-mobile-alt"></i> Gestion de l'application mobile</h2>
                    <div style="background: #e9ecef; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                        <strong>Version actuelle :</strong> <?php echo htmlspecialchars($appSettings['version']); ?><br>
                        <strong>Dernière mise à jour :</strong> <?php echo date('d/m/Y H:i', strtotime($appSettings['last_update'] ?? 'now')); ?><br>
                        <?php if(!empty($appSettings['apk_path'])): ?>
                            <strong>APK disponible :</strong> <a href="../<?php echo $appSettings['apk_path']; ?>" target="_blank">Télécharger l'APK</a>
                        <?php else: ?>
                            <strong>APK :</strong> Non téléchargée
                        <?php endif; ?>
                    </div>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Version de l'application</label>
                                <input type="text" name="app_version" value="<?php echo htmlspecialchars($appSettings['version']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Fichier APK</label>
                                <input type="file" name="apk_file" accept=".apk">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Notes de version</label>
                            <textarea name="app_changelog" rows="3"><?php echo htmlspecialchars($appSettings['changelog'] ?? ''); ?></textarea>
                        </div>
                        <button type="submit" name="update_app" class="btn btn-primary">Publier la mise à jour</button>
                    </form>
                </div>
            <?php endif; ?>
            
            <!-- ========== GESTION DES ANNONCES ========== -->
            <?php if($activeTab == 'announcements'): ?>
                <div class="section">
                    <h2><i class="fas fa-plus-circle"></i> Ajouter une annonce</h2>
                    <form method="POST">
                        <div class="form-group">
                            <input type="text" name="title" placeholder="Titre de l'annonce" required>
                        </div>
                        <div class="form-group">
                            <textarea name="content" rows="3" placeholder="Contenu de l'annonce" required></textarea>
                        </div>
                        <button type="submit" name="add_announcement" class="btn btn-primary">Publier</button>
                    </form>
                </div>
                
                <div class="section">
                    <h2><i class="fas fa-list"></i> Annonces publiées (<?php echo count($announcements); ?>)</h2>
                    <?php if(count($announcements) > 0): ?>
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr><th>Titre</th><th>Contenu</th><th>Auteur</th><th>Date</th><th>Action</th></tr>
                                </thead>
                                <tbody>
                                    <?php foreach($announcements as $a): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($a['title']); ?></strong></td>
                                        <td><?php echo htmlspecialchars(substr($a['content'], 0, 50)) . '...'; ?></td>
                                        <td><?php echo htmlspecialchars($a['author']); ?></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($a['created_at'])); ?></td>
                                        <td><a href="?delete_announcement=<?php echo $a['id']; ?>&tab=announcements" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer ?')">Supprimer</a></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p>Aucune annonce</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <!-- ========== NEWSLETTER ========== -->
            <?php if($activeTab == 'newsletter'): ?>
                <div class="section">
                    <h2><i class="fas fa-envelope"></i> Envoyer une newsletter</h2>
                    <?php
                    $newsletterCount = 0;
                    foreach($data['subscribers'] as $sub) {
                        if($sub['is_newsletter'] == 1 && $sub['status'] == 'approved' && $sub['is_blocked'] != 1 && $sub['email_validated'] == 1) {
                            $newsletterCount++;
                        }
                    }
                    ?>
                    <div style="background: #e9ecef; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                        <strong>📧 Abonnés à la newsletter :</strong> <?php echo $newsletterCount; ?> destinataires
                    </div>
                    <form method="POST">
                        <div class="form-group">
                            <label>Sujet</label>
                            <input type="text" name="newsletter_subject" placeholder="Sujet" required>
                        </div>
                        <div class="form-group">
                            <label>Message</label>
                            <textarea name="newsletter_message" rows="10" placeholder="Votre message..." required></textarea>
                        </div>
                        <button type="submit" name="send_newsletter" class="btn btn-primary" onclick="return confirm('Envoyer la newsletter ?')">Envoyer</button>
                    </form>
                </div>
            <?php endif; ?>
            
            <!-- ========== PARAMÈTRES DU SITE ========== -->
            <?php if($activeTab == 'settings'): ?>
                <div class="section">
                    <h2><i class="fas fa-home"></i> Page d'accueil</h2>
                    <form method="POST">
                        <div class="form-group">
                            <label>Titre du héros</label>
                            <input type="text" name="hero_title" value="<?php echo htmlspecialchars($homepageSettings['hero_title'] ?? "Vision d'Aigles Tabernacle"); ?>">
                        </div>
                        <div class="form-group">
                            <label>Texte du héros</label>
                            <textarea name="hero_text" rows="6"><?php echo htmlspecialchars($homepageSettings['hero_text'] ?? ""); ?></textarea>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Nom du Pasteur</label>
                                <input type="text" name="pastor_name" value="<?php echo htmlspecialchars($homepageSettings['pastor_name'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label>Titre du Pasteur</label>
                                <input type="text" name="pastor_title" value="<?php echo htmlspecialchars($homepageSettings['pastor_title'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Biographie</label>
                            <textarea name="pastor_bio" rows="4"><?php echo htmlspecialchars($homepageSettings['pastor_bio'] ?? ''); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label>Citation</label>
                            <textarea name="pastor_quote" rows="2"><?php echo htmlspecialchars($homepageSettings['pastor_quote'] ?? ''); ?></textarea>
                        </div>
                        <button type="submit" name="update_homepage" class="btn btn-primary">Enregistrer</button>
                    </form>
                </div>
            <?php endif; ?>
            
            <!-- ========== JOURS DE CULTES ========== -->
            <?php if($activeTab == 'cultes'): ?>
                <div class="section">
                    <h2><i class="fas fa-calendar-alt"></i> Jours de cultes</h2>
                    <form method="POST">
                        <?php 
                        $defaultCultes = [
                            ['day' => 'DIMANCHE', 'time' => '9h00 - 13h00', 'description' => 'Culte Dominical - Méditation, Adoration & Louange, Prédication'],
                            ['day' => 'MERCREDI', 'time' => '16h00 - 18h30', 'description' => 'Culte de Semaine - Méditation, Adoration & Louange, Prédication'],
                            ['day' => 'VENDREDI', 'time' => '16h00 - 18h30', 'description' => 'Culte de Semaine - Méditation, Adoration & Louange, Prédication']
                        ];
                        for($i = 1; $i <= 3; $i++): 
                            $culte = isset($cultesSettings[$i-1]) ? $cultesSettings[$i-1] : $defaultCultes[$i-1];
                        ?>
                        <div style="background: #f8f9fa; padding: 15px; border-radius: 10px; margin-bottom: 20px;">
                            <h3>Culte <?php echo $i; ?></h3>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Jour</label>
                                    <input type="text" name="culte_day_<?php echo $i; ?>" value="<?php echo htmlspecialchars($culte['day']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Horaire</label>
                                    <input type="text" name="culte_time_<?php echo $i; ?>" value="<?php echo htmlspecialchars($culte['time']); ?>" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Description</label>
                                <textarea name="culte_desc_<?php echo $i; ?>" rows="2"><?php echo htmlspecialchars($culte['description']); ?></textarea>
                            </div>
                        </div>
                        <?php endfor; ?>
                        <button type="submit" name="update_cultes" class="btn btn-primary">Enregistrer</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>