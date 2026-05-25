<?php
header('Content-Type: application/json');

$roomTier = $_GET['roomTier'] ?? '';

if (!$roomTier) {
    echo json_encode(['error' => 'No room tier specified']);
    exit;
}

$db = new SQLite3('bookings.db');

$result = $db->query("
    SELECT check_in, check_out FROM bookings 
    WHERE room_tier = '" . $db->escapeString($roomTier) . "'
");

$bookedDates = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $current = strtotime($row['check_in']);
    $end = strtotime($row['check_out']);

    while ($current <= $end) {
        $bookedDates[] = date('Y-m-d', $current);
        $current += 86400;
    }
}

$db->close();
echo json_encode(['bookedDates' => $bookedDates]);
