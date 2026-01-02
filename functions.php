<?php
// Database helpers
function loadDatabase() {
    $json = file_get_contents(DB_FILE);
    return json_decode($json, true);
}

function saveDatabase($data) {
    file_put_contents(DB_FILE, json_encode($data, JSON_PRETTY_PRINT));
}

function getAllUsers() {
    $db = loadDatabase();
    return $db['users'] ?? [];
}

function getUserById($id) {
    $users = getAllUsers();
    foreach ($users as $user) {
        if ($user['id'] === $id) return $user;
    }
    return null;
}

function isModerationEnabled() {
    $db = loadDatabase();
    return $db['moderation_enabled'] ?? false;
}

// User actions
function handleCreateProfile() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
    
    $username = trim($_POST['username'] ?? '');
    $city = trim($_POST['city'] ?? 'Unknown Region');
    
    // Validate username
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username) || strlen($username) < 3) {
        $_SESSION['error'] = 'Invalid username';
        header('Location: ?page=onboarding');
        exit;
    }
    
    // Handle photo upload
    $photoUrl = '';
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $ext;
        $destination = UPLOADS_DIR . $filename;
        
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $destination)) {
            $photoUrl = 'uploads/' . $filename;
        }
    }
    
    if (empty($photoUrl)) {
        $_SESSION['error'] = 'Photo required';
        header('Location: ?page=onboarding');
        exit;
    }
    
    // Detect device
    $ua = $_SERVER['HTTP_USER_AGENT'];
    $deviceType = (preg_match('/(iPad|iPhone|iPod|Macintosh|Mac OS X)/i', $ua)) ? 'apple' : null;
    
    // Create user
    $db = loadDatabase();
    $userId = bin2hex(random_bytes(16));
    
    $newUser = [
        'id' => $userId,
        'instagram_username' => $username,
        'photo_url' => $photoUrl,
        'city' => $city,
        'device_type' => $deviceType,
        'created_at' => date('c'),
        'last_active' => date('c'),
        'ip_hash' => md5($_SERVER['REMOTE_ADDR'] ?? ''),
        'banned' => false,
        'approved' => !isModerationEnabled()
    ];
    
    $db['users'][] = $newUser;
    saveDatabase($db);
    
    $_SESSION['user_id'] = $userId;
    header('Location: ?page=feed');
    exit;
}

function handleDeleteProfile() {
    if (!isset($_SESSION['user_id'])) return;
    
    $db = loadDatabase();
    $userId = $_SESSION['user_id'];
    
    // Remove user
    $db['users'] = array_filter($db['users'], function($u) use ($userId) {
        return $u['id'] !== $userId;
    });
    $db['users'] = array_values($db['users']);
    
    saveDatabase($db);
    session_destroy();
    header('Location: ?page=home');
    exit;
}

function getRandomProfile($excludeId = null) {
    $users = getAllUsers();
    
    // Filter suitable profiles
    $filtered = array_filter($users, function($u) use ($excludeId) {
        return $u['id'] !== $excludeId 
            && !$u['banned'] 
            && $u['approved'];
    });
    
    $filtered = array_values($filtered);
    
    if (empty($filtered)) return null;
    
    return $filtered[array_rand($filtered)];
}

// Admin actions
function handleAdminLogin() {
    if ($_POST['password'] === ADMIN_PASSWORD) {
        $_SESSION['admin'] = true;
        header('Location: ?page=admin');
    } else {
        $_SESSION['error'] = 'Invalid password';
        header('Location: ?page=admin');
    }
    exit;
}

function handleToggleBan() {
    if (!isset($_SESSION['admin'])) return;
    
    $userId = $_POST['user_id'] ?? null;
    if (!$userId) return;
    
    $db = loadDatabase();
    foreach ($db['users'] as &$user) {
        if ($user['id'] === $userId) {
            $user['banned'] = !$user['banned'];
            break;
        }
    }
    saveDatabase($db);
    header('Location: ?page=admin');
    exit;
}

function handleToggleApproval() {
    if (!isset($_SESSION['admin'])) return;
    
    $userId = $_POST['user_id'] ?? null;
    if (!$userId) return;
    
    $db = loadDatabase();
    foreach ($db['users'] as &$user) {
        if ($user['id'] === $userId) {
            $user['approved'] = !$user['approved'];
            break;
        }
    }
    saveDatabase($db);
    header('Location: ?page=admin');
    exit;
}

function handleDeleteUser() {
    if (!isset($_SESSION['admin'])) return;
    
    $userId = $_POST['user_id'] ?? null;
    if (!$userId) return;
    
    $db = loadDatabase();
    $db['users'] = array_filter($db['users'], function($u) use ($userId) {
        return $u['id'] !== $userId;
    });
    $db['users'] = array_values($db['users']);
    
    saveDatabase($db);
    header('Location: ?page=admin');
    exit;
}

function handleToggleModeration() {
    if (!isset($_SESSION['admin'])) return;
    
    $db = loadDatabase();
    $db['moderation_enabled'] = !($db['moderation_enabled'] ?? false);
    saveDatabase($db);
    header('Location: ?page=admin');
    exit;
}

function getAdminStats() {
    $users = getAllUsers();
    
    $stats = [
        'total_users' => count($users),
        'total_apple_users' => 0,
        'active_users_count' => 0,
        'banned_count' => 0,
        'top_cities' => []
    ];
    
    $cityMap = [];
    $activeThreshold = strtotime('-30 days');
    
    foreach ($users as $user) {
        if ($user['device_type'] === 'apple') {
            $stats['total_apple_users']++;
        }
        if (strtotime($user['last_active']) > $activeThreshold) {
            $stats['active_users_count']++;
        }
        if ($user['banned']) {
            $stats['banned_count']++;
        }
        
        $city = $user['city'];
        $cityMap[$city] = ($cityMap[$city] ?? 0) + 1;
    }
    
    arsort($cityMap);
    $topCities = [];
    $count = 0;
    foreach ($cityMap as $city => $num) {
        if ($count++ >= 5) break;
        $topCities[] = ['city' => $city, 'count' => $num];
    }
    $stats['top_cities'] = $topCities;
    
    return $stats;
}
