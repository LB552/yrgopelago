# Revered Hotel Booking System

A modern, interactive hotel booking platform for **Revered**, a 2-star hotel offering multiple room tiers and premium amenities.

## Features

### 🛏️ Room Tiers
- **Economy**: 2 credits per night
- **Standard**: 3 credits per night
- **Luxury**: 4 credits per night

### ✨ Premium Amenities
- **Yahtzee Gaming**: 3 credits
- **Bicycle Rentals**: 2 credits
- **Bundle Discount**: Book both Yahtzee and Bicycle together in Economy rooms for only 4 credits (save 1 credit!)

### 📅 Interactive Calendar
- Visual May 2026 calendar with real-time availability
- Booked dates highlighted in red
- Selected date range highlighted with golden borders
- Automatic cost calculation as you select dates

### 💳 Secure Payment Integration
- Integrated with Central Bank API for secure transactions
- Transfer code validation
- Automatic deposit processing
- Receipt generation for all bookings

### 📊 Booking Management
- Real-time room availability checking
- Prevention of double-booking conflicts
- JSON-based booking storage for reliability
- Consistent availability display across all devices

## How to Book

1. **Select Your Room**: Choose from Economy, Standard, or Luxury tiers
2. **Pick Your Dates**: Click on the calendar to select check-in and check-out dates
3. **Add Amenities**: Optionally select Yahtzee gaming and/or bicycle rentals
4. **Review Cost**: See the total cost calculated in real-time
5. **Enter Details**: Provide your username and Central Bank transfer code
6. **Confirm**: Submit your booking for instant processing

## Technical Stack

- **Frontend**: HTML5, CSS3, Vanilla JavaScript
- **Backend**: PHP 8.2
- **Storage**: JSON file-based persistence
- **Payment API**: Central Bank REST API integration
- **Hosting**: one.com PHP hosting

## Project Structure

```
yrgopelag2/
├── index.php              # Main booking page with calendar and form
├── booking.js             # Frontend logic: calculations, UI updates, calendar fetching
├── style.css              # Styling for layout, calendar, and form
├── config.php             # Configuration: hotel info, room/feature pricing
├── process_booking.php    # Backend: booking validation, payment processing
├── get_availability.php   # API endpoint: returns booked dates for room tiers
├── bookings.json          # Persistent booking storage
└── README.md              # This file
```

## Key Technologies

- **Strict Type Declarations**: All PHP files use `declare(strict_types=1)` for type safety
- **RESTful API Integration**: Communicates with Central Bank for payment validation and deposits
- **JSON Storage**: Lightweight, file-based alternative to database
- **Responsive Design**: Works across desktop browsers

## Booking Flow

1. User selects room tier, dates, and amenities
2. Frontend validates selection and fetches current availability
3. User submits with username and transfer code
4. Backend validates:
   - Room availability for selected dates
   - Transfer code validity with Central Bank
5. Receipt is posted to Central Bank
6. Booking is stored in JSON file
7. Payment is deposited
8. Calendar immediately reflects the new booking

## API Integration

### Central Bank Endpoints
- `/centralbank/transferCode` - Validate transfer codes
- `/centralbank/receipt` - Post booking receipt
- `/centralbank/deposit` - Process payment deposits

## File Permissions

Ensure `bookings.json` has write permissions (644 or 755) on the server for booking persistence.

---

**Hotel Name**: Revered  
**Star Rating**: ⭐⭐  
**Property**: yrgopelag2
