<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Configuration
define('DB_PATH', dirname(__DIR__) . '/db/');
define('SHIPMENTS_FILE', DB_PATH . 'shipments.json');
define('ADMINS_FILE', DB_PATH . 'admins.json');
define('SETTINGS_FILE', DB_PATH . 'settings.json');

// Utility functions
function readJsonFile($filename) {
    if (!file_exists($filename)) {
        return null;
    }

    $content = file_get_contents($filename);
    if ($content === false) {
        return null;
    }

    $data = json_decode($content, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return null;
    }

    return $data;
}

function writeJsonFile($filename, $data) {
    $data['lastUpdated'] = date('c'); // ISO 8601 format
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

    if ($json === false) {
        return false;
    }

    // Ensure directory exists
    $dir = dirname($filename);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    return file_put_contents($filename, $json) !== false;
}

function sendJsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit();
}

function sendErrorResponse($message, $statusCode = 400) {
    sendJsonResponse(['error' => $message], $statusCode);
}

function getJsonInput() {
    $input = file_get_contents('php://input');
    return json_decode($input, true);
}

// Initialize default data files if they don't exist
function initializeDataFiles() {
    // Initialize shipments.json
    if (!file_exists(SHIPMENTS_FILE)) {
        $shipmentsData = [
            'shipments' => [
                [
                    'id' => '1',
                    'trackingNumber' => 'GS-2025-001234',
                    'client' => 'Marcus Chen',
                    'description' => '24k Gold Bars (5kg)',
                    'origin' => 'Accra Vault Facility, Ghana',
                    'destination' => 'London Secure Vault, UK',
                    'status' => 'transit',
                    'value' => 325000,
                    'currency' => 'USD',
                    'serviceType' => 'Express International Shipping',
                    'eta' => '2025-07-27',
                    'createdAt' => '2025-07-22T10:00:00.000Z',
                    'updatedAt' => '2025-07-25T14:00:00.000Z',
                    'timeline' => [
                        [
                            'status' => 'storage',
                            'location' => 'Accra Vault Facility',
                            'timestamp' => '2025-07-22T10:00:00.000Z',
                            'description' => 'Gold received and verified in secure vault',
                            'notes' => 'Initial storage confirmation'
                        ],
                        [
                            'status' => 'preparing',
                            'location' => 'Accra Processing Center',
                            'timestamp' => '2025-07-24T14:30:00.000Z',
                            'description' => 'Documentation completed, customs clearance obtained',
                            'notes' => 'Ready for shipment'
                        ],
                        [
                            'status' => 'transit',
                            'location' => 'DHL Express International',
                            'timestamp' => '2025-07-25T14:00:00.000Z',
                            'description' => 'Departed Accra - Next stop: London Heathrow',
                            'notes' => 'In transit to destination'
                        ]
                    ]
                ]
            ],
            'nextTrackingId' => 11224,
            'lastUpdated' => date('c')
        ];
        writeJsonFile(SHIPMENTS_FILE, $shipmentsData);
    }

    // Initialize admins.json
    if (!file_exists(ADMINS_FILE)) {
        $adminsData = [
            'admins' => [
                [
                    'id' => '1',
                    'username' => 'midiiwo',
                    'password' => 'kwansa',
                    'role' => 'supaadmin',
                    'createdAt' => date('c'),
                    'lastLogin' => null,
                    'active' => true,
                    'permissions' => [
                        'manage_shipments',
                        'manage_admins',
                        'view_reports',
                        'export_data',
                        'system_settings'
                    ]
                ]
            ],
            'lastUpdated' => date('c')
        ];
        writeJsonFile(ADMINS_FILE, $adminsData);
    }

    // Initialize settings.json
    if (!file_exists(SETTINGS_FILE)) {
        $settingsData = [
            'app' => [
                'name' => 'Athena Security Tracking System',
                'version' => '1.0.0',
                'trackingIdPrefix' => 'GS',
                'trackingIdFormat' => 'GS-YYYY-XXXXXX'
            ],
            'statuses' => [
                [
                    'key' => 'storage',
                    'label' => 'In Storage',
                    'description' => 'Item is securely stored in vault',
                    'color' => '#6b7280'
                ],
                [
                    'key' => 'preparing',
                    'label' => 'Preparing for Shipment',
                    'description' => 'Documentation and customs clearance in progress',
                    'color' => '#f59e0b'
                ],
                [
                    'key' => 'transit',
                    'label' => 'In Transit',
                    'description' => 'Item is being transported to destination',
                    'color' => '#3b82f6'
                ],
                [
                    'key' => 'delivered',
                    'label' => 'Delivered',
                    'description' => 'Item has been successfully delivered',
                    'color' => '#10b981'
                ]
            ],
            'currencies' => ['USD', 'EUR', 'GBP', 'CHF', 'SGD', 'AED'],
            'serviceTypes' => [
                'Express International Shipping',
                'Standard International Shipping',
                'Premium Secure Transport',
                'Economy Shipping',
                'Same Day Delivery'
            ],
            'lastUpdated' => date('c')
        ];
        writeJsonFile(SETTINGS_FILE, $settingsData);
    }
}

// Initialize data files
initializeDataFiles();
?>
