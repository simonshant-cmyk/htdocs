<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrgSubscription extends Model
{
    protected $table      = 'org_subscriptions';
    public    $timestamps = false;
    protected $fillable   = ['user_id', 'organization_id'];
    const CREATED_AT      = 'created_at';
}
