<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonthlyTemp extends Model
{
    use HasFactory;

    protected $table = 'monthly_temp';
    protected $guarded = ['id'];
}
