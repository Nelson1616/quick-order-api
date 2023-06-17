<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function sessionUsers(): HasMany
    {
        return $this->hasMany(SessionUser::class, 'user_id', 'id');
    }

    public function activeSessionUsers(): HasMany
    {
        return $this->hasMany(SessionUser::class, 'user_id', 'id')->where('status_id', 1);
    }
}
