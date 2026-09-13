<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;

class ExpireProductDiscounts extends Command
{
    protected $signature = 'discounts:expire';

    protected $description = 'Zero out product discount percentages whose window has ended.';

    public function handle(): int
    {
        $count = Product::expireExpiredDiscounts();

        $this->info("Expired {$count} product discount(s).");

        return self::SUCCESS;
    }
}
