<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\{Event, Venue, User};

class Review extends Model
{
    protected $table      = 'reviews';
    protected $primaryKey = 'review_id';
    public    $timestamps = false;

    protected $fillable = ['user_id', 'text', 'rating', 'event_id', 'venue_id', 'created_at'];

    public function user()  { return $this->belongsTo(User::class,  'user_id',  'user_id'); }
    public function event() { return $this->belongsTo(Event::class, 'event_id', 'event_id'); }
    public function venue() { return $this->belongsTo(Venue::class, 'venue_id', 'venue_id'); }
}
