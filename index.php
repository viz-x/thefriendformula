<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

// Router
$page = $_GET['page'] ?? 'home';
$action = $_POST['action'] ?? $_GET['action'] ?? null;

// Handle actions
if ($action) {
    switch ($action) {
        case 'consent_agree':
            $_SESSION['consent'] = true;
            header('Location: ?page=onboarding');
            exit;
            
        case 'consent_decline':
            header('Location: https://google.com');
            exit;
            
        case 'create_profile':
            handleCreateProfile();
            break;
            
        case 'logout':
            session_destroy();
            header('Location: ?page=home');
            exit;
            
        case 'delete_profile':
            handleDeleteProfile();
            break;
            
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

// Route to pages
if ($page === 'admin') {
    include 'admin.php';
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>thefriendformula</title>
    <meta name="description" content="Identity-based social discovery.">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#000000',
                        secondary: '#1a1a1a',
                        accent: '#333333',
                        'off-white': '#f5f5f7',
                    },
                    fontFamily: {
                        sans: ['Inter', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'sans-serif'],
                    },
                },
            },
        }
    </script>
    <style>
        body {
            background-color: #f5f5f7;
            color: #1d1d1f;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        ::-webkit-scrollbar { display: none; }
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in {
            animation: fadeIn 0.3s ease-out;
        }
    </style>
</head>
<body>
<?php
// Show appropriate page
if (!isset($_SESSION['consent']) || $_SESSION['consent'] !== true) {
    include 'views/consent.php';
} elseif (!isset($_SESSION['user_id'])) {
    include 'views/onboarding.php';
} elseif ($page === 'settings') {
    include 'views/settings.php';
} else {
    include 'views/feed.php';
}
?>
</body>
</html>
