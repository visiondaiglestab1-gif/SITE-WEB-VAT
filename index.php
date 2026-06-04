<?php
session_start();
require_once 'config/database.php';

$isLoggedIn = isset($_SESSION['user_id']) && isset($_SESSION['user_email']);
$userId = $_SESSION['user_id'] ?? 0;
$userName = $_SESSION['user_name'] ?? 'Membre';

// Récupérer les données depuis la base de données
$data = $pdo->getData();
$stats = $data['stats'];
$siteSettings = $data['site_settings'] ?? [];
$homepage = $siteSettings['homepage'] ?? [];
$cultesList = $siteSettings['cultes'] ?? [];
$appSettings = $siteSettings['app'] ?? ['version' => '1.0.0', 'apk_path' => '', 'changelog' => ''];
$announcements = $data['announcements'] ?? [];
$gallery = $data['gallery'] ?? [];

// Valeurs par défaut des paramètres du site
$hero_title = $homepage['hero_title'] ?? "Vision d'Aigles Tabernacle";
$hero_text = $homepage['hero_text'] ?? "« À aucun instant je n'apporte aux gens un message pour les pousser à me suivre... » — Rév. William Marrion Branham";
$pastor_name = $homepage['pastor_name'] ?? "ARTHUR GÉDÉON MOUZITA MAYOULOU";
$pastor_title = $homepage['pastor_title'] ?? "Fondateur & Pasteur Principal";
$pastor_bio = $homepage['pastor_bio'] ?? "Homme de Dieu, Charismatique, Visionnaire et Dévoué au Travail du Seigneur JÉSUS-CHRIST.";
$pastor_quote = $homepage['pastor_quote'] ?? "Notre vision est de voir l'Épouse de CHRIST jouir de sa position et de ses privilèges.";

// Valeurs par défaut des jours de cultes
$defaultCultes = [
    ['day' => 'DIMANCHE', 'time' => '9h00 - 13h00', 'description' => 'Culte Dominical - Méditation, Adoration & Louange, Prédication'],
    ['day' => 'MERCREDI', 'time' => '16h00 - 18h30', 'description' => 'Culte de Semaine - Méditation, Adoration & Louange, Prédication'],
    ['day' => 'VENDREDI', 'time' => '16h00 - 18h30', 'description' => 'Culte de Semaine - Méditation, Adoration & Louange, Prédication']
];
if(empty($cultesList)) {
    $cultesList = $defaultCultes;
}

// Version de l'application
$appVersion = $appSettings['version'] ?? '1.0.0';
$apkPath = $appSettings['apk_path'] ?? '';
$appLastUpdate = $appSettings['last_update'] ?? '';

// Gestion du thème (clair/sombre)
$theme = $_COOKIE['theme'] ?? 'light';

// Récupérer les dernières annonces
$latestAnnouncements = array_slice(array_reverse($announcements), 0, 5);

// Détection du logo - vérifier plusieurs chemins possibles
$logoPath = '';
if(file_exists('assets/images/logo.png')) {
    $logoPath = 'assets/images/logo.png';
} elseif(file_exists('images/logo.png')) {
    $logoPath = 'images/logo.png';
} elseif(file_exists('../assets/images/logo.png')) {
    $logoPath = '../assets/images/logo.png';
} else {
    $logoPath = ''; // Pas de logo trouvé
}

