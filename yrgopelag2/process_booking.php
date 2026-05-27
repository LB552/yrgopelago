<?php

declare(strict_types=1);
error_log("Booking received: " . json_encode($_POST ?? json_decode(file_get_contents('php://input'), true)));

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

$db = new SQLite3('bookings.db');

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

// Check room availability
$result = $db->querySingle("
    SELECT COUNT(*) as count FROM bookings 
    WHERE room_tier = '" . $db->escapeString($roomTier) . "' 
    AND (
        (check_in < '$checkOut' AND check_out > '$checkIn')
    )
", SQLITE3_ASSOC);

if ($result['count'] > 0) {
    echo json_encode(['error' => 'Room not available for selected dates']);
    $db->close();
    exit;
}

// Validate transfer code
$validate = cbRequest('/centralbank/transferCode', [
    'transferCode' => $input['transferCode'],
    'totalCost' => $input['totalCost']
]);

if (!$validate || isset($validate['error'])) {
    echo json_encode(['error' => $validate['error'] ?? 'Invalid transfer code']);
    $db->close();
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
    $db->close();
    exit;
}

// Store booking
$featuresJson = json_encode($input['features'] ?? []);
$stmt = $db->prepare("
    INSERT INTO bookings (username, room_tier, check_in, check_out, features, total_cost, transfer_code)
    VALUES (:username, :room_tier, :check_in, :check_out, :features, :total_cost, :transfer_code)
");
$stmt->bindValue(':username', $input['username'], SQLITE3_TEXT);
$stmt->bindValue(':room_tier', $roomTier, SQLITE3_TEXT);
$stmt->bindValue(':check_in', $checkIn, SQLITE3_TEXT);
$stmt->bindValue(':check_out', $checkOut, SQLITE3_TEXT);
$stmt->bindValue(':features', $featuresJson, SQLITE3_TEXT);
$stmt->bindValue(':total_cost', $input['totalCost'], SQLITE3_INTEGER);
$stmt->bindValue(':transfer_code', $input['transferCode'], SQLITE3_TEXT);
$stmt->execute();

// Deposit payment
$deposit = cbRequest('/centralbank/deposit', [
    'user' => HOTEL_OWNER,
    'transferCode' => $input['transferCode']
]);

if (!$deposit || isset($deposit['error'])) {
    echo json_encode(['error' => $deposit['error'] ?? 'Deposit failed']);
    $db->close();
    exit;
}

$db->close();
echo json_encode(['status' => 'success']);
