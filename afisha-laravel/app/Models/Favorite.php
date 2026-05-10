<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    protected $table      = 'favorites';
    protected $primaryKey = 'favorite_id';
    public    $timestamps = false;

    protected $fillable = ['user_id', 'event_id', 'venue_id', 'created_at'];

    public function event() { return $this->belongsTo(Event::class, 'event_id', 'event_id'); }
    public function venue() { return $this->belongsTo(Venue::class, 'venue_id', 'venue_id'); }
}
