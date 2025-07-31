<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$response = [
    'success' => true,
    'message' => 'XtX Track PHP API is working',
    'timestamp' => date('c'),
    'php_version' => phpversion(),
    'server_info' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
    'api_endpoints' => [
        'track_shipment' => 'api/shipments.php?path=track&tracking=TRACKING_NUMBER',
        'admin_login' => 'api/admin.php?path=login',
        'get_all_shipments' => 'api/shipments.php?path=all',
        'get_stats' => 'api/shipments.php?path=stats'
    ]
];

echo json_encode($response, JSON_PRETTY_PRINT);
?>
