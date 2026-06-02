<?php
session_start();
require_once '../config/database.php';

$message = '';
$type = '';

if(isset($_GET['token'])) {
    $token = $_GET['token'];
    
    $stmt = $pdo->prepare("SELECT * FROM subscribers WHERE validation_token = ? AND email_validated = 0");
    $stmt->execute([$token]);
    $user = $stmt->fetch();
    
    if($user) {
        $stmt = $pdo->prepare("UPDATE subscribers SET email_validated = 1, validation_token = NULL WHERE id = ?");
        $stmt->execute([$user['id']]);
        $message = "✅ Votre email a été validé avec succès ! Votre compte est maintenant en attente d'approbation par l'administration. Vous recevrez un email de confirmation.";
        $type = "success";
    } else {
        $message = "❌ Lien de validation invalide ou déjà utilisé.";
        $type = "error";
    }
} else {
    $message = "❌ Aucun token de validation fourni.";
    $type = "error";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validation email - Vision d'Aigles</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #0a2f44 0%, #1a4a6e 100%); min-height: 100vh; display: flex; justify-content: center; align-items: center; padding: 20px; }
        .container { width: 100%; max-width: 500px; }
        .card { background: white; border-radius: 20px; padding: 40px; text-align: center; box-shadow: 0 20px 60px rgba(0,0,0,0.3); }
        .icon { width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; }
        .icon-success { background: #d4edda; color: #155724; }
        .icon-error { background: #f8d7da; color: #721c24; }
        .icon i { font-size: 2.5rem; }
        h2 { margin-bottom: 15px; }
        .message { margin-bottom: 30px; line-height: 1.6; }
        .btn { display: inline-block; background: #f4c542; color: #0a2f44; padding: 12px 30px; border-radius: 50px; text-decoration: none; font-weight: 600; transition: all 0.3s; }
        .btn:hover { transform: translateY(-2px); }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="icon icon-<?php echo $type; ?>">
                <i class="fas <?php echo $type == 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
            </div>
            <h2><?php echo $type == 'success' ? 'Validation réussie !' : 'Erreur de validation'; ?></h2>
            <div class="message"><?php echo $message; ?></div>
            <a href="../index.php" class="btn">Retour au site</a>
        </div>
    </div>
</body>
</html>