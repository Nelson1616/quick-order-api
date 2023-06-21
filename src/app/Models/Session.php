<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Session extends Model
{
    protected $table = 'sessions';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'status_id',
        'table_id',
        'updated_at',
        'created_at',
    ];

    protected $hidden = [];

    public function table(): BelongsTo
    {
        return $this->BelongsTo(Table::class, 'table_id', 'id');
    }

    public function sessionUsers(): HasMany
    {
        return $this->hasMany(SessionUser::class, 'session_id', 'id');
    }

    public function sessionOrders(): HasMany
    {
        return $this->hasMany(SessionOrder::class, 'session_id', 'id');
    }

    public function sessionWaiterCalls(): HasMany
    {
        return $this->hasMany(sessionWaiterCall::class, 'session_id', 'id');
    }

    public static function createNew(int $table_id) : self {
        return self::create([
            'table_id' => $table_id,
        ]);
    }

    public static function verifyUserSameName(int $sessionId, string $name) {
        return DB::select("SELECT 
        u.*
        FROM users u 
        JOIN session_users su ON su.user_id = u.id 
        WHERE
        u.name = '$name'
        AND su.session_id = $sessionId");
    }

    public static function tryUpdateToFinished(int $session_id) {
        $query = DB::select("SELECT 
        su.*
        FROM sessions s
        JOIN session_users su ON su.session_id = s.id
        WHERE 
        s.id = $session_id
        AND su.status_id > 0");

        if (empty($query)) {
            Session::find($session_id)->update(["status_id" => 0]);
        }
    }
}