// LIEN DE TÉLÉCHARGEMENT DE L'APPLICATION (AJOUTÉ)
$appDownloadLink = "https://upload.app/download/vision-d/com.visiondaigles.app/723c33c39a8d09512f20cab12cc8ce2346bd6b10287e409ac0615a25d902b84b";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <meta name="description" content="Vision d'Aigles Tabernacle - Église chrétienne à Pointe-Noire, Congo. Cultes, sermons, enseignements bibliques et communauté de foi.">
    <title><?php echo htmlspecialchars($hero_title); ?></title>
    <link rel="icon" type="image/png" href="<?php echo $logoPath ?: 'assets/images/favicon.png'; ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <style>
        /* ========== VARIABLES THEMES ========== */
        :root {
            --primary: #0a2f44;
            --primary-light: #1a4a6e;
            --gold: #f4c542;
            --gold-dark: #d4a22a;
            --youtube: #FF0000;
            --whatsapp: #25D366;
            --radio: #9146FF;
            --text: #333;
            --text-light: #666;
            --white: #fff;
            --gray: #f8f9fa;
            --gray-dark: #e9ecef;
            --shadow: rgba(0,0,0,0.1);
            --transition: all 0.3s ease;
        }

        body.dark {
            --primary: #1a1a2e;
            --primary-light: #16213e;
            --gold: #f4c542;
            --text: #f0f0f0;
            --text-light: #aaa;
            --white: #1e1e2e;
            --gray: #2d2d3a;
            --gray-dark: #252530;
            --shadow: rgba(0,0,0,0.3);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--text);
            overflow-x: hidden;
            background: var(--white);
            transition: var(--transition);
        }

        .container {
            width: 90%;
            max-width: 1280px;
            margin: 0 auto;
        }

        /* ========== HEADER ========== */
        .header {
            background: var(--primary);
            padding: 15px 0;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
            transition: var(--transition);
        }

        .header.scrolled {
            padding: 10px 0;
            background: rgba(10, 47, 68, 0.95);
            backdrop-filter: blur(10px);
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
            cursor: pointer;
        }

        .logo-icon {
            width: 50px;
            height: 50px;
            background: var(--gold);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .logo-icon img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .logo-icon i {
            font-size: 1.5rem;
            color: var(--primary);
        }

        .logo-text h1 {
            font-size: 1.2rem;
            color: white;
            line-height: 1.2;
        }

        .logo-text span {
            font-size: 0.7rem;
            color: var(--gold);
            letter-spacing: 2px;
        }

        .nav-wrapper {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        nav ul {
            display: flex;
            list-style: none;
            gap: 25px;
        }

        nav ul li a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
            font-size: 0.95rem;
        }

        nav ul li a:hover {
            color: var(--gold);
        }

        .theme-toggle-btn {
            background: rgba(255,255,255,0.2);
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
        }

        .theme-toggle-btn:hover {
            background: var(--gold);
            color: var(--primary);
            transform: rotate(15deg);
        }

        .auth-buttons {
            display: flex;
            gap: 10px;
        }

        .login-btn, .register-btn, .profile-btn, .logout-btn {
            background: var(--gold);
            color: var(--primary);
            border: none;
            padding: 8px 20px;
            border-radius: 50px;
            cursor: pointer;
            transition: var(--transition);
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .logout-btn {
            background: #dc3545;
            color: white;
        }

        .login-btn:hover, .register-btn:hover, .profile-btn:hover {
            transform: scale(1.05);
        }

        .menu-toggle {
            display: none;
            font-size: 1.5rem;
            color: white;
            cursor: pointer;
        }

        /* ========== DATETIME BAR ========== */
        .datetime-bar {
            background: var(--gold);
            padding: 8px 0;
            position: fixed;
            top: 80px;
            left: 0;
            width: 100%;
            z-index: 999;
        }

        .datetime-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .date-display, .time-display {
            font-weight: 600;
            color: var(--primary);
            font-size: 0.9rem;
        }

        /* ========== HERO ========== */
        .hero {
            position: relative;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            margin-top: 120px;
            overflow: hidden;
            padding: 60px 0;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        }

        .hero-content {
            position: relative;
            z-index: 2;
            color: white;
            max-width: 900px;
            padding: 0 20px;
        }

        .hero-badge {
            display: inline-block;
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(10px);
            padding: 8px 20px;
            border-radius: 50px;
            font-size: 0.85rem;
            margin-bottom: 20px;
        }

        .hero-content h1 {
            font-size: 3rem;
            margin-bottom: 20px;
            line-height: 1.2;
        }

        .hero-content h1 span {
            color: var(--gold);
        }

        .hero-content p {
            font-size: 1rem;
            margin-bottom: 30px;
            line-height: 1.8;
            font-style: italic;
        }

        .hero-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-primary, .btn-outline {
            padding: 12px 30px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
        }

        .btn-primary {
            background: var(--gold);
            color: var(--primary);
            border: none;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .btn-outline {
            border: 2px solid white;
            color: white;
            background: transparent;
        }

        .btn-outline:hover {
            background: white;
            color: var(--primary);
            transform: translateY(-3px);
        }

        .scroll-indicator {
            position: absolute;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            text-align: center;
            color: white;
            z-index: 2;
            cursor: pointer;
            animation: bounce 2s infinite;
        }

        @keyframes bounce {
            0%, 100% { transform: translateX(-50%) translateY(0); }
            50% { transform: translateX(-50%) translateY(10px); }
        }

        /* ========== STATS COUNTER ========== */
        .stats-counter {
            background: var(--gray);
            padding: 50px 0;
            position: relative;
            top: -30px;
            border-radius: 30px;
            margin-bottom: -30px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 30px;
        }

        .stat-item {
            background: var(--white);
            padding: 25px;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 2px 10px var(--shadow);
            transition: var(--transition);
        }

        .stat-item:hover {
            transform: translateY(-10px);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            background: rgba(244, 197, 66, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
        }

        .stat-icon i {
            font-size: 1.8rem;
            color: var(--gold);
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary);
            display: block;
        }

        .stat-label {
            font-size: 0.85rem;
            color: var(--text-light);
        }

        /* ========== SECTIONS ========== */
        .section {
            padding: 80px 0;
        }

        .section-gray {
            background: var(--gray);
        }

        .section-title {
            text-align: center;
            margin-bottom: 50px;
        }

        .section-label {
            display: inline-block;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 3px;
            color: var(--gold);
            margin-bottom: 15px;
        }

        .section-label:before {
            content: '';
            display: inline-block;
            width: 30px;
            height: 2px;
            background: var(--gold);
            margin-right: 10px;
            vertical-align: middle;
        }

        .section-title h2 {
            font-size: 2rem;
            color: var(--primary);
            margin-bottom: 10px;
        }

        .section-title h2 span {
            color: var(--gold);
        }

        .divider {
            width: 60px;
            height: 3px;
            background: var(--gold);
            margin: 20px auto;
        }

        /* ========== PASTEUR SECTION ========== */
        .pastor-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
            align-items: start;
        }

        .pastor-image {
            position: relative;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px var(--shadow);
        }

        .pastor-image img {
            width: 100%;
            height: auto;
            display: block;
            transition: var(--transition);
        }

        .pastor-image:hover img {
            transform: scale(1.05);
        }

        .whatsapp-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: var(--whatsapp);
            color: white;
            padding: 12px 24px;
            border-radius: 50px;
            text-decoration: none;
            transition: var(--transition);
            font-weight: 600;
            margin-top: 20px;
        }

        .whatsapp-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(37, 211, 102, 0.4);
        }

        .pastor-content h3 {
            font-size: 1.5rem;
            margin-bottom: 10px;
        }

        .pastor-title {
            color: var(--gold);
            font-weight: 600;
            margin-bottom: 20px;
        }

        .pastor-content p {
            margin-bottom: 15px;
        }

        .pastor-quote {
            background: rgba(244, 197, 66, 0.1);
            padding: 20px;
            border-radius: 15px;
            margin: 20px 0;
            position: relative;
        }

        .pastor-quote i {
            position: absolute;
            top: 15px;
            left: 20px;
            font-size: 2rem;
            color: var(--gold);
            opacity: 0.3;
        }

        .pastor-quote p {
            margin-left: 30px;
            font-style: italic;
            margin-bottom: 0;
        }

        /* ========== BIBLIOTHÈQUE ========== */
        .library-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }

        .library-card {
            background: var(--white);
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 5px 15px var(--shadow);
            transition: var(--transition);
        }

        .library-card:hover {
            transform: translateY(-10px);
        }

        .library-icon {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2.5rem;
            color: white;
        }

        .audio-bg { background: linear-gradient(135deg, #1A2B4C, #2C3E66); }
        .mega-bg { background: linear-gradient(135deg, #E1306C, #C13584); }
        .degoo-bg { background: linear-gradient(135deg, #FF6B35, #FF8C42); }

        .library-stats {
            margin: 15px 0;
        }

        .library-count {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary);
            display: block;
        }

        .btn-library {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 12px 25px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            margin-top: 20px;
            transition: var(--transition);
        }

        .audio-btn { background: linear-gradient(135deg, #1A2B4C, #2C3E66); color: white; }
        .mega-btn { background: linear-gradient(135deg, #E1306C, #C13584); color: white; }
        .degoo-btn { background: linear-gradient(135deg, #FF6B35, #FF8C42); color: white; }

        .btn-library:hover {
            transform: scale(1.05);
        }

        /* ========== MÉDIAS ========== */
        .media-highlight {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            padding: 60px 0;
            position: relative;
            overflow: hidden;
        }

        .media-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 40px;
            position: relative;
            z-index: 2;
        }

        .media-card {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px 30px;
            text-align: center;
            transition: var(--transition);
            border: 1px solid rgba(255,255,255,0.2);
        }

        .media-card:hover {
            transform: translateY(-15px);
            background: rgba(255,255,255,0.2);
        }

        .media-icon {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2.5rem;
            color: white;
        }

        .youtube-bg { background: var(--youtube); }
        .radio-bg { background: var(--radio); }
        .whatsapp-bg { background: var(--whatsapp); }

        .btn-media {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 12px 28px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            margin-top: 20px;
            transition: var(--transition);
            color: white;
        }

        .youtube-btn { background: var(--youtube); }
        .radio-btn { background: var(--radio); }
        .whatsapp-btn { background: var(--whatsapp); }

        .btn-media:hover {
            transform: scale(1.08);
            box-shadow: 0 5px 20px rgba(0,0,0,0.3);
        }

        .live-badge {
            display: inline-block;
            margin-top: 15px;
            padding: 5px 15px;
            background: #e74c3c;
            color: white;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: bold;
            animation: blink 1s infinite;
        }

        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        /* ========== JOURS DE CULTES ========== */
        .cultes-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
        }

        .culte-card {
            background: var(--white);
            border-radius: 20px;
            padding: 35px;
            text-align: center;
            box-shadow: 0 5px 15px var(--shadow);
            transition: var(--transition);
            border-top: 4px solid var(--gold);
        }

        .culte-card:hover {
            transform: translateY(-10px);
        }

        .culte-day {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--gold);
            margin-bottom: 10px;
        }

        .culte-time {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 20px;
        }

        .culte-description {
            color: var(--text-light);
            line-height: 1.8;
        }

        /* ========== GALERIE ========== */
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }

        .gallery-item {
            position: relative;
            overflow: hidden;
            border-radius: 15px;
            cursor: pointer;
            aspect-ratio: 1;
        }

        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: var(--transition);
        }

        .gallery-item:hover img {
            transform: scale(1.1);
        }

        .gallery-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0,0,0,0.7));
            padding: 20px;
            transform: translateY(100%);
            transition: var(--transition);
            color: white;
        }

        .gallery-item:hover .gallery-overlay {
            transform: translateY(0);
        }

        /* ========== APPLICATION SECTION ========== */
        .app-section {
            background: linear-gradient(135deg, var(--primary), #1a4a6e);
            color: white;
            padding: 60px 0;
        }

        .app-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 50px;
            flex-wrap: wrap;
        }

        .app-info {
            flex: 1;
        }

        .app-info h2 {
            font-size: 2rem;
            margin-bottom: 20px;
        }

        .app-info h2 span {
            color: var(--gold);
        }

        .app-info p {
            margin-bottom: 30px;
            opacity: 0.9;
        }

        .app-buttons {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .btn-app {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(10px);
            padding: 15px 30px;
            border-radius: 15px;
            text-decoration: none;
            color: white;
            transition: var(--transition);
            border: 1px solid rgba(255,255,255,0.2);
            cursor: pointer;
        }

        .btn-app:hover {
            background: var(--gold);
            color: var(--primary);
            transform: translateY(-5px);
        }

        .btn-app i {
            font-size: 2rem;
        }

        .btn-app span {
            font-size: 1.1rem;
            font-weight: 600;
        }

        .btn-app small {
            display: block;
            font-size: 0.7rem;
            opacity: 0.8;
        }

        .app-qr {
            text-align: center;
            background: white;
            padding: 20px;
            border-radius: 20px;
        }

        .app-qr img {
            width: 150px;
            height: 150px;
        }

        .app-qr p {
            margin-top: 10px;
            color: var(--primary);
            font-weight: 500;
        }

        /* ========== ANNONCES ========== */
        .announcements-container {
            max-width: 800px;
            margin: 0 auto;
        }

        .announcement-item {
            background: var(--white);
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 20px;
            border-left: 4px solid var(--gold);
            box-shadow: 0 2px 10px var(--shadow);
            transition: var(--transition);
        }

        .announcement-item:hover {
            transform: translateX(10px);
        }

        .announcement-title {
            font-weight: bold;
            font-size: 1.1rem;
            margin-bottom: 10px;
            color: var(--primary);
        }

        .announcement-date {
            font-size: 0.8rem;
            color: #999;
            margin-top: 10px;
        }

        /* ========== LOCALISATION ========== */
        #map {
            height: 400px;
            width: 100%;
            border-radius: 20px;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .gps-card {
            background: var(--white);
            padding: 15px 25px;
            border-radius: 10px;
            text-align: center;
            flex: 1;
            box-shadow: 0 2px 10px var(--shadow);
        }

        /* ========== SUPPORT ========== */
        .support-section {
            padding: 50px 0;
            background: var(--gray);
        }

        .support-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }

        .support-card {
            background: var(--white);
            padding: 20px;
            border-radius: 15px;
            text-align: center;
            transition: var(--transition);
        }

        .support-card:hover {
            transform: translateY(-5px);
        }

        .support-card i {
            font-size: 2rem;
            color: var(--whatsapp);
            margin-bottom: 10px;
            display: block;
        }

        /* ========== FOOTER ========== */
        .footer {
            background: var(--primary);
            color: #ccc;
            padding: 50px 0 20px;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            margin-bottom: 40px;
        }

        .footer-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }

        .footer-logo h3 {
            color: white;
            font-size: 1rem;
        }

        .social-links {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }

        .social-links a {
            width: 40px;
            height: 40px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            transition: var(--transition);
        }

        .social-links a:hover {
            background: var(--gold);
            color: var(--primary);
        }

        .footer-links h4, .footer-contact h4 {
            color: white;
            margin-bottom: 20px;
        }

        .footer-links ul {
            list-style: none;
        }

        .footer-links ul li {
            margin-bottom: 10px;
        }

        .footer-links a {
            color: #ccc;
            text-decoration: none;
        }

        .footer-links a:hover {
            color: var(--gold);
        }

        .footer-contact p {
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .footer-contact i {
            color: var(--gold);
        }

        .footer-bottom {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid rgba(255,255,255,0.1);
        }

        /* ========== MODAL ========== */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.8);
            z-index: 2000;
            justify-content: center;
            align-items: center;
        }
        .modal.show {
            display: flex;
        }
        .modal-content {
            background: var(--white);
            border-radius: 20px;
            width: 90%;
            max-width: 500px;
            padding: 30px;
            position: relative;
            max-height: 90vh;
            overflow-y: auto;
        }
        .modal-close {
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 1.5rem;
            cursor: pointer;
            color: #999;
        }
        .modal-close:hover {
            color: var(--primary);
        }
        .modal-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            border-bottom: 2px solid #eee;
        }
        .tab-btn {
            background: transparent;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            font-size: 1rem;
        }
        .tab-btn.active {
            color: var(--gold);
            border-bottom: 2px solid var(--gold);
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            background: var(--white);
            color: var(--text);
        }
        .form-row {
            display: flex;
            gap: 15px;
        }
        .form-group.half {
            flex: 1;
        }
        .form-group.checkbox {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .form-group.checkbox input {
            width: auto;
        }
        .btn-submit {
            width: 100%;
            background: var(--gold);
            color: var(--primary);
            border: none;
            padding: 12px;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            font-size: 1rem;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
        }
        .form-message {
            margin-top: 15px;
            padding: 10px;
            border-radius: 8px;
            display: none;
        }
        .form-message.success {
            background: #d4edda;
            color: #155724;
            display: block;
        }
        .form-message.error {
            background: #f8d7da;
            color: #721c24;
            display: block;
        }
        .form-message.info {
            background: #d1ecf1;
            color: #0c5460;
            display: block;
        }

        /* ========== CHAT FLOATING ========== */
        .chat-float {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 1000;
        }
        
        .chat-toggle {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #25D366, #128C7E);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
            transition: all 0.3s;
            color: white;
            font-size: 1.8rem;
            position: relative;
        }
        
        .chat-toggle:hover {
            transform: scale(1.1);
        }
        
        .chat-unread {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #e74c3c;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 0.7rem;
            min-width: 20px;
            text-align: center;
        }
        
        .chat-window {
            position: fixed;
            bottom: 100px;
            right: 30px;
            width: 350px;
            height: 500px;
            background: var(--white);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            display: none;
            flex-direction: column;
            z-index: 1001;
            overflow: hidden;
            border: 1px solid var(--gray-dark);
        }
        
        .chat-window.open {
            display: flex;
        }
        
        .chat-header {
            background: var(--primary);
            color: white;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .chat-header h3 {
            font-size: 1rem;
            margin: 0;
        }
        
        .chat-header .close-chat {
            cursor: pointer;
            font-size: 1.2rem;
            font-weight: bold;
        }
        
        .chat-messages-list {
            flex: 1;
            padding: 15px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 10px;
            background: var(--gray);
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
            padding: 8px 12px;
            border-radius: 15px;
            word-wrap: break-word;
        }
        
        .message.sent .message-bubble {
            background: var(--gold);
            color: var(--primary);
            border-bottom-right-radius: 3px;
        }
        
        .message.received .message-bubble {
            background: var(--white);
            color: var(--text);
            border-bottom-left-radius: 3px;
            box-shadow: 0 1px 2px var(--shadow);
        }
        
        .message-time {
            font-size: 0.65rem;
            color: var(--text-light);
            margin-top: 3px;
            margin-left: 5px;
        }
        
        .message.sent .message-time {
            text-align: right;
        }
        
        .chat-input {
            padding: 10px;
            border-top: 1px solid var(--gray-dark);
            display: flex;
            gap: 10px;
            background: var(--white);
        }
        
        .chat-input input {
            flex: 1;
            padding: 10px;
            border: 1px solid var(--gray-dark);
            border-radius: 25px;
            outline: none;
            background: var(--white);
            color: var(--text);
        }
        
        .chat-input button {
            background: var(--gold);
            border: none;
            padding: 10px 15px;
            border-radius: 25px;
            cursor: pointer;
            font-weight: bold;
        }

        /* ========== RESPONSIVE ========== */
        @media (max-width: 992px) {
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .media-grid { grid-template-columns: repeat(2, 1fr); }
            .cultes-grid { grid-template-columns: repeat(2, 1fr); }
            .support-grid { grid-template-columns: repeat(2, 1fr); }
            .library-grid { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 768px) {
            .menu-toggle { display: block; }
            nav ul { display: none; flex-direction: column; width: 100%; background: var(--primary); padding: 20px; position: absolute; top: 100%; left: 0; text-align: center; }
            nav ul.active { display: flex; }
            .nav-wrapper { position: relative; }
            .auth-buttons { display: none; }
            .datetime-bar { top: 70px; }
            .hero { margin-top: 110px; }
            .hero-content h1 { font-size: 1.8rem; }
            .hero-content p { font-size: 0.9rem; }
            .hero-buttons { flex-direction: column; align-items: center; }
            .btn-primary, .btn-outline { width: 100%; justify-content: center; }
            .stats-grid { grid-template-columns: 1fr; gap: 15px; }
            .library-grid { grid-template-columns: 1fr; }
            .media-grid { grid-template-columns: 1fr; }
            .cultes-grid { grid-template-columns: 1fr; }
            .support-grid { grid-template-columns: 1fr; }
            .pastor-grid { grid-template-columns: 1fr; }
            .app-content { flex-direction: column; text-align: center; }
            .app-buttons { justify-content: center; }
            .gallery-grid { grid-template-columns: repeat(2, 1fr); }
            .footer-grid { grid-template-columns: 1fr; text-align: center; }
            .footer-logo { justify-content: center; }
            .social-links { justify-content: center; }
            .footer-contact p { justify-content: center; }
            .form-row { flex-direction: column; }
            .chat-window { width: 90%; right: 5%; left: 5%; bottom: 80px; height: 450px; }
        }

        @media (max-width: 480px) {
            .container { width: 95%; }
            .hero-content h1 { font-size: 1.5rem; }
            .section-title h2 { font-size: 1.5rem; }
            .library-card { padding: 25px; }
            .culte-day { font-size: 1.3rem; }
            .modal-content { padding: 20px; }
            .gallery-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body class="<?php echo $theme; ?>">
    <!-- HEADER -->
    <header class="header">
        <div class="container">
            <div class="logo" onclick="window.location.href='#accueil'">
                <div class="logo-icon">
                    <?php if($logoPath): ?>
                        <img src="<?php echo $logoPath; ?>" alt="Logo Vision d'Aigles Tabernacle">
                    <?php else: ?>
                        <i class="fas fa-dove"></i>
                    <?php endif; ?>
                </div>
                <div class="logo-text">
                    <h1>VISION D'AIGLES</h1>
                    <span>TABERNACLE</span>
                </div>
            </div>
            <div class="nav-wrapper">
                <nav>
                    <ul id="navMenu">
                        <li><a href="#accueil">Accueil</a></li>
                        <li><a href="#pasteur">À propos</a></li>
                        <li><a href="#bibliotheque">Sermons</a></li>
                        <li><a href="#live">Médias</a></li>
                        <li><a href="#cultes">Cultes</a></li>
                        <li><a href="#gallery">Galerie</a></li>
                        <li><a href="#app">Application</a></li>
                        <li><a href="#contact">Contact</a></li>
                    </ul>
                </nav>
                <button class="theme-toggle-btn" id="themeToggleBtn">
                    <i class="fas <?php echo $theme === 'dark' ? 'fa-sun' : 'fa-moon'; ?>"></i>
                </button>
                <div class="auth-buttons">
                    <?php if($isLoggedIn): ?>
                        <a href="profile.php" class="profile-btn"><i class="fas fa-user"></i> <?php echo htmlspecialchars($userName); ?></a>
                        <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
                        <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                            <a href="admin/dashboard.php" class="login-btn"><i class="fas fa-crown"></i> Admin</a>
                        <?php endif; ?>
                    <?php else: ?>
                        <button class="login-btn" id="showLoginModal"><i class="fas fa-sign-in-alt"></i> Connexion</button>
                        <button class="register-btn" id="showRegisterModal"><i class="fas fa-user-plus"></i> Inscription</button>
                    <?php endif; ?>
                </div>
                <div class="menu-toggle" id="menuToggle">
                    <i class="fas fa-bars"></i>
                </div>
            </div>
        </div>
    </header>

    <!-- HORLOGE -->
    <div class="datetime-bar">
        <div class="container">
            <div class="datetime-content">
                <div class="date-display" id="currentDate">Chargement...</div>
                <div class="time-display" id="currentTime">--:--:--</div>
            </div>
        </div>
    </div>

    <!-- HERO -->
    <section id="accueil" class="hero">
        <div class="hero-content">
            <div class="hero-badge">Bienvenue à la maison</div>
            <h1><?php echo htmlspecialchars($hero_title); ?></h1>
            <p><?php echo nl2br(htmlspecialchars($hero_text)); ?></p>
            <div class="hero-buttons">
                <a href="#bibliotheque" class="btn-primary">
                    <i class="fas fa-headphones"></i> Accéder aux sermons
                </a>
                <a href="#live" class="btn-outline">
                    <i class="fab fa-youtube"></i> Regarder en Direct
                </a>
            </div>
        </div>
        <div class="scroll-indicator">
            <span>Découvrir</span>
            <i class="fas fa-chevron-down"></i>
        </div>
    </section>

    <!-- STATISTIQUES -->
    <section class="stats-counter">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-icon"><i class="fas fa-users"></i></div>
                    <span class="stat-number" id="visitorsCount"><?php echo $stats['visitors_count'] ?? 0; ?></span>
                    <span class="stat-label">Visiteurs</span>
                </div>
                <div class="stat-item">
                    <div class="stat-icon"><i class="fas fa-user-check"></i></div>
                    <span class="stat-number" id="subscribersCount"><?php echo count($data['subscribers'] ?? []); ?></span>
                    <span class="stat-label">Abonnés</span>
                </div>
                <div class="stat-item">
                    <div class="stat-icon"><i class="fas fa-music"></i></div>
                    <span class="stat-number" id="sermonsCount"><?php echo ($stats['sermons_count_mega_manual'] ?? 0) + ($stats['sermons_count_degoo_manual'] ?? 0); ?></span>
                    <span class="stat-label">Sermons</span>
                </div>
                <div class="stat-item">
                    <div class="stat-icon"><i class="fas fa-clock"></i></div>
                    <span class="stat-number">24/7</span>
                    <span class="stat-label">En ligne</span>
                </div>
            </div>
        </div>
    </section>

    <!-- PASTEUR -->
    <section id="pasteur" class="section">
        <div class="container">
            <div class="section-title">
                <div class="section-label">Notre Pasteur</div>
                <h2>Rencontrez <span>le Pasteur</span></h2>
                <div class="divider"></div>
            </div>
            <div class="pastor-grid">
                <div class="pastor-image">
                    <img src="assets/images/pasteur.jpg" alt="Pasteur Arthur Gédéon Mouzita Mayoulou" onerror="this.src='https://via.placeholder.com/500x500?text=Pasteur'">
                    <div style="text-align: center; margin-top: 20px;">
                        <a href="https://wa.me/242066293093" target="_blank" class="whatsapp-btn">
                            <i class="fab fa-whatsapp"></i> Contacter le Pasteur
                        </a>
                    </div>
                </div>
                <div class="pastor-content">
                    <h3>Pasteur <strong><?php echo htmlspecialchars($pastor_name); ?></strong></h3>
                    <div class="pastor-title"><?php echo htmlspecialchars($pastor_title); ?></div>
                    <p><?php echo nl2br(htmlspecialchars($pastor_bio)); ?> <strong>« Ayez foi en Dieu »</strong>, tel est son message constant.</p>
                    <div class="pastor-quote">
                        <i class="fas fa-quote-left"></i>
                        <p><?php echo htmlspecialchars($pastor_quote); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- BIBLIOTHÈQUE -->
    <section id="bibliotheque" class="section section-gray">
        <div class="container">
            <div class="section-title">
                <div class="section-label">Ressources</div>
                <h2>Bibliothèque <span>Audio</span></h2>
                <div class="divider"></div>
                <p>Accédez à tous nos sermons et enseignements</p>
            </div>
            <div class="library-grid">
                <div class="library-card">
                    <div class="library-icon audio-bg">
                        <i class="fas fa-headphones"></i>
                    </div>
                    <h3>Bibliothèque Audio</h3>
                    <div class="library-stats">
                        <span class="library-count"><?php echo ($stats['sermons_count_mega_manual'] ?? 0) + ($stats['sermons_count_degoo_manual'] ?? 0); ?></span>
                        <span>sermons disponibles</span>
                    </div>
                    <p>Accédez à tous nos sermons sur notre plateforme audio</p>
                    <a href="https://audio.com/vision-daigles-tabernacle-pn" target="_blank" class="btn-library audio-btn">
                        <i class="fas fa-download"></i> Accéder à la bibliothèque
                    </a>
                </div>
                <div class="library-card">
                    <div class="library-icon mega-bg">
                        <i class="fas fa-cloud-upload-alt"></i>
                    </div>
                    <h3>Bibliothèque MEGA</h3>
                    <div class="library-stats">
                        <span class="library-count" id="megaCount"><?php echo $stats['sermons_count_mega_manual'] ?? 0; ?></span>
                        <span>sermons</span>
                    </div>
                    <p>Accédez à notre collection MEGA</p>
                    <a href="#" class="btn-library mega-btn" onclick="alert('Lien MEGA à venir')">
                        <i class="fas fa-database"></i> Accéder
                    </a>
                </div>
                <div class="library-card">
                    <div class="library-icon degoo-bg">
                        <i class="fas fa-cloud-download-alt"></i>
                    </div>
                    <h3>Bibliothèque Degoo</h3>
                    <div class="library-stats">
                        <span class="library-count" id="degooCount"><?php echo $stats['sermons_count_degoo_manual'] ?? 0; ?></span>
                        <span>sermons</span>
                    </div>
                    <p>Accédez à notre collection Degoo</p>
                    <a href="#" class="btn-library degoo-btn" onclick="alert('Lien Degoo à venir')">
                        <i class="fas fa-database"></i> Accéder
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- MÉDIAS -->
    <section id="live" class="media-highlight">
        <div class="container">
            <div class="section-title">
                <div class="section-label">Suivez-nous en ligne</div>
                <h2>Restez <span>connectés</span></h2>
                <div class="divider"></div>
                <p>Rejoignez-nous sur nos différentes plateformes</p>
            </div>
            <div class="media-grid">
                <div class="media-card">
                    <div class="media-icon youtube-bg"><i class="fab fa-youtube"></i></div>
                    <h3>YouTube Live</h3>
                    <p>Suivez nos cultes en direct</p>
                    <a href="https://www.youtube.com/@AyezFoienDieu" target="_blank" class="btn-media youtube-btn"><i class="fab fa-youtube"></i> Regarder</a>
                    <div class="live-badge">EN DIRECT</div>
                </div>
                <div class="media-card">
                    <div class="media-icon radio-bg"><i class="fas fa-tower-broadcast"></i></div>
                    <h3>Radio en Ligne</h3>
                    <p>24h/24 d'édification</p>
                    <a href="https://vateglise.ismyradio.com/player" target="_blank" class="btn-media radio-btn"><i class="fas fa-headphones"></i> Écouter</a>
                    <div class="live-badge">24/7</div>
                </div>
                <div class="media-card">
                    <div class="media-icon whatsapp-bg"><i class="fab fa-whatsapp"></i></div>
                    <h3>Groupe WhatsApp</h3>
                    <p>Rejoignez la communauté</p>
                    <a href="https://chat.whatsapp.com/HCikWDquIvw4qNfDGjErRC" target="_blank" class="btn-media whatsapp-btn"><i class="fab fa-whatsapp"></i> Rejoindre</a>
                    <div class="live-badge">500+ membres</div>
                </div>
            </div>
        </div>
    </section>

    <!-- JOURS DE CULTES -->
    <section id="cultes" class="section">
        <div class="container">
            <div class="section-title">
                <div class="section-label">Les Services</div>
                <h2>Nos <span>Jours de Cultes</span></h2>
                <div class="divider"></div>
            </div>
            <div class="cultes-grid">
                <?php foreach($cultesList as $culte): ?>
                <div class="culte-card">
                    <div class="culte-day">🕊️ <?php echo htmlspecialchars($culte['day']); ?></div>
                    <div class="culte-time"><?php echo htmlspecialchars($culte['time']); ?></div>
                    <div class="culte-description">
                        <?php echo nl2br(htmlspecialchars($culte['description'])); ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- GALERIE PHOTO -->
    <section id="gallery" class="section section-gray">
        <div class="container">
            <div class="section-title">
                <div class="section-label">Souvenirs</div>
                <h2>Galerie <span>Photo</span></h2>
                <div class="divider"></div>
            </div>
            <div class="gallery-grid" id="galleryGrid">
                <?php if(empty($gallery)): ?>
                    <div style="text-align: center; grid-column: 1/-1; padding: 50px;">
                        <i class="fas fa-images" style="font-size: 3rem; color: var(--gold);"></i>
                        <p>Aucune photo disponible pour le moment.</p>
                    </div>
                <?php else: ?>
                    <?php foreach(array_slice($gallery, 0, 6) as $image): ?>
                        <div class="gallery-item" onclick="openImageModal('assets/uploads/gallery/<?php echo $image['filename']; ?>')">
                            <img src="assets/uploads/gallery/<?php echo $image['filename']; ?>" alt="<?php echo htmlspecialchars($image['title']); ?>">
                            <div class="gallery-overlay">
                                <h4><?php echo htmlspecialchars($image['title']); ?></h4>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <?php if(count($gallery) > 6): ?>
                <div style="text-align: center; margin-top: 30px;">
                    <a href="gallery.php" class="btn-primary">Voir toute la galerie</a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- APPLICATION -->
    <section id="app" class="app-section">
        <div class="container">
            <div class="app-content">
                <div class="app-info">
                    <h2>Téléchargez <span>notre application</span></h2>
                    <p>Accédez à tous nos contenus hors ligne : sermons, cantiques, Bible et bien plus encore. L'application se met à jour automatiquement lorsqu'une connexion est détectée.</p>
                    <div class="app-buttons">
                        <!-- LIEN DE TÉLÉCHARGEMENT DIRECT AJOUTÉ -->
                        <a href="<?php echo $appDownloadLink; ?>" class="btn-app" target="_blank" rel="noopener noreferrer">
                            <i class="fas fa-download"></i>
                            <div>
                                <span>Télécharger l'application</span>
                                <small>Version officielle - APK</small>
                            </div>
                        </a>
                        <?php if(!empty($apkPath) && file_exists($apkPath)): ?>
                            <a href="<?php echo htmlspecialchars($apkPath); ?>" class="btn-app" download>
                                <i class="fas fa-database"></i>
                                <div>
                                    <span>Télécharger l'APK (miroir)</span>
                                    <small>Version v<?php echo htmlspecialchars($appVersion); ?></small>
                                </div>
                            </a>
                        <?php endif; ?>
                        <button class="btn-app" onclick="checkForUpdates()">
                            <i class="fas fa-sync-alt"></i>
                            <div>
                                <span>Vérifier les mises à jour</span>
                                <small id="appVersion">v<?php echo htmlspecialchars($appVersion); ?></small>
                            </div>
                        </button>
                    </div>
                    <p style="margin-top: 20px; font-size: 0.85rem;"><i class="fas fa-info-circle"></i> L'application fonctionne hors ligne et se synchronise automatiquement en ligne.</p>
                </div>
                <div class="app-qr">
                    <!-- QR code redirigeant vers le lien de téléchargement -->
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?php echo urlencode($appDownloadLink); ?>" alt="QR Code de téléchargement">
                    <p>Scannez pour télécharger</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ANNONCES -->
    <section id="annonces" class="section">
        <div class="container">
            <div class="section-title">
                <div class="section-label">Actualités</div>
                <h2>Dernières <span>Annonces</span></h2>
                <div class="divider"></div>
            </div>
            <div class="announcements-container" id="announcementsList">
                <?php if(empty($latestAnnouncements)): ?>
                    <p style="text-align:center;">Aucune annonce pour le moment.</p>
                <?php else: ?>
                    <?php foreach($latestAnnouncements as $announcement): ?>
                        <div class="announcement-item">
                            <div class="announcement-title">📢 <?php echo htmlspecialchars($announcement['title']); ?></div>
                            <div><?php echo nl2br(htmlspecialchars($announcement['content'])); ?></div>
                            <div class="announcement-date">📅 <?php echo date('d/m/Y', strtotime($announcement['created_at'])); ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- LOCALISATION -->
    <section id="localisation" class="section section-gray">
        <div class="container">
            <div class="section-title">
                <div class="section-label">Nous trouver</div>
                <h2>Notre <span>Localisation</span></h2>
                <div class="divider"></div>
                <p>Quartier Voungou, Pointe-Noire, République du Congo</p>
            </div>
            <div id="map"></div>
            <div style="display: flex; gap: 20px; justify-content: center; flex-wrap: wrap;">
                <div class="gps-card">
                    <i class="fas fa-map-marker-alt" style="color: var(--gold); font-size: 1.5rem;"></i>
                    <p><strong>Adresse</strong><br>Quartier Voungou, Pointe-Noire, Congo</p>
                </div>
                <div class="gps-card">
                    <i class="fas fa-code-branch" style="color: var(--gold); font-size: 1.5rem;"></i>
                    <p><strong>Code GPS</strong><br>6W54+45, Pointe-Noire</p>
                </div>
                <div class="gps-card">
                    <i class="fas fa-road" style="color: var(--gold); font-size: 1.5rem;"></i>
                    <p><strong>Itinéraire</strong><br><a href="https://maps.google.com/?q=6W54%2B45+Pointe-Noire" target="_blank" style="color: var(--gold);">Ouvrir dans Google Maps</a></p>
                </div>
            </div>
        </div>
    </section>

    <!-- SUPPORT -->
    <section id="support" class="support-section">
        <div class="container">
            <div class="section-title">
                <div class="section-label">Aide</div>
                <h2>Support <span>Technique</span></h2>
                <div class="divider"></div>
            </div>
            <div class="support-grid">
                <div class="support-card">
                    <i class="fab fa-whatsapp"></i>
                    <strong>Fr Bosley</strong>
                    <a href="https://wa.me/242066119323">+242 06 611 93 23</a>
                </div>
                <div class="support-card">
                    <i class="fab fa-whatsapp"></i>
                    <strong>Fr Jabien</strong>
                    <a href="https://wa.me/242055684041">+242 05 568 40 41</a>
                </div>
                <div class="support-card">
                    <i class="fab fa-whatsapp"></i>
                    <strong>Fr Kelly</strong>
                    <a href="https://wa.me/242064221426">+242 06 422 14 26</a>
                </div>
                <div class="support-card">
                    <i class="fab fa-whatsapp"></i>
                    <strong>Fr Timothée</strong>
                    <a href="https://wa.me/242068722292">+242 06 872 22 92</a>
                </div>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="footer" id="contact">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-info">
                    <div class="footer-logo">
                        <?php if($logoPath): ?>
                            <img src="<?php echo $logoPath; ?>" alt="Logo" style="width: 40px; height: 40px; border-radius: 50%;">
                        <?php else: ?>
                            <i class="fas fa-dove" style="font-size: 2rem; color: var(--gold);"></i>
                        <?php endif; ?>
                        <h3>VISION D'AIGLES TABERNACLE</h3>
                    </div>
                    <p>Ministère Prophétique partageant la Parole de Vie et manifestant l'amour de Christ à travers des actions sociales et missionnaires.</p>
                    <div class="social-links">
                        <a href="https://www.youtube.com/@AyezFoienDieu" target="_blank"><i class="fab fa-youtube"></i></a>
                        <a href="https://chat.whatsapp.com/HCikWDquIvw4qNfDGjErRC" target="_blank"><i class="fab fa-whatsapp"></i></a>
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                <div class="footer-links">
                    <h4>Liens rapides</h4>
                    <ul>
                        <li><a href="#accueil">Accueil</a></li>
                        <li><a href="#pasteur">À propos</a></li>
                        <li><a href="#bibliotheque">Sermons</a></li>
                        <li><a href="#live">Médias</a></li>
                        <li><a href="#cultes">Cultes</a></li>
                        <li><a href="#gallery">Galerie</a></li>
                        <li><a href="#app">Application</a></li>
                    </ul>
                </div>
                <div class="footer-contact">
                    <h4>Contact</h4>
                    <p><i class="fas fa-map-marker-alt"></i> Quartier Voungou, Pointe-Noire, Congo</p>
                    <p><i class="fas fa-envelope"></i> visiondaigles.tab1@gmail.com</p>
                    <p><i class="fab fa-whatsapp"></i> +242 06 629 30 93</p>
                    <p><i class="fas fa-clock"></i> Lun-Ven: 9h-17h | Sam: 9h-13h</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2026 Vision d'Aigles Tabernacle. Tous droits réservés.</p>
                <p>Développé pour la gloire de Dieu</p>
            </div>
        </div>
    </footer>

    <!-- MODAL AUTHENTIFICATION -->
    <div id="authModal" class="modal">
        <div class="modal-content">
            <span class="modal-close">&times;</span>
            <div class="modal-tabs">
                <button class="tab-btn active" data-tab="login">Connexion</button>
                <button class="tab-btn" data-tab="register">Inscription</button>
            </div>
            <div id="loginTab" class="tab-content active">
                <form id="loginFormAjax">
                    <div class="form-group">
                        <input type="email" id="loginEmailAjax" placeholder="Votre email" required>
                    </div>
                    <div class="form-group">
                        <input type="password" id="loginPasswordAjax" placeholder="Mot de passe" required>
                    </div>
                    <button type="submit" class="btn-submit">Se connecter</button>
                    <div id="loginMessageAjax" class="form-message"></div>
                </form>
            </div>
            <div id="registerTab" class="tab-content">
                <form id="registerFormAjax" enctype="multipart/form-data">
                    <div class="form-row">
                        <div class="form-group half">
                            <input type="text" id="regFirstnameAjax" placeholder="Prénom" required>
                        </div>
                        <div class="form-group half">
                            <input type="text" id="regLastnameAjax" placeholder="Nom" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <input type="email" id="regEmailAjax" placeholder="Email" required>
                    </div>
                    <div class="form-group">
                        <input type="tel" id="regPhoneAjax" placeholder="Téléphone WhatsApp" required>
                    </div>
                    <div class="form-group">
                        <input type="password" id="regPasswordAjax" placeholder="Mot de passe" required>
                        <small>Minimum 6 caractères</small>
                    </div>
                    <div class="form-group checkbox">
                        <input type="checkbox" id="regNewsletterAjax" checked>
                        <label>Recevoir la newsletter</label>
                    </div>
                    <button type="submit" class="btn-submit">S'inscrire</button>
                    <div id="registerMessageAjax" class="form-message"></div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL IMAGE -->
    <div id="imageModal" class="modal" onclick="closeImageModal()">
        <div class="modal-content" style="max-width: 90%; padding: 0; background: transparent;" onclick="event.stopPropagation()">
            <span class="modal-close" style="color: white; right: -40px; top: -40px;">&times;</span>
            <img id="modalImage" style="width: 100%; height: auto; border-radius: 10px;">
        </div>
    </div>

    <!-- CHAT FLOATING POUR UTILISATEURS CONNECTÉS -->
    <?php if($isLoggedIn): ?>
    <div class="chat-float">
        <div class="chat-toggle" id="chatToggleBtn">
            <i class="fas fa-comment-dots"></i>
            <span class="chat-unread" id="chatUnreadCount" style="display: none;">0</span>
        </div>
        <div class="chat-window" id="chatWindow">
            <div class="chat-header">
                <h3><i class="fas fa-headset"></i> Support technique</h3>
                <span class="close-chat" id="closeChatBtn">&times;</span>
            </div>
            <div class="chat-messages-list" id="chatMessagesList">
                <div style="text-align: center; padding: 20px;">Chargement des messages...</div>
            </div>
            <div class="chat-input">
                <input type="text" id="chatMessageInput" placeholder="Écrivez votre message..." autocomplete="off">
                <button id="chatSendMessageBtn"><i class="fas fa-paper-plane"></i> Envoyer</button>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // ========== INITIALISATION ==========
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM chargé - Initialisation');
            initMap();
            updateDateTime(); // Appel immédiat
            setInterval(updateDateTime, 1000);
            initScrollEffects();
            initMobileMenu();
            initThemeToggle();
            initModal();
            initAuthForms();
            loadStats();
            initChat();
        });

        // ========== DATE ET HEURE (CORRIGÉ) ==========
        function updateDateTime() {
            try {
                const now = new Date();
                
                // Format de la date: "Mercredi 3 juin 2026"
                const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
                let dateStr = now.toLocaleDateString('fr-FR', options);
                dateStr = dateStr.charAt(0).toUpperCase() + dateStr.slice(1);
                
                // Format de l'heure: "14:30:45"
                const timeStr = now.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                
                const dateElement = document.getElementById('currentDate');
                const timeElement = document.getElementById('currentTime');
                
                if(dateElement) dateElement.textContent = dateStr;
                if(timeElement) timeElement.textContent = timeStr;
                
                console.log('Date/Heure mise à jour:', dateStr, timeStr);
            } catch(e) {
                console.error('Erreur updateDateTime:', e);
            }
        }

        // ========== CARTE GPS ==========
        function initMap() {
            try {
                const churchLat = -4.7691;
                const churchLng = 11.8664;
                const map = L.map('map').setView([churchLat, churchLng], 15);
                L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="https://carto.com/attributions">CARTO</a>',
                    maxZoom: 19
                }).addTo(map);
                const marker = L.marker([churchLat, churchLng]).addTo(map);
                marker.bindPopup("<b>Vision d'Aigles Tabernacle</b><br>Quartier Voungou, Pointe-Noire<br>République du Congo").openPopup();
            } catch(e) {
                console.error('Erreur carte:', e);
            }
        }

        // ========== SCROLL HEADER ==========
        function initScrollEffects() {
            const header = document.querySelector('.header');
            window.addEventListener('scroll', () => {
                if(window.scrollY > 50) header.classList.add('scrolled');
                else header.classList.remove('scrolled');
            });
            
            const scrollIndicator = document.querySelector('.scroll-indicator');
            if(scrollIndicator) {
                scrollIndicator.addEventListener('click', () => {
                    window.scrollTo({ top: window.innerHeight, behavior: 'smooth' });
                });
            }
            
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    const href = this.getAttribute('href');
                    if(href !== "#" && href !== "#" && href !== "") {
                        const target = document.querySelector(href);
                        if(target) {
                            e.preventDefault();
                            target.scrollIntoView({ behavior: 'smooth' });
                        }
                    }
                });
            });
        }

        // ========== MENU MOBILE ==========
        function initMobileMenu() {
            const menuToggle = document.getElementById('menuToggle');
            const navMenu = document.getElementById('navMenu');
            if(menuToggle && navMenu) {
                menuToggle.addEventListener('click', () => { 
                    navMenu.classList.toggle('active'); 
                });
            }
        }

        // ========== THÈME CLAIR/SOMBRE ==========
        function initThemeToggle() {
            const themeToggleBtn = document.getElementById('themeToggleBtn');
            const body = document.body;
            if(themeToggleBtn) {
                themeToggleBtn.addEventListener('click', () => {
                    if(body.classList.contains('dark')) {
                        body.classList.remove('dark');
                        setCookie('theme', 'light', 365);
                        themeToggleBtn.innerHTML = '<i class="fas fa-moon"></i>';
                    } else {
                        body.classList.add('dark');
                        setCookie('theme', 'dark', 365);
                        themeToggleBtn.innerHTML = '<i class="fas fa-sun"></i>';
                    }
                });
            }
        }

        function setCookie(name, value, days) {
            const date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            document.cookie = `${name}=${value}; expires=${date.toUTCString()}; path=/`;
        }

        // ========== MODAL ==========
        function initModal() {
            const modal = document.getElementById('authModal');
            const closeBtn = document.querySelector('.modal-close');
            const showLoginBtn = document.getElementById('showLoginModal');
            const showRegisterBtn = document.getElementById('showRegisterModal');
            
            if(showLoginBtn) {
                showLoginBtn.addEventListener('click', () => {
                    document.querySelector('.tab-btn[data-tab="login"]').click();
                    modal.classList.add('show');
                });
            }
            
            if(showRegisterBtn) {
                showRegisterBtn.addEventListener('click', () => {
                    document.querySelector('.tab-btn[data-tab="register"]').click();
                    modal.classList.add('show');
                });
            }
            
            if(closeBtn) {
                closeBtn.addEventListener('click', () => modal.classList.remove('show'));
            }
            
            window.addEventListener('click', (e) => {
                if(e.target === modal) modal.classList.remove('show');
            });
            
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const tab = btn.dataset.tab;
                    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                    document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
                    btn.classList.add('active');
                    document.getElementById(`${tab}Tab`).classList.add('active');
                });
            });
        }

        function openImageModal(src) {
            const modal = document.getElementById('imageModal');
            const modalImg = document.getElementById('modalImage');
            if(modal && modalImg) {
                modal.classList.add('show');
                modalImg.src = src;
            }
        }

        function closeImageModal() {
            const modal = document.getElementById('imageModal');
            if(modal) modal.classList.remove('show');
        }

        // ========== FORMULAIRES AUTH ==========
        function initAuthForms() {
            // Login
            const loginForm = document.getElementById('loginFormAjax');
            if(loginForm) {
                loginForm.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    const email = document.getElementById('loginEmailAjax').value;
                    const password = document.getElementById('loginPasswordAjax').value;
                    const messageDiv = document.getElementById('loginMessageAjax');
                    
                    try {
                        const response = await fetch('php/login.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ email, password, action: 'login' })
                        });
                        const result = await response.json();
                        messageDiv.className = 'form-message';
                        if(result.success) {
                            messageDiv.classList.add('success');
                            messageDiv.textContent = 'Connexion réussie ! Redirection...';
                            setTimeout(() => window.location.reload(), 1000);
                        } else {
                            messageDiv.classList.add('error');
                            messageDiv.textContent = result.message || 'Erreur de connexion';
                        }
                    } catch(error) {
                        messageDiv.classList.add('error');
                        messageDiv.textContent = 'Erreur de connexion au serveur';
                    }
                });
            }
            
            // Register
            const registerForm = document.getElementById('registerFormAjax');
            if(registerForm) {
                registerForm.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    const formData = new FormData();
                    formData.append('first_name', document.getElementById('regFirstnameAjax').value);
                    formData.append('last_name', document.getElementById('regLastnameAjax').value);
                    formData.append('email', document.getElementById('regEmailAjax').value);
                    formData.append('phone', document.getElementById('regPhoneAjax').value);
                    formData.append('password', document.getElementById('regPasswordAjax').value);
                    formData.append('newsletter', document.getElementById('regNewsletterAjax').checked ? 'on' : '');
                    formData.append('action', 'register');
                    
                    const messageDiv = document.getElementById('registerMessageAjax');
                    try {
                        const response = await fetch('php/subscribe.php', { method: 'POST', body: formData });
                        const result = await response.json();
                        messageDiv.className = 'form-message';
                        if(result.success) {
                            messageDiv.classList.add('success');
                            messageDiv.textContent = result.message;
                            registerForm.reset();
                            setTimeout(() => document.querySelector('.tab-btn[data-tab="login"]').click(), 2000);
                        } else {
                            messageDiv.classList.add('error');
                            messageDiv.textContent = result.message;
                        }
                    } catch(error) {
                        messageDiv.classList.add('error');
                        messageDiv.textContent = 'Erreur de connexion';
                    }
                });
            }
        }

        // ========== STATS ==========
        async function loadStats() {
            try {
                const response = await fetch('php/get-stats.php');
                const data = await response.json();
                if(data.success) {
                    const visitorsEl = document.getElementById('visitorsCount');
                    const subscribersEl = document.getElementById('subscribersCount');
                    const sermonsEl = document.getElementById('sermonsCount');
                    
                    if(visitorsEl) visitorsEl.innerText = data.visitors || 0;
                    if(subscribersEl) subscribersEl.innerText = data.subscribers || 0;
                    if(sermonsEl) sermonsEl.innerText = data.total_sermons || 0;
                }
            } catch(e) { console.error('Erreur loadStats:', e); }
        }

        // ========== VÉRIFICATION MISE À JOUR ==========
        async function checkForUpdates() {
            try {
                const response = await fetch('php/get-app-info.php');
                const data = await response.json();
                if(data.success) {
                    const currentVersion = "<?php echo $appVersion; ?>";
                    const latestVersion = data.version;
                    if(currentVersion !== latestVersion) {
                        alert(`✅ Nouvelle version disponible !\n\nVersion actuelle : v${currentVersion}\nNouvelle version : v${latestVersion}`);
                    } else {
                        alert(`✅ Vous utilisez la dernière version (v${currentVersion}).`);
                    }
                }
            } catch(error) {
                alert('Impossible de vérifier les mises à jour.');
            }
        }

        // ========== CHAT ==========
        function initChat() {
            <?php if($isLoggedIn): ?>
            const myUserId = <?php echo $userId; ?>;
            const adminId = 1;
            const chatToggle = document.getElementById('chatToggleBtn');
            const chatWindow = document.getElementById('chatWindow');
            const closeChat = document.getElementById('closeChatBtn');
            const sendBtn = document.getElementById('chatSendMessageBtn');
            const messageInput = document.getElementById('chatMessageInput');
            const messagesContainer = document.getElementById('chatMessagesList');
            const unreadBadge = document.getElementById('chatUnreadCount');
            
            function escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }
            
            function formatTime(dateString) {
                const date = new Date(dateString);
                return date.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
            }
            
            async function loadMessages() {
                if(!messagesContainer) return;
                try {
                    const response = await fetch('php/chat-get-messages.php?user_id=' + adminId + '&t=' + Date.now());
                    const data = await response.json();
                    
                    if(data.success && data.messages) {
                        if(data.messages.length === 0) {
                            messagesContainer.innerHTML = '<div style="text-align: center; padding: 20px; color: #999;">Aucun message. Commencez la conversation !</div>';
                            if(unreadBadge) unreadBadge.style.display = 'none';
                            return;
                        }
                        
                        let unreadCount = 0;
                        const messagesHtml = data.messages.map(msg => {
                            if(msg.sender_id !== myUserId && !msg.is_read) unreadCount++;
                            return `
                                <div class="message ${msg.sender_id === myUserId ? 'sent' : 'received'}">
                                    <div class="message-bubble">${escapeHtml(msg.message)}</div>
                                    <div class="message-time">${formatTime(msg.created_at)} ${msg.sender_id === myUserId ? (msg.is_read ? '✓✓' : '✓') : ''}</div>
                                </div>
                            `;
                        }).join('');
                        
                        messagesContainer.innerHTML = messagesHtml;
                        messagesContainer.scrollTop = messagesContainer.scrollHeight;
                        
                        if(unreadCount > 0 && chatWindow && !chatWindow.classList.contains('open')) {
                            if(unreadBadge) {
                                unreadBadge.style.display = 'block';
                                unreadBadge.textContent = unreadCount;
                            }
                        } else if(unreadBadge) {
                            unreadBadge.style.display = 'none';
                        }
                        
                        if(chatWindow && chatWindow.classList.contains('open') && unreadCount > 0) {
                            await markAsRead();
                        }
                    }
                } catch(error) {
                    console.error('Erreur chargement messages:', error);
                }
            }
            
            async function sendMessage() {
                const message = messageInput.value.trim();
                if(!message) {
                    alert('Veuillez écrire un message');
                    return;
                }
                
                if(sendBtn) {
                    sendBtn.disabled = true;
                    sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Envoi...';
                }
                
                try {
                    const response = await fetch('php/chat-send.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ to_user_id: adminId, message: message })
                    });
                    const data = await response.json();
                    
                    if(data.success) {
                        if(messageInput) messageInput.value = '';
                        await loadMessages();
                    } else {
                        alert('Erreur: ' + (data.message || 'Envoi impossible'));
                    }
                } catch(error) {
                    console.error('Erreur envoi:', error);
                    alert('Erreur de connexion. Veuillez réessayer.');
                } finally {
                    if(sendBtn) {
                        sendBtn.disabled = false;
                        sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Envoyer';
                    }
                }
            }
            
            async function markAsRead() {
                try {
                    await fetch('php/chat-mark-read.php?user_id=' + adminId);
                    if(unreadBadge) unreadBadge.style.display = 'none';
                } catch(error) {
                    console.error('Erreur marquage lu:', error);
                }
            }
            
            if(chatToggle && chatWindow) {
                chatToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    chatWindow.classList.toggle('open');
                    if(chatWindow.classList.contains('open')) {
                        markAsRead();
                        loadMessages();
                    }
                });
            }
            
            if(closeChat && chatWindow) {
                closeChat.addEventListener('click', function() {
                    chatWindow.classList.remove('open');
                });
            }
            
            if(sendBtn && messageInput) {
                sendBtn.addEventListener('click', sendMessage);
                messageInput.addEventListener('keypress', function(e) {
                    if(e.key === 'Enter') {
                        e.preventDefault();
                        sendMessage();
                    }
                });
            }
            
            loadMessages();
            setInterval(loadMessages, 5000);
            <?php endif; ?>
        }
    </script>
</body>
</html>
