<?php
// Configuration base de données InfinityFree
// Mode fichier JSON

class SimpleDatabase {
    private $dataFile;
    private $data;
    
    public function __construct() {
        $this->dataFile = __DIR__ . '/../data/data.json';
        $this->loadData();
    }
    
    private function loadData() {
        if(file_exists($this->dataFile)) {
            $content = file_get_contents($this->dataFile);
            $this->data = json_decode($content, true);
            if(!$this->data) $this->initData();
        } else {
            $this->initData();
        }
    }
    
    private function initData() {
        $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
        
        $this->data = [
            'stats' => [
                'id' => 1,
                'visitors_count' => 0,
                'sermons_count_mega_manual' => 50,
                'sermons_count_degoo_manual' => 36,
                'last_updated' => date('Y-m-d H:i:s')
            ],
            'subscribers' => [
                [
                    'id' => 1,
                    'first_name' => 'Administrateur',
                    'last_name' => 'Vision',
                    'email' => 'visiondaigles.tab1@gmail.com',
                    'phone' => '+242066293093',
                    'password' => $adminPassword,
                    'is_newsletter' => 1,
                    'status' => 'approved',
                    'role' => 'admin',
                    'email_validated' => 1,
                    'validation_token' => null,
                    'reset_token' => null,
                    'reset_expires' => null,
                    'subscribed_at' => date('Y-m-d H:i:s'),
                    'approved_at' => date('Y-m-d H:i:s'),
                    'is_blocked' => 0
                ]
            ],
            'sermons' => [],
            'announcements' => [
                [
                    'id' => 1,
                    'title' => 'Bienvenue sur notre site !',
                    'content' => 'Nous sommes ravis de vous accueillir sur le site de Vision d\'Aigles Tabernacle. Restez connectés pour les dernières actualités.',
                    'author' => 'Admin',
                    'is_active' => 1,
                    'is_notified' => 0,
                    'created_at' => date('Y-m-d H:i:s')
                ]
            ],
            'notifications' => [],
            'visitors' => [],
            'site_settings' => [
                'homepage' => [
                    'hero_title' => 'Vision d\'Aigles Tabernacle',
                    'hero_text' => '« À aucun instant je n\'apporte aux gens un message pour les pousser à me suivre... » — Rév. William Marrion Branham',
                    'pastor_name' => 'ARTHUR GÉDÉON MOUZITA MAYOULOU',
                    'pastor_title' => 'Fondateur & Pasteur Principal',
                    'pastor_bio' => 'Homme de Dieu, Carismatique, Visionnaire et Dévoué au Travail du Seigneur JÉSUS-CHRIST, Portant la Parole de Vie, Vase utile et propre à toutes bonnes oeuvres.',
                    'pastor_quote' => 'Notre vision est de voir l\'Épouse de CHRIST jouir de sa position et de ses privilèges dans le ministère qui est sienne en ces derniers jours',
                    'last_updated' => date('Y-m-d H:i:s')
                ],
                'cultes' => [
                    ['day' => 'DIMANCHE', 'time' => '9h00 - 13h00', 'description' => 'Culte Dominical - Méditation, Adoration & Louange, Prédication'],
                    ['day' => 'MERCREDI', 'time' => '16h00 - 18h30', 'description' => 'Culte de Semaine - Méditation, Adoration & Louange, Prédication'],
                    ['day' => 'VENDREDI', 'time' => '16h00 - 18h30', 'description' => 'Culte de Semaine - Méditation, Adoration & Louange, Prédication']
                ],
                'app' => [
                    'version' => '1.0.0',
                    'apk_path' => '',
                    'last_update' => date('Y-m-d H:i:s'),
                    'changelog' => 'Première version de l\'application'
                ]
            ]
        ];
        $this->saveData();
    }
    
    private function saveData() {
        $dir = dirname($this->dataFile);
        if(!is_dir($dir)) mkdir($dir, 0777, true);
        file_put_contents($this->dataFile, json_encode($this->data, JSON_PRETTY_PRINT));
    }
    
    public function prepare($sql) {
        return new SimpleStatement($this, $sql);
    }
    
    public function query($sql) {
        return new SimpleResult($this, $sql);
    }
    
    public function lastInsertId() {
        $data = $this->data;
        $maxId = 0;
        
        if(!empty($data['subscribers'])) {
            $ids = array_column($data['subscribers'], 'id');
            $maxId = max($maxId, max($ids));
        }
        if(!empty($data['sermons'])) {
            $ids = array_column($data['sermons'], 'id');
            $maxId = max($maxId, max($ids));
        }
        if(!empty($data['announcements'])) {
            $ids = array_column($data['announcements'], 'id');
            $maxId = max($maxId, max($ids));
        }
        
        return $maxId + 1;
    }
    
