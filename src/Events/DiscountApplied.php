<?php

namespace Devpratyusa\UserDiscounts\Events;

use Devpratyusa\UserDiscounts\Models\DiscountAudit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DiscountApplied
{
    use Dispatchable, SerializesModels;

    public function __construct(public DiscountAudit $audit) {}
}
