<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    // soft delete silme işleminde kayıt silinmez sadece silinme tarihi kaydedilir
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'quantity',
        'sku',
        'category_id',
        'is_active',
    ];


    protected $casts = [
        'price' => 'decimal:2', // fiyatın virgülden sonra 2 basamak olmasını sağlar
        'is_active' => 'boolean',
        'quantity' => 'integer', // Miktar integer olmalı
    ];

    public function category()
    {
        return $this->belongsTo(Category::class); // bir ürünün bir kategorisi olabilir
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInStock($query)
    {
        return $query->where('quantity', '>', 0);
    }
}
