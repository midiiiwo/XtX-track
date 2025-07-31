<?php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];
$path = isset($_GET['path']) ? $_GET['path'] : '';

switch ($method) {
    case 'GET':
        if ($path === 'track') {
            handleTrackShipment();
        } elseif ($path === 'all') {
            handleGetAllShipments();
        } elseif ($path === 'stats') {
            handleGetStats();
        } else {
            sendErrorResponse('Invalid endpoint');
        }
        break;

    case 'POST':
        if ($path === 'create') {
            handleCreateShipment();
        } elseif ($path === 'update-status') {
            handleUpdateShipmentStatus();
        } else {
            sendErrorResponse('Invalid endpoint');
        }
        break;

    case 'PUT':
        handleUpdateShipment();
        break;

    case 'DELETE':
        handleDeleteShipment();
        break;

    default:
        sendErrorResponse('Method not allowed', 405);
}

function handleTrackShipment() {
    $trackingNumber = isset($_GET['tracking']) ? $_GET['tracking'] : '';

    if (empty($trackingNumber)) {
        sendErrorResponse('Tracking number is required');
    }

    $data = readJsonFile(SHIPMENTS_FILE);
    if (!$data) {
        sendErrorResponse('Unable to read shipments data');
    }

    foreach ($data['shipments'] as $shipment) {
        if ($shipment['trackingNumber'] === $trackingNumber) {
            sendJsonResponse([
                'success' => true,
                'shipment' => $shipment
            ]);
        }
    }

    sendJsonResponse([
        'success' => false,
        'message' => 'Shipment not found'
    ], 404);
}

function handleGetAllShipments() {
    $data = readJsonFile(SHIPMENTS_FILE);
    if (!$data) {
        sendErrorResponse('Unable to read shipments data');
    }

    sendJsonResponse([
        'success' => true,
        'shipments' => $data['shipments']
    ]);
}

function handleGetStats() {
    $data = readJsonFile(SHIPMENTS_FILE);
    if (!$data) {
        sendErrorResponse('Unable to read shipments data');
    }

    $shipments = $data['shipments'];
    $total = count($shipments);
    $inTransit = 0;
    $delivered = 0;
    $totalValue = 0;
    $statuses = [
        'storage' => 0,
        'preparing' => 0,
        'transit' => 0,
        'delivered' => 0
    ];

    foreach ($shipments as $shipment) {
        if ($shipment['status'] === 'transit') $inTransit++;
        if ($shipment['status'] === 'delivered') $delivered++;
        if (isset($shipment['value'])) $totalValue += $shipment['value'];
        if (isset($statuses[$shipment['status']])) {
            $statuses[$shipment['status']]++;
        }
    }

    sendJsonResponse([
        'success' => true,
        'stats' => [
            'totalShipments' => $total,
            'inTransit' => $inTransit,
            'delivered' => $delivered,
            'totalValue' => $totalValue,
            'statuses' => $statuses
        ]
    ]);
}

function handleCreateShipment() {
    $input = getJsonInput();

    if (!$input) {
        sendErrorResponse('Invalid JSON input');
    }

    // Validate required fields
    $required = ['client', 'description', 'origin', 'destination', 'value', 'serviceType', 'eta'];
    foreach ($required as $field) {
        if (!isset($input[$field]) || empty($input[$field])) {
            sendErrorResponse("Field '$field' is required");
        }
    }

    $data = readJsonFile(SHIPMENTS_FILE);
    if (!$data) {
        sendErrorResponse('Unable to read shipments data');
    }

    // Generate tracking number
    $year = date('Y');
    $nextId = isset($data['nextTrackingId']) ? $data['nextTrackingId'] : 1;
    $paddedId = str_pad($nextId, 6, '0', STR_PAD_LEFT);
    $trackingNumber = "GS-{$year}-{$paddedId}";

    // Create new shipment
    $newShipment = [
        'id' => (string)(count($data['shipments']) + 1),
        'trackingNumber' => $trackingNumber,
        'client' => $input['client'],
        'description' => $input['description'],
        'origin' => $input['origin'],
        'destination' => $input['destination'],
        'status' => isset($input['status']) ? $input['status'] : 'storage',
        'value' => (float)$input['value'],
        'currency' => isset($input['currency']) ? $input['currency'] : 'USD',
        'serviceType' => $input['serviceType'],
        'eta' => $input['eta'],
        'createdAt' => date('c'),
        'updatedAt' => date('c'),
        'timeline' => [
            [
                'status' => isset($input['status']) ? $input['status'] : 'storage',
                'location' => $input['origin'],
                'timestamp' => date('c'),
                'description' => 'Shipment created',
                'notes' => 'Initial shipment entry'
            ]
        ]
    ];

    $data['shipments'][] = $newShipment;
    $data['nextTrackingId'] = $nextId + 1;

    if (writeJsonFile(SHIPMENTS_FILE, $data)) {
        sendJsonResponse([
            'success' => true,
            'shipment' => $newShipment
        ]);
    } else {
        sendErrorResponse('Failed to save shipment');
    }
}

