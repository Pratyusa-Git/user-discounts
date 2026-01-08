<?php

namespace Devpratyusa\UserDiscounts\Facades;

use Illuminate\Support\Facades\Facade;

class UserDiscounts extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Devpratyusa\UserDiscounts\Services\DiscountManager::class;
    }
}