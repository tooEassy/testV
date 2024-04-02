<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SdnItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'uid', 'first_name', 'last_name'
    ];
}
