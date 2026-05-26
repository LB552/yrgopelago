<?php require_once 'config.php'; ?>

<!DOCTYPE html>
<html>

<head>
    <title>Hotel Booking</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <h1>Revered</h1>

    <div class="stars">
        <?php
        for ($i = 0; $i < HOTEL_STAR_RATING; $i++) {
            echo "★";
        }

        for ($i = HOTEL_STAR_RATING; $i < 5; $i++) {
            echo "☆";
        }
        ?>
    </div>

    <p>Special offer for budget rooms: Select both yahtzee and bicycle to save 1 credit</p>

    <?php
    $day = 1;
    for ($i = 0; $i < 6; $i++) { ?>
        <div class="hori">
            <?php for ($j = 0; $j < 7; $j++) {

                $class = 'date';
                $content = '';
                $dateAttr = '';

                // Days (mon-sun)
                if ($i === 0) {
                    $class = 'dayDate';
                }

                // First 4 squares on top row (Mon-Thu, before Fri May 1)
                if ($i === 1 && $j < 4) {
                    $class = 'dullDate';
                }

                // Put day number only on valid dates
                if ($class === 'date' && $day <= 31) {
                    $content = $day;
                    $dateAttr = 'data-date="2026-05-' . str_pad($day, 2, '0', STR_PAD_LEFT) . '"';
                    $day++;
                } else if ($class === 'date' && $day > 31) {
                    $class = 'dullDate';
                }

                // Put days (mon-sun)
                $weekday = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
                if ($class === 'dayDate' && $i === 0) {
                    $content = $weekday[$j];
                }
            ?>
                <div class="<?= $class ?>" <?= $dateAttr ?>><?= $content ?></div>
            <?php } ?>
        </div>
    <?php } ?>

    <form id="bookingForm">

        <h3>Rooms</h3>
        <div id="roomsContainer"></div>

        <label>
            Check-in:
            <input type="date" id="checkIn" required>
        </label>

        <label>
            Check-out:
            <input type="date" id="checkOut" required>
        </label>

        <h3>Features</h3>
        <div id="featuresContainer"></div>

        <script>
            window.APP_CONFIG = {
                roomPrices: <?= json_encode(ROOM_PRICES) ?>,
                featurePrices: <?= json_encode(FEATURE_PRICES) ?>,
                featureGrid: <?= json_encode(FEATURE_GRID) ?>,

                // whitelist
                allowedFeatures: [
                    "yahtzee",
                    "bicycle"
                ]
            };
        </script>

        <label>
            Username:
            <input type="text" id="username" required>
        </label>

        <label>
            Transfer Code:
            <input type="text" id="transferCode" required>
        </label>

        <p>
            Room:
            <span id="roomCost">0</span>
            ×
            <span id="nights">0</span>
            =
            <span id="roomTotal">0</span>
        </p>

        <p>
            Features:
            <span id="featureCost">0</span>
        </p>

        <p>
            <strong>Total:
                <span id="totalCost">0</span>
            </strong>
        </p>

        <button type="submit">Submit</button>

        <div id="message"></div>

    </form>

    <script src="booking.js"></script>

</body>

</html>