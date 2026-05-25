<?php
$db = new SQLite3('bookings.db');

$db->exec('CREATE TABLE IF NOT EXISTS bookings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL,
    room_tier TEXT NOT NULL,
    check_in DATE NOT NULL,
    check_out DATE NOT NULL,
    features TEXT,
    total_cost INTEGER NOT NULL,
    transfer_code TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)');

$db->close();
echo "Database initialized";
