<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once '../config/database.php';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $newsletter = isset($_POST['newsletter']) ? 1 : 0;
    
    // Validation
    $errors = [];
    if(empty($first_name)) $errors[] = "Prénom requis";
    if(empty($last_name)) $errors[] = "Nom requis";
    if(empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email valide requis";
    if(empty($phone)) $errors[] = "Téléphone requis";
    if(empty($password)) $errors[] = "Mot de passe requis";
    if(strlen($password) < 6) $errors[] = "Mot de passe trop court (min 6 caractères)";
    
    if(!empty($errors)) {
        echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
        exit;
    }
    
    // Vérifier si l'email existe déjà
    $stmt = $pdo->prepare("SELECT * FROM subscribers WHERE email = ?");
    $stmt->execute([$email]);
    $existing = $stmt->fetch();
    
    if($existing) {
        if($existing['status'] == 'pending') {
            echo json_encode(['success' => false, 'message' => 'Une demande est déjà en attente pour cet email']);
        } elseif($existing['status'] == 'approved') {
            echo json_encode(['success' => false, 'message' => 'Cet email est déjà enregistré. <a href="login.php">Connectez-vous</a>']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Cette demande a été rejetée. Contactez l\'administration']);
        }
        exit;
    }
    
    // Générer un token de validation
    $validation_token = bin2hex(random_bytes(32));
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Insérer le nouvel abonné
    $stmt = $pdo->prepare("INSERT INTO subscribers (first_name, last_name, email, phone, password, is_newsletter, validation_token) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $result = $stmt->execute([$first_name, $last_name, $email, $phone, $hashedPassword, $newsletter, $validation_token]);
    
    if($result) {
        // Envoyer email de validation
        $validation_link = "https://visiondaiglestab.page.gd/auth/validate.php?token=" . $validation_token;
        $subject = "Confirmation de votre inscription - Vision d'Aigles Tabernacle";
        $message = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; }
                .header { background: #0a2f44; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; }
                .button { display: inline-block; background: #f4c542; color: #0a2f44; padding: 12px 25px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                .footer { background: #f5f5f5; padding: 15px; text-align: center; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h2>Vision d'Aigles Tabernacle</h2>
            </div>
            <div class='content'>
                <h3>Bienvenue $first_name $last_name !</h3>
                <p>Merci de vous être inscrit sur notre plateforme. Pour finaliser votre inscription, veuillez confirmer votre adresse email en cliquant sur le bouton ci-dessous :</p>
                <center><a href='$validation_link' class='button'>Confirmer mon email</a></center>
                <p>Ou copiez ce lien :<br>$validation_link</p>
                <p>Après validation, votre compte devra être approuvé par l'administration. Vous recevrez un email de confirmation.</p>
                <p>Que Dieu vous bénisse !</p>
            </div>
            <div class='footer'>
                <p>&copy; 2026 Vision d'Aigles Tabernacle</p>
            </div>
        </body>
        </html>
        ";
        
        sendEmail($email, $subject, $message);
        echo json_encode(['success' => true, 'message' => 'Inscription réussie ! Un email de validation vous a été envoyé.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'inscription, veuillez réessayer']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
}
?>