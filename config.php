<?php
// Database configuration
define('DB_PATH', __DIR__ . '/../todos.db');

// Create database and table if they don't exist
function initDB() {
    $db = new SQLite3(DB_PATH);
    $db->exec('CREATE TABLE IF NOT EXISTS todos (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        task TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        completed BOOLEAN DEFAULT 0
    )');
    return $db;
}
?>
