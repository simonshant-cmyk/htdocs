<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;

    protected $table      = 'users';
    protected $primaryKey = 'user_id';
    public    $timestamps = false;

    protected $fillable = [
        'last_name', 'first_name', 'patronymic',
        'phone', 'email', 'vk_id', 'date_of_birth',
        'avatar', 'role_id', 'pd_consent', 'password_hash',
        'status', 'warning_count', 'blocked_until', 'restriction_until',
    ];

    protected $casts = [
        'blocked_until'     => 'datetime',
        'restriction_until' => 'datetime',
    ];

    protected $hidden = ['password_hash'];

    protected $appends = ['full_name'];

    public function getFullNameAttribute(): string
    {
        return trim(implode(' ', array_filter([
            $this->last_name, $this->first_name, $this->patronymic,
        ])));
    }

    public function getAuthPassword(): string
    {
        return $this->password_hash ?? '';
    }

    public function reviews()   { return $this->hasMany(Review::class,   'user_id', 'user_id'); }
    public function favorites() { return $this->hasMany(Favorite::class,  'user_id', 'user_id'); }
    public function tickets()   { return $this->hasMany(Ticket::class,    'user_id', 'user_id'); }
}
