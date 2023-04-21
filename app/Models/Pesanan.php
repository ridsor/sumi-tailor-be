<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pesanan extends Model
{
    use HasFactory;
    
    protected $table = 'pesanan';
    protected $guarded = ['id'];

    public function getCompanyID()
    {
        return sprintf('ST%03d', $this->id);
    }
}
