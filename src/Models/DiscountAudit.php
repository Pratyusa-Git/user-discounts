<?php

namespace Devpratyusa\UserDiscounts\Models;

use Illuminate\Database\Eloquent\Model;

class DiscountAudit extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'applied_discounts' => 'array',
        'skipped_discounts' => 'array',
    ];
}
