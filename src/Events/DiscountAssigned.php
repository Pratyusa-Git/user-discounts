<?php

namespace Devpratyusa\UserDiscounts\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DiscountAssigned
{
    use Dispatchable, SerializesModels;

    public function __construct(public $user, public $discount) {}
}