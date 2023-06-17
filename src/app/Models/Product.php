<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    protected $table = 'products';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'name',
        'description',
        'image',
        'status_id',
        'price',
        'restaurant_id',
        'updated_at',
        'created_at',
    ];

    public function restaurant(): BelongsTo
    {
        return $this->BelongsTo(Restaurant::class, 'restaurant_id', 'id');
    }
}
