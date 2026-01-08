<?php

return [
    /*
    | Stacking order for eligible discounts
    | 'priority' → uses stacking_priority (lower first)
    | 'created_at' → oldest first
    */
    'stacking_order' => 'priority',

    // Maximum total percentage discount allowed (e.g., 80 = 80%)
    'max_percentage_cap' => 80,

    // Rounding mode (PHP_ROUND_HALF_UP, PHP_ROUND_HALF_DOWN, etc.)
    'rounding_mode' => PHP_ROUND_HALF_UP,

    // Decimal precision for final amount
    'precision' => 2,
];