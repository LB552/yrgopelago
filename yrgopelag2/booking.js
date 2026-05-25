function capitalize(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}

// -------------------------
// CONFIG SAFETY CHECK
// -------------------------
if (!window.APP_CONFIG) {
    throw new Error("APP_CONFIG not loaded from PHP");
}

const {
    roomPrices: ROOM_PRICES,
    featurePrices: FEATURE_PRICES,
    featureGrid: FEATURE_GRID,
    allowedFeatures = []   // safe default so it never breaks
} = window.APP_CONFIG;


// -------------------------
// FEATURE LOOKUP
// -------------------------
const FEATURE_LOOKUP = {};

for (const [category, tiers] of Object.entries(FEATURE_GRID)) {
    for (const [tier, name] of Object.entries(tiers)) {
        FEATURE_LOOKUP[name] = tier;
    }
}

// -------------------------
// RENDER ROOMS
// -------------------------
function renderRooms() {
    const container = document.getElementById('roomsContainer');
    if (!container) return;

    container.innerHTML = '';

    const select = document.createElement('select');
    select.id = 'roomTier';
    select.name = 'roomTier';
    select.required = true;

    const defaultOption = document.createElement('option');
    defaultOption.value = '';
    defaultOption.textContent = 'Select a room';
    select.appendChild(defaultOption);

    for (const [tier, price] of Object.entries(ROOM_PRICES)) {
        const option = document.createElement('option');
        option.value = tier;
        option.textContent = `${capitalize(tier)} (${price}/night)`;
        if (tier === 'budget') {
            option.selected = true;
        }
        select.appendChild(option);
    }

    container.appendChild(select);
}

renderRooms();

// -------------------------
// RENDER FEATURES
// -------------------------
function renderFeatures() {
    const container = document.getElementById('featuresContainer');
    if (!container) return;

    container.innerHTML = '';

    const allowed = new Set(allowedFeatures);

    for (const [category, tiers] of Object.entries(FEATURE_GRID)) {
        let hasAny = false;

        const section = document.createElement('div');

        const title = document.createElement('h3');
        title.textContent = category;
        section.appendChild(title);

        for (const [tier, name] of Object.entries(tiers)) {
            if (!allowed.has(name)) continue;

            hasAny = true;

            const label = document.createElement('label');

            label.className = 'hori';

            label.innerHTML = `
                <input type="checkbox" name="features" value="${name}">
                ${name} (${FEATURE_PRICES[tier]})
            `;

            section.appendChild(label);
        }

        if (hasAny) {
            container.appendChild(section);
        }
    }
}

renderFeatures();

// -------------------------
// UI UPDATE
// -------------------------
function updateUI() {
    const roomTier = document.getElementById('roomTier')?.value;

    const checkIn = document.getElementById('checkIn').value;
    const checkOut = document.getElementById('checkOut').value;

    const features = Array.from(
        document.querySelectorAll('input[name="features"]:checked')
    ).map(e => e.value);

    const roomCostEl = document.getElementById('roomCost');
    const nightsEl = document.getElementById('nights');
    const roomTotalEl = document.getElementById('roomTotal');
    const featureCostEl = document.getElementById('featureCost');
    const totalEl = document.getElementById('totalCost');

    let nights = 0;
    if (checkIn && checkOut) {
        nights = Math.max(0, Math.ceil((new Date(checkOut) - new Date(checkIn)) / 86400000));
    }

    const roomCost = roomTier ? ROOM_PRICES[roomTier] : 0;
    const roomTotal = roomCost * nights;

    let featureCost = 0;
    for (const f of features) {
        const tier = FEATURE_LOOKUP[f];
        if (tier) {
            featureCost += FEATURE_PRICES[tier];
        }
    }

    // Bundle discount: if both yahtzee and bicycle, save 1 credit (only for budget)
    if (roomTier === 'budget' && features.includes('yahtzee') && features.includes('bicycle')) {
        featureCost -= 1;
    }

    const total = roomTotal + featureCost;

    roomCostEl.textContent = roomCost;
    nightsEl.textContent = nights;
    roomTotalEl.textContent = roomTotal;
    featureCostEl.textContent = featureCost;
    totalEl.textContent = total;

    // Update calendar with booked dates for this room tier
    if (roomTier) {
        updateCalendar(roomTier);
    }

    // Update selected date range highlighting
    updateSelectedDates(checkIn, checkOut);
}

// -------------------------
// UPDATE SELECTED DATES
// -------------------------
function updateSelectedDates(checkIn, checkOut) {
    // Clear all selected classes
    document.querySelectorAll('.date').forEach(el => {
        el.classList.remove('selected');
    });

    // If only check-in is set, highlight it
    if (checkIn && !checkOut) {
        const el = document.querySelector(`[data-date="${checkIn}"]`);
        if (el) el.classList.add('selected');
        return;
    }

    // If both are set, highlight the range
    if (checkIn && checkOut) {
        const start = new Date(checkIn);
        const end = new Date(checkOut);
        let current = new Date(start);

        while (current <= end) {
            const dateStr = current.toISOString().split('T')[0];
            const el = document.querySelector(`[data-date="${dateStr}"]`);
            if (el) el.classList.add('selected');
            current.setDate(current.getDate() + 1);
        }
    }
}

// -------------------------
// UPDATE CALENDAR
// -------------------------
async function updateCalendar(roomTier) {
    const response = await fetch(`get_availability.php?roomTier=${encodeURIComponent(roomTier)}`);
    const data = await response.json();
    const bookedDates = new Set(data.bookedDates || []);

    // Clear all booked classes
    document.querySelectorAll('.date').forEach(el => {
        el.classList.remove('booked');
    });

    // Add booked class to booked dates
    document.querySelectorAll('.date[data-date]').forEach(el => {
        const date = el.getAttribute('data-date');
        if (bookedDates.has(date)) {
            el.classList.add('booked');
        }
    });
}


// -------------------------
// EVENT LISTENERS
// -------------------------
document.querySelectorAll(
    '#checkIn, #checkOut, input[name="features"]'
).forEach(el => el.addEventListener('change', updateUI));

document.addEventListener('DOMContentLoaded', () => {
    const roomSelect = document.getElementById('roomTier');
    if (roomSelect) {
        roomSelect.addEventListener('change', updateUI);
    }
});

updateUI();


// -------------------------
// SUBMIT
// -------------------------
document.getElementById('bookingForm')
.addEventListener('submit', async (e) => {
    e.preventDefault();

    const roomTier = document.getElementById('roomTier').value;
    const checkIn = document.getElementById('checkIn').value;
    const checkOut = document.getElementById('checkOut').value;

    const features = Array.from(
        document.querySelectorAll('input[name="features"]:checked')
    ).map(e => e.value);

    const username = document.getElementById('username').value;
    const transferCode = document.getElementById('transferCode').value;
    const totalCost = parseInt(document.getElementById('totalCost').textContent);

    const response = await fetch('process_booking.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            roomTier,
            checkIn,
            checkOut,
            features,
            username,
            transferCode,
            totalCost
        })
    });

    const result = await response.json();

    document.getElementById('message').textContent =
        result.error || result.status;

    if (result.receipt) {
        console.log(result.receipt);
    }
});