function handleUpdateShipmentStatus() {
    $input = getJsonInput();

    if (!$input || !isset($input['trackingNumber']) || !isset($input['status'])) {
        sendErrorResponse('Tracking number and status are required');
    }

    $data = readJsonFile(SHIPMENTS_FILE);
    if (!$data) {
        sendErrorResponse('Unable to read shipments data');
    }

    $found = false;
    foreach ($data['shipments'] as &$shipment) {
        if ($shipment['trackingNumber'] === $input['trackingNumber']) {
            $shipment['status'] = $input['status'];
            $shipment['updatedAt'] = date('c');

            // Add to timeline
            $shipment['timeline'][] = [
                'status' => $input['status'],
                'location' => isset($input['location']) ? $input['location'] : 'Unknown',
                'timestamp' => date('c'),
                'description' => isset($input['description']) ? $input['description'] : 'Status updated',
                'notes' => isset($input['notes']) ? $input['notes'] : ''
            ];

            $found = true;
            break;
        }
    }

    if (!$found) {
        sendErrorResponse('Shipment not found', 404);
    }

    if (writeJsonFile(SHIPMENTS_FILE, $data)) {
        sendJsonResponse([
            'success' => true,
            'message' => 'Shipment status updated'
        ]);
    } else {
        sendErrorResponse('Failed to update shipment');
    }
}

function handleUpdateShipment() {
    $input = getJsonInput();

    if (!$input || !isset($input['trackingNumber'])) {
        sendErrorResponse('Tracking number is required');
    }

    $data = readJsonFile(SHIPMENTS_FILE);
    if (!$data) {
        sendErrorResponse('Unable to read shipments data');
    }

    $found = false;
    foreach ($data['shipments'] as &$shipment) {
        if ($shipment['trackingNumber'] === $input['trackingNumber']) {
            // Update allowed fields
            $allowedFields = ['client', 'description', 'origin', 'destination', 'value', 'currency', 'serviceType', 'eta'];
            foreach ($allowedFields as $field) {
                if (isset($input[$field])) {
                    $shipment[$field] = $input[$field];
                }
            }
            $shipment['updatedAt'] = date('c');
            $found = true;
            break;
        }
    }

    if (!$found) {
        sendErrorResponse('Shipment not found', 404);
    }

    if (writeJsonFile(SHIPMENTS_FILE, $data)) {
        sendJsonResponse([
            'success' => true,
            'message' => 'Shipment updated'
        ]);
    } else {
        sendErrorResponse('Failed to update shipment');
    }
}

function handleDeleteShipment() {
    $trackingNumber = isset($_GET['tracking']) ? $_GET['tracking'] : '';

    if (empty($trackingNumber)) {
        sendErrorResponse('Tracking number is required');
    }

    $data = readJsonFile(SHIPMENTS_FILE);
    if (!$data) {
        sendErrorResponse('Unable to read shipments data');
    }

    $found = false;
    foreach ($data['shipments'] as $index => $shipment) {
        if ($shipment['trackingNumber'] === $trackingNumber) {
            array_splice($data['shipments'], $index, 1);
            $found = true;
            break;
        }
    }

    if (!$found) {
        sendErrorResponse('Shipment not found', 404);
    }

    if (writeJsonFile(SHIPMENTS_FILE, $data)) {
        sendJsonResponse([
            'success' => true,
            'message' => 'Shipment deleted'
        ]);
    } else {
        sendErrorResponse('Failed to delete shipment');
    }
}
?>
