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