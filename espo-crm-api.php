<?php
/**
 * EspoCRM Task Management API
 * Optimized for Call Center Agents
 * 
 * RC#1: Eliminarea din listă a 100% din sarcinile finalizate
 * RC#2: Colorarea dinamică a sarcinilor active, nefinalizate și blocate
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Simulare conexiune EspoCRM database
class EspoCRMTaskAPI {
    private $db;
    private $agentId;
    
    public function __construct($agentId = 'AG001') {
        $this->agentId = $agentId;
        // În implementarea reală, aici ar fi conexiunea la baza de date EspoCRM
        $this->initializeDatabase();
    }
    
    private function initializeDatabase() {
        // Simulare date din EspoCRM
        $this->db = [
            'tasks' => [
                [
                    'id' => 1,
                    'title' => 'Apel client - Ionescu Maria',
                    'description' => 'Discutare ofertă pachet premium, urmărire contract',
                    'status' => 'active',
                    'priority' => 'high',
                    'dueDate' => '2024-01-15',
                    'assignedUserId' => $this->agentId,
                    'parentType' => 'Account',
                    'parentId' => 'acc_001',
                    'parentName' => 'Ionescu Maria',
                    'contactPhone' => '+40722123456',
                    'taskType' => 'call',
                    'progress' => 60,
                    'createdAt' => '2024-01-10 09:00:00',
                    'updatedAt' => '2024-01-14 15:30:00'
                ],
                [
                    'id' => 2,
                    'title' => 'Follow-up contract Popescu SRL',
                    'description' => 'Verificare semnături și documentație completă',
                    'status' => 'pending',
                    'priority' => 'medium',
                    'dueDate' => '2024-01-16',
                    'assignedUserId' => $this->agentId,
                    'parentType' => 'Account',
                    'parentId' => 'acc_002',
                    'parentName' => 'Popescu SRL',
                    'contactPhone' => '+40721987654',
                    'taskType' => 'meeting',
                    'progress' => 30,
                    'createdAt' => '2024-01-11 10:15:00',
                    'updatedAt' => '2024-01-14 16:45:00'
                ],
                [
                    'id' => 3,
                    'title' => 'Rezolvare reclamație - Georgescu',
                    'description' => 'Client nemulțumit de servicii, necesită intervenție urgentă',
                    'status' => 'urgent',
                    'priority' => 'high',
                    'dueDate' => '2024-01-15',
                    'assignedUserId' => $this->agentId,
                    'parentType' => 'Case',
                    'parentId' => 'case_001',
                    'parentName' => 'Georgescu Andrei',
                    'contactPhone' => '+40734567890',
                    'taskType' => 'support',
                    'progress' => 10,
                    'escalated' => true,
                    'createdAt' => '2024-01-14 08:30:00',
                    'updatedAt' => '2024-01-14 17:00:00'
                ],
                [
                    'id' => 4,
                    'title' => 'Prezentare produs nou - Startup Tech',
                    'description' => 'Demo platformă CRM, estimare buget 50.000 RON',
                    'status' => 'blocked',
                    'priority' => 'high',
                    'dueDate' => '2024-01-17',
                    'assignedUserId' => $this->agentId,
                    'parentType' => 'Lead',
                    'parentId' => 'lead_001',
                    'parentName' => 'Startup Tech',
                    'contactPhone' => '+40744123789',
                    'taskType' => 'demo',
                    'progress' => 0,
                    'blockReason' => 'Așteptăm aprobare manager',
                    'blockedBy' => 'manager_001',
                    'createdAt' => '2024-01-12 14:20:00',
                    'updatedAt' => '2024-01-14 11:15:00'
                ],
                [
                    'id' => 5,
                    'title' => 'Actualizare date client - Radu Industries',
                    'description' => 'Modificare adresă de facturare și contact principal',
                    'status' => 'active',
                    'priority' => 'low',
                    'dueDate' => '2024-01-18',
                    'assignedUserId' => $this->agentId,
                    'parentType' => 'Account',
                    'parentId' => 'acc_003',
                    'parentName' => 'Radu Industries',
                    'contactPhone' => '+40755666777',
                    'taskType' => 'update',
                    'progress' => 80,
                    'createdAt' => '2024-01-13 11:45:00',
                    'updatedAt' => '2024-01-14 18:20:00'
                ]
            ],
            'call_logs' => [
                [
                    'id' => 1,
                    'taskId' => 1,
                    'agentId' => $this->agentId,
                    'phoneNumber' => '+40722123456',
                    'duration' => 420, // seconds
                    'status' => 'completed',
                    'notes' => 'Client interesat, programat follow-up',
                    'timestamp' => '2024-01-14 10:30:00'
                ],
                [
                    'id' => 2,
                    'taskId' => 3,
                    'agentId' => $this->agentId,
                    'phoneNumber' => '+40734567890',
                    'duration' => 180,
                    'status' => 'escalated',
                    'notes' => 'Client foarte nemulțumit, transferat la manager',
                    'timestamp' => '2024-01-14 14:15:00'
                ]
            ]
        ];
    }
    
    /**
     * RC#1: Obține toate sarcinile EXCLUSIV finalizate
     */
    public function getTasks($filters = []) {
        $tasks = $this->db['tasks'];
        
        // RC#1: Eliminarea completă a sarcinilor finalizate
        $tasks = array_filter($tasks, function($task) {
            return $task['status'] !== 'completed';
        });
        
        // Aplicare filtre
        if (isset($filters['status']) && $filters['status'] !== 'all') {
            $tasks = array_filter($tasks, function($task) use ($filters) {
                return $task['status'] === $filters['status'];
            });
        }
        
        if (isset($filters['priority'])) {
            $tasks = array_filter($tasks, function($task) use ($filters) {
                return $task['priority'] === $filters['priority'];
            });
        }
        
        if (isset($filters['search'])) {
            $searchTerm = strtolower($filters['search']);
            $tasks = array_filter($tasks, function($task) use ($searchTerm) {
                return strpos(strtolower($task['title']), $searchTerm) !== false ||
                       strpos(strtolower($task['parentName']), $searchTerm) !== false ||
                       strpos(strtolower($task['description']), $searchTerm) !== false;
            });
        }
        
        // Sortare după prioritate și dată
        usort($tasks, function($a, $b) {
            $priorityOrder = ['high' => 3, 'medium' => 2, 'low' => 1];
            $statusOrder = ['urgent' => 4, 'active' => 3, 'pending' => 2, 'blocked' => 1];
            
            // Primul criteriu: status (urgent prioritar)
            $statusCompare = $statusOrder[$b['status']] - $statusOrder[$a['status']];
            if ($statusCompare !== 0) return $statusCompare;
            
            // Al doilea criteriu: prioritate
            $priorityCompare = $priorityOrder[$b['priority']] - $priorityOrder[$a['priority']];
            if ($priorityCompare !== 0) return $priorityCompare;
            
            // Al treilea criteriu: data scadentă
            return strtotime($a['dueDate']) - strtotime($b['dueDate']);
        });
        
        return [
            'success' => true,
            'data' => array_values($tasks),
            'stats' => $this->getTaskStats(),
            'agent' => [
                'id' => $this->agentId,
                'name' => 'Ion Popescu',
                'team' => 'Vânzări',
                'status' => 'online'
            ]
        ];
    }
    
    /**
     * RC#2: Statistici pentru colorarea dinamică
     */
    public function getTaskStats() {
        $tasks = array_filter($this->db['tasks'], function($task) {
            return $task['status'] !== 'completed';
        });
        
        $stats = [
            'total' => count($tasks),
            'active' => 0,
            'pending' => 0,
            'blocked' => 0,
            'urgent' => 0
        ];
        
        foreach ($tasks as $task) {
            switch ($task['status']) {
                case 'active':
                    $stats['active']++;
                    break;
                case 'pending':
                    $stats['pending']++;
                    break;
                case 'blocked':
                    $stats['blocked']++;
                    break;
                case 'urgent':
                    $stats['urgent']++;
                    break;
            }
        }
        
        return $stats;
    }
    
    /**
     * Actualizează o sarcină
     */
    public function updateTask($taskId, $data) {
        $taskIndex = array_search($taskId, array_column($this->db['tasks'], 'id'));
        
        if ($taskIndex === false) {
            return ['success' => false, 'message' => 'Sarcina nu a fost găsită'];
        }
        
        // RC#1: Dacă sarcina este marcată ca finalizată, aceasta va fi exclusă din listă
        if (isset($data['status']) && $data['status'] === 'completed') {
            $this->db['tasks'][$taskIndex]['status'] = 'completed';
            $this->db['tasks'][$taskIndex]['progress'] = 100;
            $this->db['tasks'][$taskIndex]['completedAt'] = date('Y-m-d H:i:s');
            
            // Log activitate finalizare
            $this->logActivity($taskId, 'task_completed', 'Sarcină finalizată cu succes');
            
            return [
                'success' => true,
                'message' => 'Sarcină finalizată și eliminată din listă',
                'data' => $this->db['tasks'][$taskIndex]
            ];
        }
        
        // Actualizare normală
        foreach ($data as $field => $value) {
            if (isset($this->db['tasks'][$taskIndex][$field])) {
                $this->db['tasks'][$taskIndex][$field] = $value;
            }
        }
        
        $this->db['tasks'][$taskIndex]['updatedAt'] = date('Y-m-d H:i:s');
        
        return [
            'success' => true,
            'message' => 'Sarcină actualizată cu succes',
            'data' => $this->db['tasks'][$taskIndex]
        ];
    }
    
    /**
     * Gestionare apeluri call center
     */
    public function startCall($taskId, $phoneNumber) {
        $call = [
            'id' => count($this->db['call_logs']) + 1,
            'taskId' => $taskId,
            'agentId' => $this->agentId,
            'phoneNumber' => $phoneNumber,
            'status' => 'in_progress',
            'startTime' => date('Y-m-d H:i:s'),
            'notes' => ''
        ];
        
        $this->db['call_logs'][] = $call;
        
        return [
            'success' => true,
            'message' => 'Apel inițiat cu succes',
            'data' => $call
        ];
    }
    
    public function endCall($callId, $duration, $notes = '', $outcome = 'completed') {
        $callIndex = array_search($callId, array_column($this->db['call_logs'], 'id'));
        
        if ($callIndex === false) {
            return ['success' => false, 'message' => 'Apelul nu a fost găsit'];
        }
        
        $this->db['call_logs'][$callIndex]['duration'] = $duration;
        $this->db['call_logs'][$callIndex]['notes'] = $notes;
        $this->db['call_logs'][$callIndex]['status'] = $outcome;
        $this->db['call_logs'][$callIndex]['endTime'] = date('Y-m-d H:i:s');
        
        // Actualizare automată progres sarcină după apel
        $taskId = $this->db['call_logs'][$callIndex]['taskId'];
        if ($outcome === 'completed') {
            $this->updateTaskProgress($taskId, 25); // +25% progres după apel reușit
        }
        
        return [
            'success' => true,
            'message' => 'Apel finalizat cu succes',
            'data' => $this->db['call_logs'][$callIndex]
        ];
    }
    
    private function updateTaskProgress($taskId, $increment) {
        $taskIndex = array_search($taskId, array_column($this->db['tasks'], 'id'));
        if ($taskIndex !== false) {
            $currentProgress = $this->db['tasks'][$taskIndex]['progress'];
            $newProgress = min(100, $currentProgress + $increment);
            $this->db['tasks'][$taskIndex]['progress'] = $newProgress;
            
            // Dacă sarcina ajunge la 100%, o marcăm ca finalizată
            if ($newProgress >= 100) {
                $this->db['tasks'][$taskIndex]['status'] = 'completed';
            }
        }
    }
    
    /**
     * Rapoarte pentru agenți
     */
    public function getAgentReport($date = null) {
        $date = $date ?: date('Y-m-d');
        
        $todaysCalls = array_filter($this->db['call_logs'], function($call) use ($date) {
            return strpos($call['timestamp'] ?? $call['startTime'], $date) === 0;
        });
        
        $completedCalls = array_filter($todaysCalls, function($call) {
            return $call['status'] === 'completed';
        });
        
        $totalDuration = array_sum(array_column($completedCalls, 'duration'));
        
        return [
            'success' => true,
            'data' => [
                'date' => $date,
                'totalCalls' => count($todaysCalls),
                'completedCalls' => count($completedCalls),
                'successRate' => count($todaysCalls) > 0 ? round((count($completedCalls) / count($todaysCalls)) * 100, 1) : 0,
                'totalDuration' => $totalDuration,
                'averageDuration' => count($completedCalls) > 0 ? round($totalDuration / count($completedCalls)) : 0
            ]
        ];
    }
    
    private function logActivity($entityId, $action, $details) {
        // Log în sistem pentru audit trail
        error_log("[EspoCRM] Agent {$this->agentId} - Action: {$action} - Entity: {$entityId} - Details: {$details}");
    }
}

