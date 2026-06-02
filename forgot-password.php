<?php
session_start();
require_once '../config/database.php';

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    if(empty($email)) {
        $error = "Veuillez saisir votre email";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM subscribers WHERE email = ? AND status = 'approved'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if($user) {
            // Générer un token de réinitialisation
            $reset_token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            $stmt = $pdo->prepare("UPDATE subscribers SET reset_token = ?, reset_expires = ? WHERE id = ?");
            $stmt->execute([$reset_token, $expires, $user['id']]);
            
            // Envoyer l'email
            $reset_link = "https://visiondaiglestab.page.gd/auth/reset-password.php?token=" . $reset_token;
            $to = $email;
            $subject = "Réinitialisation de votre mot de passe - Vision d'Aigles Tabernacle";
            $message = "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: #0a2f44; color: white; padding: 20px; text-align: center; }
                    .content { padding: 20px; }
                    .button { display: inline-block; background: #f4c542; color: #0a2f44; padding: 12px 25px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h2>Vision d'Aigles Tabernacle</h2>
                    </div>
                    <div class='content'>
                        <p>Bonjour " . $user['first_name'] . ",</p>
                        <p>Vous avez demandé à réinitialiser votre mot de passe. Cliquez sur le bouton ci-dessous pour créer un nouveau mot de passe :</p>
                        <center><a href='$reset_link' class='button'>Réinitialiser mon mot de passe</a></center>
                        <p>Ce lien expirera dans 1 heure.</p>
                        <p>Si vous n'êtes pas à l'origine de cette demande, ignorez simplement cet email.</p>
                    </div>
                </div>
            </body>
            </html>
            ";
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=UTF-8\r\n";
            $headers .= "From: Vision d'Aigles Tabernacle <visiondaigles.tab1@gmail.com>\r\n";
            
            mail($to, $subject, $message, $headers);
            
            $success = "Un email de réinitialisation vous a été envoyé. Vérifiez votre boîte de réception.";
        } else {
            $error = "Aucun compte trouvé avec cet email";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot de passe oublié - Vision d'Aigles Tabernacle</title>
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
            background: linear-gradient(135deg, #0a2f44 0%, #1a4a6e 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .forgot-container {
            width: 100%;
            max-width: 450px;
        }
        
        .forgot-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo-icon {
            width: 70px;
            height: 70px;
            background: #f4c542;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
        }
        
        .logo-icon i {
            font-size: 2rem;
            color: #0a2f44;
        }
        
        .logo h2 {
            color: #0a2f44;
            font-size: 1.3rem;
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
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 10px;
            font-size: 1rem;
        }
        
        .btn-submit {
            width: 100%;
            background: #f4c542;
            color: #0a2f44;
            border: none;
            padding: 14px;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
        }
        
        .alert {
            padding: 12px 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
        }
        
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        
        .back-link a {
            color: #f4c542;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="forgot-container">
        <div class="forgot-card">
            <div class="logo">
                <div class="logo-icon">
                    <i class="fas fa-key"></i>
                </div>
                <h2>Mot de passe oublié</h2>
                <p>Entrez votre email pour réinitialiser votre mot de passe</p>
            </div>
            
            <?php if($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if(!$success): ?>
            <form method="POST">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>
                <button type="submit" class="btn-submit">Envoyer l'email de réinitialisation</button>
            </form>
            <?php endif; ?>
            
            <div class="back-link">
                <a href="login.php">← Retour à la connexion</a>
            </div>
        </div>
    </div>
</body>
</html>