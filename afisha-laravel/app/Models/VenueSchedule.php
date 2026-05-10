<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VenueSchedule extends Model
{
    protected $table      = 'schedule';
    protected $primaryKey = 'id';
    public    $timestamps = false;

    protected $fillable = ['venue_id', 'day_of_week', 'start_time', 'end_time', 'description'];
}
