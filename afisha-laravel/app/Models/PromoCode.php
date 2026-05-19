<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoCode extends Model
{
    protected $table    = 'promo_codes';
    protected $fillable = ['code','discount_type','discount_value','max_uses','uses_count','expires_at','organization_id','is_active'];
    protected $casts    = ['expires_at' => 'datetime', 'is_active' => 'boolean'];
}
