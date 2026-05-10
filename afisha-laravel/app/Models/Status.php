<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    protected $table      = 'statuses';
    protected $primaryKey = 'status_id';
    public    $timestamps = false;

    protected $fillable = ['status_name'];
}
