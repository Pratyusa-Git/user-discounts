<?php

namespace Devpratyusa\UserDiscounts\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Devpratyusa\UserDiscounts\Models\DiscountAudit;

class DiscountApplied
{
    use Dispatchable, SerializesModels;

    public function __construct(public DiscountAudit $audit) {}
}