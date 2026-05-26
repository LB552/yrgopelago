<?php
header('Content-Type: application/json');

$roomTier = $_GET['roomTier'] ?? null;
if (!$roomTier) {
    echo json_encode(['bookedDates' => []]);
    exit;
}

$bookings = json_decode(file_get_contents('bookings.json'), true) ?? [];
$bookedDates = [];

foreach ($bookings as $booking) {
    if ($booking['room_tier'] === $roomTier) {
        $current = strtotime($booking['check_in']);
        $end = strtotime($booking['check_out']);

        while ($current <= $end) {
            $bookedDates[] = date('Y-m-d', $current);
            $current += 86400;
        }
    }
}

echo json_encode(['bookedDates' => array_unique($bookedDates)]);
