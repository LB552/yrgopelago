<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);
header('Content-Type: application/json');

const CENTRAL_BANK = 'https://www.yrgopelag.se';
const HOTEL_OWNER = 'Anton';
const HOTEL_API_KEY = 'e95ad03a-e09b-44e1-b239-b91427a0ddc8';

const ROOM_PRICES = ['economy' => 2, 'standard' => 3, 'luxury' => 4];

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['error' => 'Invalid JSON input']);
    exit;
}

function cbRequest($endpoint, $data)
{
    $ctx = stream_context_create(['http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => json_encode($data)
    ]]);
    $res = @file_get_contents(CENTRAL_BANK . $endpoint, false, $ctx);
    return json_decode($res, true);
}

$checkIn = $input['checkIn'];
$checkOut = $input['checkOut'];
$roomTier = $input['roomTier'];

// Load bookings from JSON
$bookings = json_decode(file_get_contents('bookings.json'), true) ?? [];

// Check room availability
$conflict = false;
foreach ($bookings as $booking) {
    if ($booking['room_tier'] === $roomTier) {
        if (!(strtotime($checkOut) <= strtotime($booking['check_in']) || strtotime($checkIn) >= strtotime($booking['check_out']))) {
            $conflict = true;
            break;
        }
    }
}

if ($conflict) {
    echo json_encode(['error' => 'Room not available for selected dates']);
    exit;
}

// Validate transfer code
$validate = cbRequest('/centralbank/transferCode', [
    'transferCode' => $input['transferCode'],
    'totalCost' => $input['totalCost']
]);

if (!$validate || isset($validate['error'])) {
    echo json_encode(['error' => $validate['error'] ?? 'Invalid transfer code']);
    exit;
}

// Post receipt
$receiptRes = cbRequest('/centralbank/receipt', [
    'user' => HOTEL_OWNER,
    'api_key' => HOTEL_API_KEY,
    'guest_name' => $input['username'],
    'arrival_date' => $checkIn,
    'departure_date' => $checkOut,
    'features_used' => array_map(fn($f) => ['activity' => $f, 'tier' => $roomTier], $input['features'] ?? [])
]);

if (isset($receiptRes['error'])) {
    echo json_encode(['error' => 'Receipt failed: ' . $receiptRes['error']]);
    exit;
}

// Store booking in JSON
$newBooking = [
    'username' => $input['username'],
    'room_tier' => $roomTier,
    'check_in' => $checkIn,
    'check_out' => $checkOut,
    'features' => $input['features'] ?? [],
    'total_cost' => $input['totalCost'],
    'transfer_code' => $input['transferCode'],
    'created_at' => date('Y-m-d H:i:s')
];

$bookings[] = $newBooking;
file_put_contents('bookings.json', json_encode($bookings, JSON_PRETTY_PRINT));

// Deposit payment
$deposit = cbRequest('/centralbank/deposit', [
    'user' => HOTEL_OWNER,
    'transferCode' => $input['transferCode']
]);

if (!$deposit || isset($deposit['error'])) {
    echo json_encode(['error' => $deposit['error'] ?? 'Deposit failed']);
    exit;
}

echo json_encode(['status' => 'success']);
