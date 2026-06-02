<?php
session_start();
require_once '../config/database.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Récupérer les informations de l'utilisateur
$stmt = $pdo->prepare("SELECT * FROM subscribers WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

$success = '';
$error = '';

// Mise à jour du profil
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $phone = trim($_POST['phone']);
    $newsletter = isset($_POST['newsletter']) ? 1 : 0;
    
    $data = $pdo->getData();
    foreach($data['subscribers'] as &$sub) {
        if($sub['id'] == $_SESSION['user_id']) {
            $sub['first_name'] = $first_name;
            $sub['last_name'] = $last_name;
            $sub['phone'] = $phone;
            $sub['is_newsletter'] = $newsletter;
            break;
        }
    }
    $pdo->setData($data);
    
    $_SESSION['user_name'] = $first_name . ' ' . $last_name;
    $success = "Profil mis à jour avec succès !";
    
    // Recharger les données
    $stmt = $pdo->prepare("SELECT * FROM subscribers WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
}

// Changement de mot de passe
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if(!password_verify($current_password, $user['password'])) {
        $error = "Mot de passe actuel incorrect";
    } elseif(strlen($new_password) < 6) {
        $error = "Le nouveau mot de passe doit contenir au moins 6 caractères";
    } elseif($new_password !== $confirm_password) {
        $error = "Les mots de passe ne correspondent pas";
    } else {
        $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
        $data = $pdo->getData();
        foreach($data['subscribers'] as &$sub) {
            if($sub['id'] == $_SESSION['user_id']) {
                $sub['password'] = $hashedPassword;
                break;
            }
        }
        $pdo->setData($data);
        $success = "Mot de passe modifié avec succès !";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon compte - Vision d'Aigles Tabernacle</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: #f5f5f5;
        }
        
        .header {
            background: #0a2f44;
            color: white;
            padding: 20px 0;
        }
        
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .logo-icon {
            width: 45px;
            height: 45px;
            background: #f4c542;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .logo-icon i {
            font-size: 1.3rem;
            color: #0a2f44;
        }
        
        .logo h1 {
            font-size: 1.2rem;
        }
        
        .nav-links {
            display: flex;
            gap: 20px;
            align-items: center;
        }
        
        .nav-links a {
            color: white;
            text-decoration: none;
        }
        
        .nav-links a:hover {
            color: #f4c542;
        }
        
        .btn-logout {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 50px;
            cursor: pointer;
        }
        
        .main {
            padding: 40px 0;
        }
        
        .profile-grid {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 30px;
        }
        
        .sidebar {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            text-align: center;
        }
        
        .avatar {
            width: 100px;
            height: 100px;
            background: #f4c542;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
        }
        
        .avatar i {
            font-size: 3rem;
            color: #0a2f44;
        }
        
        .sidebar h3 {
            margin-bottom: 5px;
        }
        
        .sidebar p {
            color: #666;
            font-size: 0.85rem;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.75rem;
            margin-top: 15px;
        }
        
        .status-approved {
            background: #d4edda;
            color: #155724;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .content-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        
        .content-card h2 {
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f4c542;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .btn-submit {
            background: #f4c542;
            color: #0a2f44;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
        }
        
        .alert {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
        }
        
        /* Styles du chat */
        .chat-container {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .chat-messages {
            height: 350px;
            overflow-y: auto;
            background: #f8f9fa;
            border-radius: 15px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .message {
            display: flex;
            flex-direction: column;
            max-width: 80%;
        }
        
        .message.sent {
            align-self: flex-end;
        }
        
        .message.received {
            align-self: flex-start;
        }
        
        .message-bubble {
            padding: 12px 18px;
            border-radius: 20px;
            word-wrap: break-word;
            position: relative;
        }
        
        .message.sent .message-bubble {
            background: #f4c542;
            color: #0a2f44;
            border-bottom-right-radius: 5px;
        }
        
        .message.received .message-bubble {
            background: white;
            color: #333;
            border-bottom-left-radius: 5px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }
        
        .message-time {
            font-size: 0.7rem;
            color: #999;
            margin-top: 5px;
            margin-left: 10px;
        }
        
        .message.sent .message-time {
            text-align: right;
        }
        
        .chat-input {
            display: flex;
            gap: 10px;
            background: white;
            padding: 15px;
            border-radius: 50px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .chat-input input {
            flex: 1;
            padding: 12px 20px;
            border: 1px solid #ddd;
            border-radius: 50px;
            outline: none;
            font-size: 1rem;
        }
        
        .chat-input input:focus {
            border-color: #f4c542;
        }
        
        .chat-input button {
            background: #f4c542;
            border: none;
            padding: 12px 25px;
            border-radius: 50px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s;
        }
        
        .chat-input button:hover {
            background: #e6b12e;
            transform: scale(1.02);
        }
        
        .no-messages {
            text-align: center;
            padding: 40px;
            color: #999;
        }
        
        .no-messages i {
            font-size: 3rem;
            margin-bottom: 10px;
            color: #ddd;
        }
        
        /* Badge de notification */
        .unread-badge {
            background: #e74c3c;
            color: white;
            border-radius: 50%;
            padding: 2px 8px;
            font-size: 0.7rem;
            margin-left: 8px;
        }
        
        @media (max-width: 768px) {
            .profile-grid {
                grid-template-columns: 1fr;
            }
            .form-row {
                grid-template-columns: 1fr;
                gap: 10px;
            }
            .message {
                max-width: 90%;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="logo">
                <div class="logo-icon">
                    <i class="fas fa-dove"></i>
                </div>
                <h1>Vision d'Aigles Tabernacle</h1>
            </div>
            <div class="nav-links">
                <a href="../index.php#accueil">Accueil</a>
                <a href="../index.php#bibliotheque">Sermons</a>
                <a href="../index.php#live">Médias</a>
                <form method="POST" action="logout.php" style="display: inline;">
                    <button type="submit" class="btn-logout">Déconnexion</button>
                </form>
            </div>
        </div>
    </header>
    
    <main class="main">
        <div class="container">
            <div class="profile-grid">
                <aside class="sidebar">
                    <div class="avatar">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <h3><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h3>
                    <p><?php echo htmlspecialchars($user['email']); ?></p>
                    <span class="status-badge status-<?php echo $user['status']; ?>">
                        <?php echo $user['status'] == 'approved' ? '✅ Compte approuvé' : '⏳ En attente d\'approbation'; ?>
                    </span>
                </aside>
                
                <div>
                    <?php if($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    <?php if($error): ?>
                        <div class="alert alert-error"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <!-- Formulaire profil -->
                    <div class="content-card">
                        <h2><i class="fas fa-user-edit"></i> Informations personnelles</h2>
                        <form method="POST">
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Prénom</label>
                                    <input type="text" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Nom</label>
                                    <input type="text" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                                <small style="color: #666;">L'email ne peut pas être modifié</small>
                            </div>
                            <div class="form-group">
                                <label>Téléphone WhatsApp</label>
                                <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>
                                    <input type="checkbox" name="newsletter" value="1" <?php echo $user['is_newsletter'] ? 'checked' : ''; ?>>
                                    Recevoir la newsletter
                                </label>
                            </div>
                            <button type="submit" name="update_profile" class="btn-submit">Mettre à jour</button>
                        </form>
                    </div>
                    
                    <!-- Formulaire changement mot de passe -->
                    <div class="content-card">
                        <h2><i class="fas fa-lock"></i> Changer mon mot de passe</h2>
                        <form method="POST">
                            <div class="form-group">
                                <label>Mot de passe actuel</label>
                                <input type="password" name="current_password" required>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Nouveau mot de passe</label>
                                    <input type="password" name="new_password" required>
                                </div>
                                <div class="form-group">
                                    <label>Confirmer le nouveau mot de passe</label>
                                    <input type="password" name="confirm_password" required>
                                </div>
                            </div>
                            <button type="submit" name="change_password" class="btn-submit">Changer le mot de passe</button>
                        </form>
                    </div>
                    
                    <!-- ========== SUPPORT - CHAT AVEC L'ADMINISTRATEUR ========== -->
                    <div class="content-card">
                        <h2><i class="fas fa-headset"></i> Support - Contacter l'administrateur</h2>
                        <p style="margin-bottom: 15px; color: #666; font-size: 0.9rem;">
                            <i class="fas fa-info-circle"></i> Posez vos questions, signalez un problème ou demandez de l'aide. L'administrateur vous répondra dès que possible.
                        </p>
                        
                        <div class="chat-container">
                            <div class="chat-messages" id="chatMessages">
                                <div class="no-messages">
                                    <i class="fas fa-comments"></i>
                                    <p>Chargement des messages...</p>
                                </div>
                            </div>
                            <div class="chat-input">
                                <input type="text" id="messageInput" placeholder="Écrivez votre message au support..." autocomplete="off">
                                <button id="sendBtn"><i class="fas fa-paper-plane"></i> Envoyer</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <script>
        let myUserId = <?php echo $_SESSION['user_id']; ?>;
        let adminId = 1; // L'admin a l'ID 1
        
        // Fonction pour échapper le HTML
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        // Formater la date
        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleString('fr-FR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }
        
        // Charger les messages
        async function loadMessages() {
            try {
                const response = await fetch('../php/chat-get-messages.php?user_id=' + adminId);
                const data = await response.json();
                const container = document.getElementById('chatMessages');
                
                if(data.success && data.messages && data.messages.length > 0) {
                    if(data.messages.length === 0) {
                        container.innerHTML = `
                            <div class="no-messages">
                                <i class="fas fa-comments"></i>
                                <p>Aucun message. Commencez la conversation !</p>
                                <p style="font-size: 0.8rem;">L'administrateur vous répondra dès que possible.</p>
                            </div>
                        `;
                    } else {
                        container.innerHTML = data.messages.map(msg => `
                            <div class="message ${msg.sender_id == myUserId ? 'sent' : 'received'}">
                                <div class="message-bubble">
                                    ${escapeHtml(msg.message)}
                                </div>
                                <div class="message-time">
                                    ${formatDate(msg.created_at)}
                                    ${msg.sender_id == myUserId ? (msg.is_read ? '✓✓ Lu' : '✓ Envoyé') : ''}
                                </div>
                            </div>
                        `).join('');
                        
                        // Scroller en bas
                        container.scrollTop = container.scrollHeight;
                    }
                } else {
                    container.innerHTML = `
                        <div class="no-messages">
                            <i class="fas fa-comments"></i>
                            <p>Aucun message. Commencez la conversation !</p>
                            <p style="font-size: 0.8rem;">L'administrateur vous répondra dès que possible.</p>
                        </div>
                    `;
                }
            } catch(e) {
                console.error('Erreur chargement messages:', e);
                const container = document.getElementById('chatMessages');
                container.innerHTML = `
                    <div class="no-messages">
                        <i class="fas fa-exclamation-triangle"></i>
                        <p>Erreur de chargement. Veuillez rafraîchir la page.</p>
                    </div>
                `;
            }
        }
        
        // Envoyer un message
        async function sendMessage() {
            const input = document.getElementById('messageInput');
            const message = input.value.trim();
            
            if(!message) {
                alert('Veuillez écrire un message');
                return;
            }
            
            try {
                const response = await fetch('../php/chat-send.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ 
                        to_user_id: adminId, 
                        message: message 
                    })
                });
                const data = await response.json();
                
                if(data.success) {
                    input.value = '';
                    loadMessages();
                } else {
                    alert('Erreur lors de l\'envoi du message: ' + (data.message || 'Inconnue'));
                }
            } catch(e) {
                console.error('Erreur envoi message:', e);
                alert('Erreur de connexion. Veuillez réessayer.');
            }
        }
        
        // Marquer les messages comme lus
        async function markAsRead() {
            try {
                await fetch('../php/chat-mark-read.php?user_id=' + adminId);
            } catch(e) {
                console.error('Erreur marquage lu:', e);
            }
        }
        
        // Événements
        document.getElementById('sendBtn')?.addEventListener('click', sendMessage);
        document.getElementById('messageInput')?.addEventListener('keypress', (e) => {
            if(e.key === 'Enter') sendMessage();
        });
        
        // Charger les messages au démarrage
        loadMessages();
        markAsRead();
        
        // Rafraîchir les messages toutes les 5 secondes
        setInterval(() => {
            loadMessages();
            markAsRead();
        }, 5000);
    </script>
</body>
</html>