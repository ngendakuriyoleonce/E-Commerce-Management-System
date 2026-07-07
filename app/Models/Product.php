<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'images', 'price', 'is_stock', 'is_active', 'is_featured', 'on_sale', 'category_id', 'brand_id'];

    protected $casts = [
        'images' => 'array',
        'is_stock' => 'boolean',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'on_sale' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
