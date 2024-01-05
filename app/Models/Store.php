<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use HasFactory;

    protected $fillables = [
        'name',
        'category',
        'time',
        'logo',
        'cover',
        'minimum_order',
        'delivery_chage',
    ];
}
