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

    for (const [tier, price] of Object.entries(ROOM_PRICES)) {
        const label = document.createElement('label');

        label.className = 'hori';

        label.innerHTML = `
            <input
                type="radio"
                name="roomTier"
                value="${tier}"
                ${tier === 'budget' ? 'required' : ''}
            >
            ${capitalize(tier)} (${price}/night)
        `;

        container.appendChild(label);
    }
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
    const roomTier = document.querySelector('input[name="roomTier"]:checked')?.value;

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

    // Bundle discount: if both yahtzee and bicycle, save 1 credit
    if (features.includes('yahtzee') && features.includes('bicycle')) {
        featureCost -= 1;
    }

    const total = roomTotal + featureCost;

    roomCostEl.textContent = roomCost;
    nightsEl.textContent = nights;
    roomTotalEl.textContent = roomTotal;
    featureCostEl.textContent = featureCost;
    totalEl.textContent = total;
}


// -------------------------
// EVENT LISTENERS
// -------------------------
document.querySelectorAll(
    '#checkIn, #checkOut, input[name="roomTier"], input[name="features"]'
).forEach(el => el.addEventListener('change', updateUI));

updateUI();


// -------------------------
// SUBMIT
// -------------------------
document.getElementById('bookingForm')
.addEventListener('submit', async (e) => {
    e.preventDefault();

    const roomTier = document.querySelector('input[name="roomTier"]:checked')?.value;
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