    public function getData() { return $this->data; }
    public function setData($data) { $this->data = $data; $this->saveData(); }
}

class SimpleStatement {
    private $db;
    private $sql;
    private $params;
    
    public function __construct($db, $sql) {
        $this->db = $db;
        $this->sql = $sql;
    }
    
    public function execute($params = []) {
        $this->params = $params;
        $data = $this->db->getData();
        
        // INSERT INTO subscribers
        if(strpos($this->sql, 'INSERT INTO subscribers') !== false) {
            $newSubscriber = [
                'id' => $this->db->lastInsertId(),
                'first_name' => $params[0],
                'last_name' => $params[1],
                'email' => $params[2],
                'phone' => $params[3],
                'password' => $params[4],
                'is_newsletter' => $params[5],
                'status' => 'pending',
                'role' => 'user',
                'email_validated' => 0,
                'validation_token' => $params[6] ?? null,
                'reset_token' => null,
                'reset_expires' => null,
                'subscribed_at' => date('Y-m-d H:i:s'),
                'approved_at' => null,
                'is_blocked' => 0
            ];
            $data['subscribers'][] = $newSubscriber;
            $this->db->setData($data);
            return $newSubscriber['id'];
        }
        
        // INSERT INTO announcements
        if(strpos($this->sql, 'INSERT INTO announcements') !== false) {
            $data['announcements'][] = [
                'id' => $this->db->lastInsertId(),
                'title' => $params[0],
                'content' => $params[1],
                'author' => $params[2],
                'is_active' => 1,
                'is_notified' => 0,
                'created_at' => date('Y-m-d H:i:s')
            ];
            $this->db->setData($data);
        }
        
        // UPDATE stats
        if(strpos($this->sql, 'UPDATE stats SET sermons_count_mega_manual') !== false) {
            $data['stats']['sermons_count_mega_manual'] = (int)$params[0];
            $data['stats']['sermons_count_degoo_manual'] = (int)$params[1];
            $this->db->setData($data);
        }
        
        // UPDATE stats visitors
        if(strpos($this->sql, 'UPDATE stats SET visitors_count') !== false) {
            $data['stats']['visitors_count'] = $data['stats']['visitors_count'] + 1;
            $this->db->setData($data);
        }
        
        // UPDATE subscribers status
        if(strpos($this->sql, 'UPDATE subscribers SET status') !== false) {
            foreach($data['subscribers'] as &$sub) {
                if($sub['id'] == $params[1]) {
                    $sub['status'] = $params[0];
                    if($params[0] == 'approved') $sub['approved_at'] = date('Y-m-d H:i:s');
                    break;
                }
            }
            $this->db->setData($data);
        }
        
        // UPDATE subscribers email_validated
        if(strpos($this->sql, 'UPDATE subscribers SET email_validated') !== false) {
            foreach($data['subscribers'] as &$sub) {
                if($sub['id'] == $params[1]) {
                    $sub['email_validated'] = $params[0];
                    $sub['validation_token'] = $params[2];
                    break;
                }
            }
            $this->db->setData($data);
        }
        
        // UPDATE subscribers block
        if(strpos($this->sql, 'UPDATE subscribers SET is_blocked') !== false) {
            foreach($data['subscribers'] as &$sub) {
                if($sub['id'] == $params[1]) {
                    $sub['is_blocked'] = $params[0];
                    break;
                }
            }
            $this->db->setData($data);
        }
        
        // DELETE FROM subscribers
        if(strpos($this->sql, 'DELETE FROM subscribers') !== false) {
            $id = $params[0];
            $newSubscribers = [];
            foreach($data['subscribers'] as $sub) {
                if($sub['id'] != $id) $newSubscribers[] = $sub;
            }
            $data['subscribers'] = $newSubscribers;
            $this->db->setData($data);
        }
        
        // DELETE FROM announcements
        if(strpos($this->sql, 'DELETE FROM announcements') !== false) {
            $id = $params[0];
            $newAnnouncements = [];
            foreach($data['announcements'] as $a) {
                if($a['id'] != $id) $newAnnouncements[] = $a;
            }
            $data['announcements'] = $newAnnouncements;
            $this->db->setData($data);
        }
        
        // UPDATE site_settings app
        if(strpos($this->sql, 'UPDATE site_settings SET app') !== false) {
            if(!isset($data['site_settings'])) $data['site_settings'] = [];
            $data['site_settings']['app'] = json_decode($params[0], true);
            $this->db->setData($data);
        }
        
        // UPDATE site_settings homepage
        if(strpos($this->sql, 'UPDATE site_settings SET homepage') !== false) {
            if(!isset($data['site_settings'])) $data['site_settings'] = [];
            $data['site_settings']['homepage'] = json_decode($params[0], true);
            $this->db->setData($data);
        }
        
        // UPDATE site_settings cultes
        if(strpos($this->sql, 'UPDATE site_settings SET cultes') !== false) {
            if(!isset($data['site_settings'])) $data['site_settings'] = [];
            $data['site_settings']['cultes'] = json_decode($params[0], true);
            $this->db->setData($data);
        }
        
        return true;
    }
    
