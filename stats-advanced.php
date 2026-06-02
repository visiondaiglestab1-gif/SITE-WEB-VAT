<?php
session_start();
require_once '../config/database.php';

if(!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

$data = $pdo->getData();
$stats = $data['stats'];

// Statistiques des visiteurs par jour
$visitorsByDay = [];
foreach($data['visitors'] as $v) {
    $date = $v['date'];
    if(!isset($visitorsByDay[$date])) {
        $visitorsByDay[$date] = 0;
    }
    $visitorsByDay[$date]++;
}

// Préparer les données pour Chart.js
$dates = array_keys($visitorsByDay);
$visitorsCounts = array_values($visitorsByDay);

// Statistiques des abonnés par mois
$subscribersByMonth = [];
foreach($data['subscribers'] as $sub) {
    $month = date('Y-m', strtotime($sub['subscribed_at']));
    if(!isset($subscribersByMonth[$month])) {
        $subscribersByMonth[$month] = 0;
    }
    $subscribersByMonth[$month]++;
}

$months = array_keys($subscribersByMonth);
$subscribersCounts = array_values($subscribersByMonth);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiques avancées - Vision d'Aigles</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f0f2f5; }
        
        .admin-header { background: linear-gradient(135deg, #0a2f44, #1a4a6e); color: white; padding: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; }
        .admin-header h1 { font-size: 1.3rem; }
        .admin-header a { color: white; text-decoration: none; background: rgba(255,255,255,0.2); padding: 8px 15px; border-radius: 5px; margin-left: 10px; }
        
        .container { max-width: 1400px; margin: 20px auto; padding: 0 20px; }
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 25px; border-radius: 15px; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .stat-number { font-size: 2rem; font-weight: bold; color: #f4c542; }
        
        .chart-container { background: white; border-radius: 15px; padding: 25px; margin-bottom: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .chart-container h2 { margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #f4c542; }
        .charts-row { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
        
        .btn { display: inline-block; padding: 10px 20px; background: #f4c542; color: #0a2f44; text-decoration: none; border-radius: 8px; font-weight: bold; margin-top: 20px; }
        
        @media (max-width: 768px) {
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .charts-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <h1><i class="fas fa-chart-line"></i> Statistiques avancées</h1>
        <div>
            <a href="dashboard.php"><i class="fas fa-arrow-left"></i> Retour au dashboard</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
        </div>
    </div>
    
    <div class="container">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['visitors_count']; ?></div>
                <div><i class="fas fa-eye"></i> Visiteurs totaux</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo count($data['subscribers']); ?></div>
                <div><i class="fas fa-users"></i> Abonnés inscrits</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo count($data['announcements']); ?></div>
                <div><i class="fas fa-bullhorn"></i> Annonces</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo count($data['notifications']); ?></div>
                <div><i class="fas fa-bell"></i> Notifications</div>
            </div>
        </div>
        
        <div class="charts-row">
            <div class="chart-container">
                <h2><i class="fas fa-chart-bar"></i> Visiteurs par jour</h2>
                <canvas id="visitorsChart"></canvas>
                <a href="export-data.php?type=visitors" class="btn"><i class="fas fa-download"></i> Exporter les données</a>
            </div>
            
            <div class="chart-container">
                <h2><i class="fas fa-chart-line"></i> Inscriptions par mois</h2>
                <canvas id="subscribersChart"></canvas>
                <a href="export-data.php?type=subscribers" class="btn"><i class="fas fa-download"></i> Exporter les données</a>
            </div>
        </div>
        
        <div class="chart-container">
            <h2><i class="fas fa-chart-pie"></i> Répartition des sermons</h2>
            <canvas id="sermonsChart" style="max-width: 400px; margin: 0 auto;"></canvas>
            <a href="export-data.php?type=sermons" class="btn"><i class="fas fa-download"></i> Exporter les données</a>
        </div>
    </div>
    
    <script>
        // Graphique des visiteurs
        const ctx1 = document.getElementById('visitorsChart').getContext('2d');
        new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($dates); ?>,
                datasets: [{
                    label: 'Visiteurs',
                    data: <?php echo json_encode($visitorsCounts); ?>,
                    backgroundColor: '#f4c542',
                    borderColor: '#d4a22a',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: { y: { beginAtZero: true } }
            }
        });
        
        // Graphique des inscriptions
        const ctx2 = document.getElementById('subscribersChart').getContext('2d');
        new Chart(ctx2, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($months); ?>,
                datasets: [{
                    label: 'Inscriptions',
                    data: <?php echo json_encode($subscribersCounts); ?>,
                    backgroundColor: 'rgba(244, 197, 66, 0.2)',
                    borderColor: '#f4c542',
                    borderWidth: 2,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                scales: { y: { beginAtZero: true } }
            }
        });
        
        // Graphique des sermons
        const ctx3 = document.getElementById('sermonsChart').getContext('2d');
        new Chart(ctx3, {
            type: 'pie',
            data: {
                labels: ['MEGA', 'DEGOO'],
                datasets: [{
                    data: [<?php echo $stats['sermons_count_mega_manual']; ?>, <?php echo $stats['sermons_count_degoo_manual']; ?>],
                    backgroundColor: ['#1A2B4C', '#FF6B35']
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'bottom' } }
            }
        });
    </script>
</body>
</html>