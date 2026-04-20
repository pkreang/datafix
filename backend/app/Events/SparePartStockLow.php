<?php

namespace App\Events;

use App\Models\SparePart;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

class SparePartStockLow implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(
        public readonly SparePart $sparePart,
    ) {}
}
