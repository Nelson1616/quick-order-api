<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'name',
        'email',
        'password',
        'status_id',
        'restaurant_id',
        'image_id',
        'updated_at',
        'created_at',
    ];

    protected $hidden = [
        'password',
    ];
}
