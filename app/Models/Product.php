<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'label',
        'description',
        'price',
        'quantity',
        'image',
        'category_id',
    ];
      protected $with = ['category'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
