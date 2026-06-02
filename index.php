<?php
session_start();
require_once 'config/database.php';

$isLoggedIn = isset($_SESSION['user_id']) && isset($_SESSION['user_email']);
$userId = $_SESSION['user_id'] ?? 0;

// Récupérer les données depuis la base de données
$data = $pdo->getData();
$stats = $data['stats'];
$siteSettings = $data['site_settings'] ?? [];
$homepage = $siteSettings['homepage'] ?? [];
$cultesList = $siteSettings['cultes'] ?? [];
$appSettings = $siteSettings['app'] ?? ['version' => '1.0.0', 'apk_path' => '', 'changelog' => ''];

// Valeurs par défaut des paramètres du site
$hero_title = $homepage['hero_title'] ?? "Vision d'Aigles Tabernacle";
$hero_text = $homepage['hero_text'] ?? "« À aucun instant je n'apporte aux gens un message pour les pousser à me suivre... » — Rév. William Marrion Branham";
$pastor_name = $homepage['pastor_name'] ?? "ARTHUR GÉDÉON MOUZITA MAYOULOU";
$pastor_title = $homepage['pastor_title'] ?? "Fondateur & Pasteur Principal";
$pastor_bio = $homepage['pastor_bio'] ?? "Homme de Dieu, Carismatique, Visionnaire et Dévoué au Travail du Seigneur JÉSUS-CHRIST.";
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
$theme = 'light';
if(isset($_COOKIE['theme'])) {
    $theme = $_COOKIE['theme'];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title><?php echo htmlspecialchars($hero_title); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
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
            --mega: #1A2B4C;
            --degoo: #FF6B35;
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

        .announcement-btn {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 50px;
            cursor: pointer;
            transition: var(--transition);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .announcement-btn:hover {
            transform: scale(1.05);
        }

        .support-btn {
            background: linear-gradient(135deg, #25D366, #128C7E);
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 50px;
            cursor: pointer;
            transition: var(--transition);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .support-btn:hover {
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
        }

        .hero-slider {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }

        .slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-size: cover;
            background-position: center;
            opacity: 0;
            transition: opacity 1s;
        }

        .slide.active {
            opacity: 1;
        }

        .slide-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(0,0,0,0.85), rgba(0,0,0,0.7));
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

        /* ========== BIBLIOTHÈQUE (MODIFIÉE) ========== */
        .library-grid {
            display: grid;
            grid-template-columns: 1fr; /* UNE SEULE COLONNE */
            gap: 40px;
            max-width: 600px;
            margin: 0 auto;
        }

        .library-card {
            background: var(--white);
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 5px 15px var(--shadow);
            transition: var(--transition);
            position: relative;
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
            transition: var(--transition);
        }

        .library-card:hover .library-icon {
            transform: scale(1.1);
        }

        /* Nouvelle couleur pour le bloc unifié (vous pouvez la personnaliser) */
        .audio-bg {
            background: linear-gradient(135deg, #1A2B4C, #2C3E66);
        }

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
            border: none;
            cursor: pointer;
        }

        .audio-btn {
            background: linear-gradient(135deg, #1A2B4C, #2C3E66);
            color: white;
        }

        .btn-library:hover {
            transform: scale(1.05);
        }

        /* ========== MÉDIAS EN AVANT ========== */
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
            transition: var(--transition);
        }

        .media-card:hover .media-icon {
            transform: scale(1.1);
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
            font-size: 1rem;
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

        /* ========== GPS SECTION ========== */
        .gps-card {
            background: var(--white);
            padding: 15px 25px;
            border-radius: 10px;
            text-align: center;
            flex: 1;
        }

        /* ========== CHAT FLOATING BUTTON ========== */
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
        
        .chat-header .close-chat:hover {
            opacity: 0.8;
        }
        
        .chat-messages {
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
        
        .chat-input button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        @media (max-width: 480px) {
            .chat-window {
                width: 90%;
                right: 5%;
                left: 5%;
                bottom: 80px;
                height: 450px;
            }
        }
        
        /* ========== SUPPORT ========== */
        .support {
            padding: 50px 0;
            background: var(--gray);
        }

        .support-content h3 {
            text-align: center;
            font-size: 1.5rem;
            margin-bottom: 30px;
            color: var(--primary);
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

        .support-card a {
            color: var(--whatsapp);
            text-decoration: none;
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

        .footer-logo img {
            width: 40px;
            border-radius: 50%;
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
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
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

        /* ========== RESPONSIVE ========== */
        @media (max-width: 992px) {
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .media-grid { grid-template-columns: repeat(2, 1fr); }
            .cultes-grid { grid-template-columns: repeat(2, 1fr); }
            .support-grid { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 768px) {
            .menu-toggle { display: block; }
            nav ul { display: none; flex-direction: column; width: 100%; background: var(--primary); padding: 20px; position: absolute; top: 100%; left: 0; text-align: center; }
            nav ul.active { display: flex; }
            .nav-wrapper { position: relative; }
            .announcement-btn, .support-btn { display: none; }
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
        }
    </style>
</head>
<body class="<?php echo $theme; ?>">
    <!-- HEADER -->
    <header class="header">
        <div class="container">
            <div class="logo">
                <div class="logo-icon">
                    <img src="images/logo.png" alt="Logo" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <i class="fas fa-dove" style="display: none;"></i>
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
                        <li><a href="#app">Application</a></li>
                        <li><a href="#contact">Contact</a></li>
                    </ul>
                </nav>
                <button class="theme-toggle-btn" id="themeToggleBtn">
                    <i class="fas fa-moon"></i>
                </button>
                <button class="announcement-btn" id="announcementBtn">
                    <i class="fas fa-bullhorn"></i> Annonce
                </button>
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
                <div class="date-display" id="currentDate"></div>
                <div class="time-display" id="currentTime"></div>
            </div>
        </div>
    </div>

    <!-- HERO -->
    <section id="accueil" class="hero">
        <div class="hero-slider">
            <div class="slide active" style="background-image: url('images/eglise.jpg');">
                <div class="slide-overlay"></div>
            </div>
        </div>
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
                    <span class="stat-number" id="subscribersCount">0</span>
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
                    <img src="images/pasteur.jpg" alt="Pasteur" onerror="this.src='https://via.placeholder.com/500x500?text=Pasteur'">
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

    <!-- BIBLIOTHÈQUE (MODIFIÉE) -->
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
                    <h3>Bibliothèque de sermons</h3>
                    <div class="library-stats">
                        <span class="library-count" id="sermonsCountStatic"><?php echo ($stats['sermons_count_mega_manual'] ?? 0) + ($stats['sermons_count_degoo_manual'] ?? 0); ?></span>
                        <span>sermons disponibles</span>
                    </div>
                    <p>Accédez à tous nos sermons sur notre plateforme audio</p>
                    <a href="https://audio.com/vision-daigles-tabernacle-pn" target="_blank" class="btn-library audio-btn">
                        <i class="fas fa-download"></i> Accéder à la bibliothèque
                    </a>
                </div>
            </div>
        </div>
    </section>

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
                <form id="registerFormAjax">
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
                    <a href="https://youtube.com/@ayezfoiendieu" target="_blank" class="btn-media youtube-btn"><i class="fab fa-youtube"></i> Regarder</a>
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

    <!-- APPLICATION -->
    <section id="app" class="app-section">
        <div class="container">
            <div class="app-content">
                <div class="app-info">
                    <h2>Téléchargez <span>notre application</span></h2>
                    <p>Accédez à tous nos contenus hors ligne : sermons, cantiques, Bible et bien plus encore. L'application se met à jour automatiquement lorsqu'une connexion est détectée.</p>
                    <div class="app-buttons">
                        <?php if(!empty($apkPath)): ?>
                            <a href="<?php echo htmlspecialchars($apkPath); ?>" class="btn-app" download>
                                <i class="fas fa-download"></i>
                                <div>
                                    <span>Télécharger l'application</span>
                                    <small>Version Android - APK v<?php echo htmlspecialchars($appVersion); ?></small>
                                </div>
                            </a>
                        <?php else: ?>
                            <a href="#" class="btn-app" id="downloadAppBtnDisabled" onclick="alert('APK non disponible. Veuillez réessayer plus tard.')">
                                <i class="fas fa-download"></i>
                                <div>
                                    <span>Télécharger l'application</span>
                                    <small>Bientôt disponible</small>
                                </div>
                            </a>
                        <?php endif; ?>
                        <button class="btn-app" id="checkUpdatesBtn" onclick="checkForUpdates()">
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
    <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?php echo urlencode($apkPath); ?>" alt="QR Code">
    <p>Scannez pour télécharger</p>
</div>
            </div>
        </div>
    </section>

    <!-- GPS / LOCALISATION -->
    <section id="localisation" class="section section-gray">
        <div class="container">
            <div class="section-title">
                <div class="section-label">Nous trouver</div>
                <h2>Notre <span>Localisation</span></h2>
                <div class="divider"></div>
                <p>Quartier Voungou, Pointe-Noire, République du Congo</p>
            </div>
            <div id="map" style="height: 400px; width: 100%; border-radius: 20px; overflow: hidden; margin-bottom: 20px;"></div>
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

    <!-- ANNONCES -->
    <section id="annonces" class="section">
        <div class="container">
            <div class="section-title">
                <div class="section-label">Actualités</div>
                <h2>Dernières <span>Annonces</span></h2>
                <div class="divider"></div>
            </div>
            <div class="announcements-container" id="announcementsList">
                <div style="text-align: center; padding: 20px;">
                    <i class="fas fa-spinner fa-spin"></i> Chargement des annonces...
                </div>
            </div>
        </div>
    </section>

    <!-- SUPPORT (NUMÉROS WHATSAPP) -->
    <section class="support">
        <div class="container">
            <div class="support-content">
                <h3>Support Technique</h3>
                <div class="support-grid">
                    <div class="support-card"><i class="fab fa-whatsapp"></i><span>Fr Bosley</span><a href="https://wa.me/242066119323">+242 06 611 93 23</a></div>
                    <div class="support-card"><i class="fab fa-whatsapp"></i><span>Fr Jabien</span><a href="https://wa.me/242055684041">+242 05 568 40 41</a></div>
                    <div class="support-card"><i class="fab fa-whatsapp"></i><span>Fr Kelly</span><a href="https://wa.me/242064221426">+242 06 422 14 26</a></div>
                    <div class="support-card"><i class="fab fa-whatsapp"></i><span>Fr Timothée</span><a href="https://wa.me/242068722292">+242 06 872 22 92</a></div>
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
                        <img src="images/logo.png" alt="Logo" onerror="this.style.display='none'">
                        <h3>VISION D'AIGLES TABERNACLE</h3>
                    </div>
                    <p>Ministère Prophétique de William Marrion Branham.</p>
                    <div class="social-links">
                        <a href="https://youtube.com/@ayezfoiendieu" target="_blank"><i class="fab fa-youtube"></i></a>
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
                        <li><a href="#app">Application</a></li>
                    </ul>
                </div>
                <div class="footer-contact">
                    <h4>Contact</h4>
                    <p><i class="fas fa-map-marker-alt"></i> Quartier Voungou, Pointe-Noire, Congo</p>
                    <p><i class="fas fa-envelope"></i> visiondaigles.tab1@gmail.com</p>
                    <p><i class="fab fa-whatsapp"></i> +242 06 629 3093</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2026 Vision d'Aigles Tabernacle. Tous droits réservés.</p>
                <p>Développé pour la gloire de Dieu</p>
            </div>
        </div>
    </footer>

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
            <div class="chat-messages" id="chatMessagesList">
                <div style="text-align: center; padding: 20px;">Chargement des messages...</div>
            </div>
            <div class="chat-input">
                <input type="text" id="chatMessageInput" placeholder="Écrivez votre message..." autocomplete="off">
                <button id="chatSendMessageBtn"><i class="fas fa-paper-plane"></i> Envoyer</button>
            </div>
        </div>
    </div>

    <script>
    // Fonctions du chat
    (function() {
        const myUserId = <?php echo $userId; ?>;
        const adminId = 1;
        
        const chatToggle = document.getElementById('chatToggleBtn');
        const chatWindow = document.getElementById('chatWindow');
        const closeChat = document.getElementById('closeChatBtn');
        const sendBtn = document.getElementById('chatSendMessageBtn');
        const messageInput = document.getElementById('chatMessageInput');
        const messagesContainer = document.getElementById('chatMessagesList');
        const unreadBadge = document.getElementById('chatUnreadCount');
        
        let lastMessageCount = 0;
        
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
            try {
                const response = await fetch('chat-get-messages.php?user_id=' + adminId + '&t=' + Date.now());
                const data = await response.json();
                
                if (data.success && data.messages) {
                    if (data.messages.length === 0) {
                        messagesContainer.innerHTML = '<div style="text-align: center; padding: 20px; color: #999;">Aucun message. Commencez la conversation !</div>';
                        unreadBadge.style.display = 'none';
                        return;
                    }
                    
                    let unreadCount = 0;
                    const messagesHtml = data.messages.map(msg => {
                        if (msg.sender_id !== myUserId && !msg.is_read) unreadCount++;
                        return `
                            <div class="message ${msg.sender_id === myUserId ? 'sent' : 'received'}">
                                <div class="message-bubble">${escapeHtml(msg.message)}</div>
                                <div class="message-time">${formatTime(msg.created_at)} ${msg.sender_id === myUserId ? (msg.is_read ? '✓✓' : '✓') : ''}</div>
                            </div>
                        `;
                    }).join('');
                    
                    messagesContainer.innerHTML = messagesHtml;
                    messagesContainer.scrollTop = messagesContainer.scrollHeight;
                    
                    if (unreadCount > 0 && !chatWindow.classList.contains('open')) {
                        unreadBadge.style.display = 'block';
                        unreadBadge.textContent = unreadCount;
                    } else {
                        unreadBadge.style.display = 'none';
                    }
                    
                    if (chatWindow.classList.contains('open') && unreadCount > 0) {
                        await markAsRead();
                    }
                }
            } catch (error) {
                console.error('Erreur chargement messages:', error);
                messagesContainer.innerHTML = '<div style="text-align: center; padding: 20px; color: red;">Erreur de chargement. Veuillez rafraîchir.</div>';
            }
        }
        
        async function sendMessage() {
            const message = messageInput.value.trim();
            if (!message) {
                alert('Veuillez écrire un message');
                return;
            }
            
            sendBtn.disabled = true;
            sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Envoi...';
            
            try {
                const response = await fetch('php/chat-send.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ to_user_id: adminId, message: message })
                });
                const data = await response.json();
                
                if (data.success) {
                    messageInput.value = '';
                    await loadMessages();
                } else {
                    alert('Erreur: ' + (data.message || 'Envoi impossible'));
                }
            } catch (error) {
                console.error('Erreur envoi:', error);
                alert('Erreur de connexion. Veuillez réessayer.');
            } finally {
                sendBtn.disabled = false;
                sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Envoyer';
            }
        }
        
        async function markAsRead() {
            try {
                await fetch('chat-mark-read.php?user_id=' + adminId);
                unreadBadge.style.display = 'none';
            } catch (error) {
                console.error('Erreur marquage lu:', error);
            }
        }
        
        if (chatToggle) {
            chatToggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                chatWindow.classList.toggle('open');
                if (chatWindow.classList.contains('open')) {
                    markAsRead();
                    loadMessages();
                }
            });
        }
        
        if (closeChat) {
            closeChat.addEventListener('click', function() {
                chatWindow.classList.remove('open');
            });
        }
        
        if (sendBtn) {
            sendBtn.addEventListener('click', sendMessage);
        }
        
        if (messageInput) {
            messageInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    sendMessage();
                }
            });
        }
        
        loadMessages();
        setInterval(loadMessages, 5000);
    })();
    </script>
    <?php endif; ?>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // Initialisation de la carte GPS
        function initMap() {
            var churchLat = -4.7691;
            var churchLng = 11.8664;
            var map = L.map('map').setView([churchLat, churchLng], 15);
            L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="https://carto.com/attributions">CARTO</a>',
                maxZoom: 19
            }).addTo(map);
            var marker = L.marker([churchLat, churchLng]).addTo(map);
            marker.bindPopup("<b>Vision d'Aigles Tabernacle</b><br>Quartier Voungou, Pointe-Noire<br>République du Congo").openPopup();
        }
        
        document.addEventListener('DOMContentLoaded', initMap);
        
        // Menu mobile
        const menuToggle = document.getElementById('menuToggle');
        const navMenu = document.getElementById('navMenu');
        if(menuToggle) {
            menuToggle.addEventListener('click', () => { navMenu.classList.toggle('active'); });
        }
        
        // Header scroll
        const header = document.querySelector('.header');
        window.addEventListener('scroll', () => {
            if(window.scrollY > 50) header.classList.add('scrolled');
            else header.classList.remove('scrolled');
        });
        
        // Horloge
        function updateDateTime() {
            const now = new Date();
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            let dateStr = now.toLocaleDateString('fr-FR', options);
            dateStr = dateStr.charAt(0).toUpperCase() + dateStr.slice(1);
            document.getElementById('currentDate').textContent = dateStr;
            document.getElementById('currentTime').textContent = now.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        }
        updateDateTime();
        setInterval(updateDateTime, 1000);
        
        // Theme toggle
        const themeToggleBtn = document.getElementById('themeToggleBtn');
        const body = document.body;
        function setTheme(theme) {
            if(theme === 'dark') {
                body.classList.add('dark');
                themeToggleBtn.innerHTML = '<i class="fas fa-sun"></i>';
            } else {
                body.classList.remove('dark');
                themeToggleBtn.innerHTML = '<i class="fas fa-moon"></i>';
            }
            document.cookie = `theme=${theme}; path=/; max-age=${60*60*24*365}`;
        }
        if(themeToggleBtn) {
            themeToggleBtn.addEventListener('click', () => {
                if(body.classList.contains('dark')) setTheme('light');
                else setTheme('dark');
            });
        }
        
        // Scroll indicator
        document.querySelector('.scroll-indicator')?.addEventListener('click', () => {
            window.scrollTo({ top: window.innerHeight, behavior: 'smooth' });
        });
        
        // Stats
        async function loadStats() {
            try {
                const response = await fetch('php/get-stats.php');
                const data = await response.json();
                if(data.success) {
                    document.getElementById('visitorsCount').innerText = data.visitors || 0;
                    document.getElementById('subscribersCount').innerText = data.subscribers || 0;
                    document.getElementById('sermonsCount').innerText = data.total_sermons || 0;
                    document.getElementById('megaCount').innerText = data.sermons_mega || 0;
                    document.getElementById('degooCount').innerText = data.sermons_degoo || 0;
                }
            } catch(e) { console.error(e); }
        }
        
        // Annonces
        async function loadAnnouncements() {
            try {
                const response = await fetch('php/check-notifications.php');
                const data = await response.json();
                const container = document.getElementById('announcementsList');
                if(container && data.announcements) {
                    if(data.announcements.length === 0) {
                        container.innerHTML = '<p style="text-align:center;">Aucune annonce pour le moment.</p>';
                    } else {
                        container.innerHTML = data.announcements.map(ann => `
                            <div class="announcement-item">
                                <div class="announcement-title">📢 ${escapeHtml(ann.title)}</div>
                                <div>${escapeHtml(ann.content)}</div>
                                <div class="announcement-date">📅 ${new Date(ann.created_at).toLocaleDateString('fr-FR')}</div>
                            </div>
                        `).join('');
                    }
                }
            } catch(e) { console.error(e); }
        }
        
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        // Modal
        function showAuthModal() {
            const modal = document.getElementById('authModal');
            if(modal) {
                modal.classList.add('show');
                document.getElementById('loginMessageAjax').className = 'form-message';
                document.getElementById('registerMessageAjax').className = 'form-message';
            }
        }
        document.querySelector('.modal-close')?.addEventListener('click', () => {
            document.getElementById('authModal').classList.remove('show');
        });
        window.addEventListener('click', (e) => {
            const modal = document.getElementById('authModal');
            if(e.target === modal) modal.classList.remove('show');
        });
        
        // Tabs modal
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const tab = btn.dataset.tab;
                document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
                btn.classList.add('active');
                document.getElementById(`${tab}Tab`).classList.add('active');
            });
        });
        
        // Login
        document.getElementById('loginFormAjax')?.addEventListener('submit', async (e) => {
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
                    messageDiv.textContent = result.message;
                }
            } catch(error) {
                messageDiv.classList.add('error');
                messageDiv.textContent = 'Erreur de connexion';
            }
        });
        
        // Register
        document.getElementById('registerFormAjax')?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData();
            formData.append('first_name', document.getElementById('regFirstnameAjax').value);
            formData.append('last_name', document.getElementById('regLastnameAjax').value);
            formData.append('email', document.getElementById('regEmailAjax').value);
            formData.append('phone', document.getElementById('regPhoneAjax').value);
            formData.append('password', document.getElementById('regPasswordAjax').value);
            formData.append('newsletter', document.getElementById('regNewsletterAjax').checked ? 'on' : '');
            const messageDiv = document.getElementById('registerMessageAjax');
            try {
                const response = await fetch('php/subscribe.php', { method: 'POST', body: formData });
                const result = await response.json();
                messageDiv.className = 'form-message';
                if(result.success) {
                    messageDiv.classList.add('success');
                    messageDiv.textContent = result.message;
                    document.getElementById('registerFormAjax').reset();
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
        
        // Vérification des mises à jour
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
        
        // Smooth scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                if(href !== "#" && href !== "#") {
                    const target = document.querySelector(href);
                    if(target) {
                        e.preventDefault();
                        target.scrollIntoView({ behavior: 'smooth' });
                    }
                }
            });
        });
        
        // Bouton annonce
        document.getElementById('announcementBtn')?.addEventListener('click', () => {
            document.getElementById('annonces').scrollIntoView({ behavior: 'smooth' });
            loadAnnouncements();
        });
        
        // Initialisation
        loadStats();
        loadAnnouncements();
        setInterval(() => { loadStats(); loadAnnouncements(); }, 30000);
    </script>
</body>
</html>