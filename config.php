<?php
// Database configuration
define('DB_FILE', __DIR__ . '/data/users.json');
define('UPLOADS_DIR', __DIR__ . '/uploads/');
define('ADMIN_PASSWORD', 'admin123'); // Change in production!

// Mock cities
define('MOCK_CITIES', [
    'New York', 'Los Angeles', 'Tokyo', 'London', 
    'Paris', 'Berlin', 'Sydney', 'Toronto'
]);

// Ensure directories exist
if (!file_exists(dirname(DB_FILE))) {
    mkdir(dirname(DB_FILE), 0755, true);
}
if (!file_exists(UPLOADS_DIR)) {
    mkdir(UPLOADS_DIR, 0755, true);
}

// Initialize database if not exists
if (!file_exists(DB_FILE)) {
    file_put_contents(DB_FILE, json_encode([
        'users' => [],
        'moderation_enabled' => false
    ]));
}
