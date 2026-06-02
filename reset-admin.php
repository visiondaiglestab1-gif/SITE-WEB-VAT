<?php
session_start();
require_once '../config/database.php';

$message = '';
$error = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    if($action === 'reset') {
        // Réinitialiser le mot de passe admin
        $newPassword = 'admin123';
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        $data = $pdo->getData();
        $found = false;
        
        foreach($data['subscribers'] as &$sub) {
            if($sub['email'] === 'visiondaigles.tab1@gmail.com') {
                $sub['password'] = $hashedPassword;
                $sub['role'] = 'admin';
                $sub['status'] = 'approved';
                $found = true;
                break;
            }
        }
        
        if(!$found) {
            // Créer l'utilisateur admin s'il n'existe pas
            $data['subscribers'][] = [
                'id' => count($data['subscribers']) + 1,
                'first_name' => 'Administrateur',
                'last_name' => 'Vision',
                'email' => 'visiondaigles.tab1@gmail.com',
                'phone' => '+242066293093',
                'password' => $hashedPassword,
                'is_newsletter' => 1,
                'status' => 'approved',
                'role' => 'admin',
                'email_validated' => 1,
                'validation_token' => null,
                'reset_token' => null,
                'reset_expires' => null,
                'subscribed_at' => date('Y-m-d H:i:s'),
                'approved_at' => date('Y-m-d H:i:s')
            ];
        }
        
        $pdo->setData($data);
        $message = "✅ Mot de passe admin réinitialisé avec succès !<br>Email: visiondaigles.tab1@gmail.com<br>Mot de passe: admin123";
    }
    
    if($action === 'show') {
        // Afficher les utilisateurs existants
        $data = $pdo->getData();
        $message = "<strong>Utilisateurs dans la base de données :</strong><br>";
        foreach($data['subscribers'] as $sub) {
            $message .= "- " . $sub['email'] . " (rôle: " . ($sub['role'] ?? 'user') . ", status: " . $sub['status'] . ")<br>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialisation Admin - Vision d'Aigles</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #0a2f44;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 10px;
            padding: 30px;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 5px 20px rgba(0,0,0,0.3);
        }
        h1 {
            color: #0a2f44;
            text-align: center;
            margin-bottom: 20px;
        }
        .btn {
            display: block;
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            background: #f4c542;
            color: #0a2f44;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }
        .btn-danger {
            background: #e74c3c;
            color: white;
        }
        .message {
            background: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
        }
        hr {
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Réinitialisation Admin</h1>
        
        <?php if($message): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if($error): ?>
            <div class="message error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <input type="hidden" name="action" value="reset">
            <button type="submit" class="btn">🔑 Réinitialiser le mot de passe admin</button>
        </form>
        
        <form method="POST">
            <input type="hidden" name="action" value="show">
            <button type="submit" class="btn">📋 Afficher les utilisateurs</button>
        </form>
        
        <hr>
        
        <a href="login.php" class="btn" style="text-align: center; text-decoration: none;">← Retour à la page de connexion</a>
    </div>
</body>
</html>