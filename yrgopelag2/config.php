<?php

declare(strict_types=1);

const CENTRAL_BANK = 'https://www.yrgopelag.se';
const HOTEL_OWNER = 'Anton';
const HOTEL_API_KEY = 'e95ad03a-e09b-44e1-b239-b91427a0ddc8';
const HOTEL_STAR_RATING = 2;

const FEATURE_GRID = [
    'water' => [
        'economy' => 'pool',
        'basic' => 'scuba diving',
        'premium' => 'olympic pool',
        'superior' => 'waterpark with fire and minibar'
    ],
    'games' => [
        'economy' => 'yahtzee',
        'basic' => 'ping pong table',
        'premium' => 'PS5',
        'superior' => 'casino'
    ],
    'wheels' => [
        'economy' => 'unicycle',
        'basic' => 'bicycle',
        'premium' => 'trike',
        'superior' => 'four-wheeled motorized beast'
    ],
    'hotel-specific' => [
        'economy' => 'custom-1',
        'basic' => 'custom-2',
        'premium' => 'custom-3',
        'superior' => 'custom-4'
    ],
];

// Room prices per night
const ROOM_PRICES = [
    'budget' => 2,
    'standard' => 3,
    'luxury' => 4
];

// Feature prices
const FEATURE_PRICES = [
    'economy' => 2,
    'basic' => 3,
    'premium' => 5,
    'superior' => 8
];