// Router API
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$segments = explode('/', trim($path, '/'));

$api = new EspoCRMTaskAPI();

try {
    switch ($method) {
        case 'GET':
            if (end($segments) === 'tasks') {
                $filters = $_GET;
                echo json_encode($api->getTasks($filters));
            } elseif (end($segments) === 'stats') {
                echo json_encode($api->getTaskStats());
            } elseif (end($segments) === 'report') {
                $date = $_GET['date'] ?? null;
                echo json_encode($api->getAgentReport($date));
            } else {
                throw new Exception('Endpoint not found');
            }
            break;
            
        case 'PUT':
            if ($segments[count($segments)-2] === 'tasks') {
                $taskId = intval(end($segments));
                $data = json_decode(file_get_contents('php://input'), true);
                echo json_encode($api->updateTask($taskId, $data));
            } else {
                throw new Exception('Endpoint not found');
            }
            break;
            
        case 'POST':
            if (end($segments) === 'start-call') {
                $data = json_decode(file_get_contents('php://input'), true);
                echo json_encode($api->startCall($data['taskId'], $data['phoneNumber']));
            } elseif (end($segments) === 'end-call') {
                $data = json_decode(file_get_contents('php://input'), true);
                echo json_encode($api->endCall($data['callId'], $data['duration'], $data['notes'] ?? '', $data['outcome'] ?? 'completed'));
            } else {
                throw new Exception('Endpoint not found');
            }
            break;
            
        default:
            throw new Exception('Method not allowed');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>