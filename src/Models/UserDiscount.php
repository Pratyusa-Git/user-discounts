<?php

namespace Devpratyusa\UserDiscounts\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDiscount extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['revoked_at' => 'datetime'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }

    public function isRevoked(): bool
    {
        return $this->revoked_at !== null;
    }
}