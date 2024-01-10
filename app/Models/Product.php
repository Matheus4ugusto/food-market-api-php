<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Plank\Mediable\Mediable;

class Product extends Model
{
    use HasFactory;
    use Mediable;

    protected $fillable = [
        'store_id',
        'name',
        'description',
        'price',
        'status',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
