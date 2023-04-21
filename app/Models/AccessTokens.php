<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccessTokens extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $with = ['users'];

    public function user() {
        return $this->belongsTo(User::class);
    }
}
