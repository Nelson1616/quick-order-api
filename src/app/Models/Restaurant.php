<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Restaurant extends Model
{
    protected $table = 'restaurants';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'name',
        'description',
        'image',
        'status_id',
        'primary_color',
        'secondary_color',
        'tertiary_color',
        'updated_at',
        'created_at',
    ];

    protected $hidden = [];

    public function tables(): HasMany
    {
        return $this->hasMany(Tables::class, 'restaurant_id', 'id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'restaurant_id', 'id');
    }
}
