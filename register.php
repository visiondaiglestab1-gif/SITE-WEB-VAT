<?php
session_start();
require_once '../config/database.php';

$error = '';
$success = '';

if(isset($_SESSION['user_id'])) {
    header('Location: profile.php');
    exit;
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $newsletter = isset($_POST['newsletter']) ? 1 : 0;
    
    // Validation
    $errors = [];
    if(empty($first_name)) $errors[] = "Prénom requis";
    if(empty($last_name)) $errors[] = "Nom requis";
    if(empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email valide requis";
    if(empty($phone)) $errors[] = "Téléphone requis";
    if(empty($password)) $errors[] = "Mot de passe requis";
    if(strlen($password) < 6) $errors[] = "Mot de passe trop court (min 6 caractères)";
    if($password !== $confirm_password) $errors[] = "Les mots de passe ne correspondent pas";
    
    if(empty($errors)) {
        // Vérifier si l'email existe déjà
        $stmt = $pdo->prepare("SELECT * FROM subscribers WHERE email = ?");
        $stmt->execute([$email]);
        $existing = $stmt->fetch();
        
        if($existing) {
            if($existing['status'] == 'pending') {
                $error = "Votre demande est déjà en attente d'approbation";
            } elseif($existing['status'] == 'approved') {
                $error = "Cet email est déjà enregistré. <a href='login.php'>Connectez-vous</a>";
            } else {
                $error = "Votre compte a été rejeté. Contactez l'administration";
            }
        } else {
            // Générer un token de validation
            $validation_token = bin2hex(random_bytes(32));
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Insérer le nouvel utilisateur
            $stmt = $pdo->prepare("INSERT INTO subscribers (first_name, last_name, email, phone, password, is_newsletter, status, validation_token) VALUES (?, ?, ?, ?, ?, ?, 'pending', ?)");
            $stmt->execute([$first_name, $last_name, $email, $phone, $hashedPassword, $newsletter, $validation_token]);
            
            // Envoyer email de validation
            $validation_link = "https://visiondaiglestab.page.gd/auth/validate.php?token=" . $validation_token;
            $to = $email;
            $subject = "Validation de votre inscription - Vision d'Aigles Tabernacle";
            $message = "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: #0a2f44; color: white; padding: 20px; text-align: center; }
                    .content { padding: 20px; }
                    .button { display: inline-block; background: #f4c542; color: #0a2f44; padding: 12px 25px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                    .footer { background: #f5f5f5; padding: 15px; text-align: center; font-size: 12px; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h2>Vision d'Aigles Tabernacle</h2>
                    </div>
                    <div class='content'>
                        <h3>Bienvenue $first_name $last_name !</h3>
                        <p>Merci de vous être inscrit sur notre plateforme. Pour finaliser votre inscription et accéder à la bibliothèque audio, veuillez valider votre adresse email en cliquant sur le bouton ci-dessous :</p>
                        <center><a href='$validation_link' class='button'>Valider mon inscription</a></center>
                        <p>Ou copiez ce lien dans votre navigateur :<br>$validation_link</p>
                        <p>Après validation, votre compte devra être approuvé par l'administration. Vous recevrez un email de confirmation.</p>
                        <p>Que Dieu vous bénisse !</p>
                    </div>
                    <div class='footer'>
                        <p>&copy; 2026 Vision d'Aigles Tabernacle - Tous droits réservés</p>
                    </div>
                </div>
            </body>
            </html>
            ";
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=UTF-8\r\n";
            $headers .= "From: Vision d'Aigles Tabernacle <visiondaigles.tab1@gmail.com>\r\n";
            
            mail($to, $subject, $message, $headers);
            
            $success = "Inscription réussie ! Un email de validation vous a été envoyé. Vérifiez votre boîte de réception.";
        }
    } else {
        $error = implode('<br>', $errors);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Vision d'Aigles Tabernacle</title>
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
        
        .register-container {
            width: 100%;
            max-width: 500px;
        }
        
        .register-card {
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
        
        .logo p {
            color: #666;
            font-size: 0.85rem;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #f4c542;
            box-shadow: 0 0 0 3px rgba(244, 197, 66, 0.1);
        }
        
        .form-row {
            display: flex;
            gap: 15px;
        }
        
        .form-row .form-group {
            flex: 1;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .checkbox-group input {
            width: auto;
        }
        
        .btn-register {
            width: 100%;
            background: #f4c542;
            color: #0a2f44;
            border: none;
            padding: 14px;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-register:hover {
            background: #e6b12e;
            transform: translateY(-2px);
        }
        
        .alert {
            padding: 12px 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        
        .login-link {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        .login-link a {
            color: #f4c542;
            text-decoration: none;
            font-weight: 600;
        }
        
        .login-link a:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 480px) {
            .register-card {
                padding: 25px;
            }
            .form-row {
                flex-direction: column;
                gap: 0;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-card">
            <div class="logo">
                <div class="logo-icon">
                    <i class="fas fa-dove"></i>
                </div>
                <h2>Vision d'Aigles Tabernacle</h2>
                <p>Créez votre compte pour accéder à la bibliothèque audio</p>
            </div>
            
            <?php if($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if(!$success): ?>
            <form method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label>Prénom</label>
                        <input type="text" name="first_name" required value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Nom</label>
                        <input type="text" name="last_name" required value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>Téléphone WhatsApp</label>
                    <input type="tel" name="phone" required value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Mot de passe</label>
                        <input type="password" name="password" required>
                    </div>
                    <div class="form-group">
                        <label>Confirmer le mot de passe</label>
                        <input type="password" name="confirm_password" required>
                    </div>
                </div>
                <div class="form-group checkbox-group">
                    <input type="checkbox" name="newsletter" id="newsletter" checked>
                    <label for="newsletter">Je souhaite recevoir la newsletter</label>
                </div>
                <button type="submit" class="btn-register">S'inscrire</button>
            </form>
            <?php endif; ?>
            
            <div class="login-link">
                Déjà inscrit ? <a href="login.php">Se connecter</a>
            </div>
        </div>
    </div>
</body>
</html>