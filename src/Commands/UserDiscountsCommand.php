<?php

namespace App\UserDiscounts\Commands;

use Illuminate\Console\Command;

class UserDiscountsCommand extends Command
{
    public $signature = 'user-discounts';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
