<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuids;

class FeaturedProuctPurchase extends Model
{
    use Uuids;
    protected $table = 'featured_prouct_purchases';
    protected $guarded = [];
}
