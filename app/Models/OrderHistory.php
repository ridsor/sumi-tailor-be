<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderHistory extends Model
{
    use HasFactory;

    protected $table = 'order_history';
    protected $primeryKey = 'item_code';
    public $incrementing = false;
    protected $guarded = [];

    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where('item_code', $value)->firstOrFail();
    }
}
