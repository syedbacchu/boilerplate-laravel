<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserVerificationCode extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'code',
        'status',
        'type',
        'expired_at'
    ];
}
