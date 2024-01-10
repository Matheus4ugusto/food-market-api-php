<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'time',
        'logo',
        'cover',
        'minimum_order',
        'delivery_chage',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function logoPath()
    {
        if ($this->logo) {
            return storage_path("app/public/stores/{$this->id}/{$this->logo}");
        }

        return null;
    }

    public function coverPath()
    {
        if ($this->cover) {
            return storage_path("app/public/stores/{$this->id}/{$this->cover}");
        }
        
        return null;
    }
}
