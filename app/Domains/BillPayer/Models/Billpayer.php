<?php

namespace App\Domains\Billpayer\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Vanilo\Order\Models\Billpayer as VaniloBillpayer;

/**
 * Class Billpayer.
 */
class Billpayer extends VaniloBillpayer
{
    // Add soft deleted to BillPayer
    use SoftDeletes;
}
