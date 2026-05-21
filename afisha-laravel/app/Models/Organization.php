<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Organization extends Authenticatable
{
    use HasApiTokens;

    protected $table      = 'organization';
    protected $primaryKey = 'organization_id';
    public    $timestamps = false;

    protected $fillable = [
        'full_name', 'email', 'address', 'inn', 'ogrn', 'kpp',
        'phone', 'website', 'image', 'description', 'vk',
        'type_id', 'status_id', 'password_hash', 'pd_consent',
    ];

    protected $hidden = ['password_hash'];

    public function getAuthPassword(): string
    {
        return $this->password_hash ?? '';
    }

    public function events()  { return $this->hasMany(Event::class, 'organization_id', 'organization_id'); }
    public function status()  { return $this->belongsTo(Status::class, 'status_id', 'status_id'); }
}
