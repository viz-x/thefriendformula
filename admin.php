<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

// Handle actions first
$action = $_POST['action'] ?? $_GET['action'] ?? null;
if ($action) {
    switch ($action) {
        case 'admin_login':
            handleAdminLogin();
            break;
        case 'toggle_ban':
            handleToggleBan();
            break;
        case 'toggle_approval':
            handleToggleApproval();
            break;
        case 'delete_user':
            handleDeleteUser();
            break;
        case 'toggle_moderation':
            handleToggleModeration();
            break;
    }
}

// Check authentication
if (!isset($_SESSION['admin'])) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Login</title>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="bg-gray-100">
        <div class="min-h-screen flex items-center justify-center">
            <form method="POST" class="bg-white p-8 rounded-xl shadow-lg space-y-4 w-full max-w-sm">
                <h2 class="text-xl font-bold">Admin Access</h2>
                <?php if (isset($_SESSION['error'])): ?>
                    <p class="text-red-500 text-sm"><?= htmlspecialchars($_SESSION['error']) ?></p>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>
                <input type="hidden" name="action" value="admin_login">
                <input 
                    type="password" 
                    name="password"
                    class="border p-2 rounded w-full"
                    placeholder="Password (admin123)"
                    required
                >
                <button type="submit" class="bg-black text-white px-4 py-2 rounded w-full">Login</button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Get data
$users = getAllUsers();
$stats = getAdminStats();
$filter = $_GET['filter'] ?? '';

// Filter users
$filteredUsers = array_filter($users, function($u) use ($filter) {
    return empty($filter) || 
           stripos($u['instagram_username'], $filter) !== false ||
           stripos($u['city'], $filter) !== false;
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white border-b p-4 flex justify-between items-center sticky top-0 z-10">
        <h1 class="font-bold text-lg">Admin Dashboard</h1>
        <a href="index.php" class="text-blue-600">Return to App</a>
    </header>

    <div class="p-6 max-w-7xl mx-auto space-y-8">
        
        <!-- Stats -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <div class="bg-white p-4 rounded-lg shadow-sm">
                <div class="text-gray-500 text-xs uppercase">Total Users</div>
                <div class="text-2xl font-bold"><?= $stats['total_users'] ?></div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-sm">
                <div class="text-gray-500 text-xs uppercase">Apple Users</div>
                <div class="text-2xl font-bold"><?= $stats['total_apple_users'] ?></div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-sm">
                <div class="text-gray-500 text-xs uppercase">Active (30d)</div>
                <div class="text-2xl font-bold"><?= $stats['active_users_count'] ?></div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-sm">
                <div class="text-gray-500 text-xs uppercase">Banned</div>
                <div class="text-2xl font-bold text-red-500"><?= $stats['banned_count'] ?></div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-sm">
                <div class="text-gray-500 text-xs uppercase">Moderation</div>
                <div class="mt-1">
                    <form method="POST" class="inline">
                        <input type="hidden" name="action" value="toggle_moderation">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input 
                                type="checkbox" 
                                <?= isModerationEnabled() ? 'checked' : '' ?>
                                onchange="this.form.submit()"
                            >
                            <span><?= isModerationEnabled() ? 'ON' : 'OFF' ?></span>
                        </label>
                    </form>
                </div>
            </div>
        </div>

        <!-- Table Controls -->
        <form method="GET" class="flex gap-4">
            <input type="hidden" name="page" value="admin">
            <input 
                type="text" 
                name="filter"
                placeholder="Search username or city..." 
                class="flex-1 p-2 border rounded"
                value="<?= htmlspecialchars($filter) ?>"
            >
            <button type="submit" class="px-4 py-2 bg-black text-white rounded">Search</button>
        </form>

        <!-- Table -->
        <div class="bg-white rounded-lg shadow overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-gray-100 border-b text-gray-600 text-xs uppercase">
                        <th class="p-4">User</th>
                        <th class="p-4">Location</th>
                        <th class="p-4">Device</th>
                        <th class="p-4">Created</th>
                        <th class="p-4">Status</th>
                        <th class="p-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($filteredUsers as $u): ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="p-4 flex items-center gap-3">
                            <img src="<?= htmlspecialchars($u['photo_url']) ?>" class="w-8 h-8 rounded bg-gray-200 object-cover">
                            <span class="font-medium">@<?= htmlspecialchars($u['instagram_username']) ?></span>
                        </td>
                        <td class="p-4 text-gray-500"><?= htmlspecialchars($u['city']) ?></td>
                        <td class="p-4 text-gray-500"><?= $u['device_type'] ?? '-' ?></td>
                        <td class="p-4 text-gray-500"><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
                        <td class="p-4">
                            <?php if ($u['banned']): ?>
                                <span class="bg-red-100 text-red-800 text-xs px-2 py-1 rounded">Banned</span>
                            <?php elseif (!$u['approved']): ?>
                                <span class="bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded">Pending</span>
                            <?php else: ?>
                                <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded">Active</span>
                            <?php endif; ?>
                        </td>
                        <td class="p-4 flex gap-2">
                            <form method="POST" class="inline">
                                <input type="hidden" name="action" value="toggle_approval">
                                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                <button type="submit" class="text-blue-600 hover:underline">
                                    <?= $u['approved'] ? 'Suspend' : 'Approve' ?>
                                </button>
                            </form>
                            <form method="POST" class="inline">
                                <input type="hidden" name="action" value="toggle_ban">
                                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                <button type="submit" class="text-orange-600 hover:underline">
                                    <?= $u['banned'] ? 'Unban' : 'Ban' ?>
                                </button>
                            </form>
                            <form method="POST" class="inline" onsubmit="return confirm('Delete permanent?')">
                                <input type="hidden" name="action" value="delete_user">
                                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                <button type="submit" class="text-red-600 hover:underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php if (empty($filteredUsers)): ?>
                <div class="p-8 text-center text-gray-400">No users found.</div>
            <?php endif; ?>
        </div>

    </div>
</body>
</html>
