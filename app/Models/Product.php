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

    protected $appends = [
        'images'
    ];

    protected $hidden = ['media'];

    public function getImagesAttribute()
    {
        $images = $this->getMedia('products');

        return $images->map(function ($image) {
            return [
                'id' => $image->id,
                'url' => $image->getUrl(),
                'name' => $image->filename
            ];
        })->toArray();
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