    public function fetch() {
        $data = $this->db->getData();
        
        if(strpos($this->sql, 'SELECT * FROM stats') !== false) {
            return $data['stats'];
        }
        
        if(strpos($this->sql, 'SELECT COUNT(*) as count FROM subscribers WHERE status =') !== false) {
            $status = strpos($this->sql, 'approved') !== false ? 'approved' : 'pending';
            $count = 0;
            foreach($data['subscribers'] as $s) {
                if($s['status'] == $status && $s['is_blocked'] != 1) $count++;
            }
            return ['count' => $count];
        }
        
        if(strpos($this->sql, 'SELECT COUNT(*) as count FROM subscribers WHERE is_newsletter = 1 AND status =') !== false) {
            $count = 0;
            foreach($data['subscribers'] as $s) {
                if($s['is_newsletter'] == 1 && $s['status'] == 'approved' && $s['is_blocked'] != 1 && $s['email_validated'] == 1) {
                    $count++;
                }
            }
            return ['count' => $count];
        }
        
        if(strpos($this->sql, 'SELECT * FROM subscribers WHERE email =') !== false) {
            $email = $this->params[0] ?? '';
            foreach($data['subscribers'] as $s) {
                if($s['email'] == $email) return $s;
            }
            return null;
        }
        
        if(strpos($this->sql, 'SELECT * FROM subscribers WHERE validation_token =') !== false) {
            $token = $this->params[0] ?? '';
            foreach($data['subscribers'] as $s) {
                if($s['validation_token'] == $token && $s['email_validated'] == 0) return $s;
            }
            return null;
        }
        
        if(strpos($this->sql, 'SELECT * FROM subscribers WHERE id =') !== false) {
            $id = (int)$this->params[0];
            foreach($data['subscribers'] as $s) {
                if($s['id'] == $id) return $s;
            }
            return null;
        }
        
        if(strpos($this->sql, 'SELECT * FROM subscribers ORDER BY') !== false) {
            return $data['subscribers'];
        }
        
        if(strpos($this->sql, 'SELECT * FROM announcements ORDER BY') !== false) {
            return array_reverse($data['announcements']);
        }
        
        if(strpos($this->sql, 'SELECT * FROM site_settings') !== false) {
            return $data['site_settings'] ?? [];
        }
        
        return null;
    }
    
    public function fetchAll() {
        $result = $this->fetch();
        if(is_array($result) && !isset($result['id']) && !isset($result['count']) && !isset($result['hero_title'])) {
            return $result;
        }
        return $result ? [$result] : [];
    }
    
    public function rowCount() {
        $result = $this->fetch();
        return $result ? (isset($result['count']) ? $result['count'] : 1) : 0;
    }
}

class SimpleResult {
    private $data;
    
    public function __construct($db, $sql) {
        $data = $db->getData();
        if(strpos($sql, 'SELECT COUNT(*) as count FROM visitors') !== false) {
            $this->data = [['count' => count($data['visitors'])]];
        } else {
            $this->data = [];
        }
    }
    
    public function fetch() {
        return $this->data[0] ?? null;
    }
}

$pdo = new SimpleDatabase();

function countVisitor($pdo) {
    $ip = $_SERVER['REMOTE_ADDR'];
    $today = date('Y-m-d');
    $data = $pdo->getData();
    
    $found = false;
    foreach($data['visitors'] as $v) {
        if($v['ip'] == $ip && $v['date'] == $today) {
            $found = true;
            break;
        }
    }
    
    if(!$found) {
        $data['visitors'][] = ['ip' => $ip, 'date' => $today];
        $data['stats']['visitors_count']++;
        $pdo->setData($data);
    }
}

countVisitor($pdo);

// Fonction d'envoi d'email simplifiée
function sendEmail($to, $subject, $message) {
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: Vision d'Aigles Tabernacle <visiondaigles.tab1@gmail.com>\r\n";
    $headers .= "Reply-To: visiondaigles.tab1@gmail.com\r\n";
    
    return mail($to, $subject, $message, $headers);
}
?>