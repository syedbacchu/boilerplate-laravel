<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;
    protected $fillable= [
        'title', 
        'status', 
        'actions',
        'unique_code'
    ];

    protected $appends = [
        'permissions'
    ];

    public function getPermissionsAttribute() {
        $actions = explode('|', $this->actions);
        return array_map('intval', $actions);
    }
}
