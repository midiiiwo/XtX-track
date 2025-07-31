<?php
require_once 'config.php';

session_start();

$method = $_SERVER['REQUEST_METHOD'];
$path = isset($_GET['path']) ? $_GET['path'] : '';

switch ($method) {
    case 'GET':
        if ($path === 'session') {
            handleGetSession();
        } elseif ($path === 'all') {
            handleGetAllAdmins();
        } else {
            sendErrorResponse('Invalid endpoint');
        }
        break;

    case 'POST':
        if ($path === 'login') {
            handleLogin();
        } elseif ($path === 'logout') {
            handleLogout();
        } elseif ($path === 'create') {
            handleCreateAdmin();
        } else {
            sendErrorResponse('Invalid endpoint');
        }
        break;

    case 'DELETE':
        handleDeleteAdmin();
        break;

    default:
        sendErrorResponse('Method not allowed', 405);
}

function handleLogin() {
    $input = getJsonInput();

    if (!$input || !isset($input['username']) || !isset($input['password'])) {
        sendErrorResponse('Username and password are required');
    }

    $data = readJsonFile(ADMINS_FILE);
    if (!$data) {
        sendErrorResponse('Unable to read admin data');
    }

    foreach ($data['admins'] as &$admin) {
        if ($admin['username'] === $input['username'] &&
            $admin['password'] === $input['password'] &&
            $admin['active']) {

            // Update last login
            $admin['lastLogin'] = date('c');
            writeJsonFile(ADMINS_FILE, $data);

            // Create session
            $session = [
                'id' => $admin['id'],
                'username' => $admin['username'],
                'role' => $admin['role'],
                'permissions' => $admin['permissions'],
                'loginTime' => date('c')
            ];

            $_SESSION['admin'] = $session;

            sendJsonResponse([
                'success' => true,
                'session' => $session
            ]);
        }
    }

    sendJsonResponse([
        'success' => false,
        'message' => 'Invalid credentials'
    ], 401);
}

function handleLogout() {
    session_destroy();
    sendJsonResponse([
        'success' => true,
        'message' => 'Logged out successfully'
    ]);
}

function handleGetSession() {
    if (isset($_SESSION['admin'])) {
        sendJsonResponse([
            'success' => true,
            'session' => $_SESSION['admin']
        ]);
    } else {
        sendJsonResponse([
            'success' => false,
            'message' => 'No active session'
        ], 401);
    }
}

function handleGetAllAdmins() {
    if (!isset($_SESSION['admin']) || $_SESSION['admin']['role'] !== 'supaadmin') {
        sendErrorResponse('Unauthorized: Only super admin can view all admins', 403);
    }

    $data = readJsonFile(ADMINS_FILE);
    if (!$data) {
        sendErrorResponse('Unable to read admin data');
    }

    // Remove passwords from response
    $admins = array_map(function($admin) {
        unset($admin['password']);
        return $admin;
    }, $data['admins']);

    sendJsonResponse([
        'success' => true,
        'admins' => $admins
    ]);
}

function handleCreateAdmin() {
    if (!isset($_SESSION['admin']) || $_SESSION['admin']['role'] !== 'supaadmin') {
        sendErrorResponse('Unauthorized: Only super admin can create admins', 403);
    }

    $input = getJsonInput();

    if (!$input || !isset($input['username']) || !isset($input['password'])) {
        sendErrorResponse('Username and password are required');
    }

    $data = readJsonFile(ADMINS_FILE);
    if (!$data) {
        sendErrorResponse('Unable to read admin data');
    }

    // Check if username already exists
    foreach ($data['admins'] as $admin) {
        if ($admin['username'] === $input['username']) {
            sendErrorResponse('Username already exists');
        }
    }

    $role = isset($input['role']) ? $input['role'] : 'admin';
    $permissions = ($role === 'supaadmin') ?
        ['manage_shipments', 'manage_admins', 'view_reports', 'export_data', 'system_settings'] :
        ['manage_shipments', 'view_reports'];

    $newAdmin = [
        'id' => (string)(count($data['admins']) + 1),
        'username' => $input['username'],
        'password' => $input['password'],
        'role' => $role,
        'createdAt' => date('c'),
        'lastLogin' => null,
        'active' => true,
        'permissions' => $permissions
    ];

    $data['admins'][] = $newAdmin;

    if (writeJsonFile(ADMINS_FILE, $data)) {
        // Remove password from response
        unset($newAdmin['password']);
        sendJsonResponse([
            'success' => true,
            'admin' => $newAdmin
        ]);
    } else {
        sendErrorResponse('Failed to create admin');
    }
}

function handleDeleteAdmin() {
    if (!isset($_SESSION['admin']) || $_SESSION['admin']['role'] !== 'supaadmin') {
        sendErrorResponse('Unauthorized: Only super admin can delete admins', 403);
    }

    $adminId = isset($_GET['id']) ? $_GET['id'] : '';

    if (empty($adminId)) {
        sendErrorResponse('Admin ID is required');
    }

    $data = readJsonFile(ADMINS_FILE);
    if (!$data) {
        sendErrorResponse('Unable to read admin data');
    }

    $found = false;
    foreach ($data['admins'] as $index => $admin) {
        if ($admin['id'] === $adminId) {
            // Prevent deleting super admin
            if ($admin['role'] === 'supaadmin') {
                sendErrorResponse('Cannot delete super admin');
            }

            array_splice($data['admins'], $index, 1);
            $found = true;
            break;
        }
    }

    if (!$found) {
        sendErrorResponse('Admin not found', 404);
    }

    if (writeJsonFile(ADMINS_FILE, $data)) {
        sendJsonResponse([
            'success' => true,
            'message' => 'Admin deleted'
        ]);
    } else {
        sendErrorResponse('Failed to delete admin');
    }
}

function checkPermission($permission) {
    if (!isset($_SESSION['admin'])) {
        return false;
    }

    return in_array($permission, $_SESSION['admin']['permissions']);
}
?>
