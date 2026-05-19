<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $table      = 'events';
    protected $primaryKey = 'event_id';
    public    $timestamps = false;

    protected $fillable = [
        'title', 'description', 'start_datetime', 'end_datetime',
        'price', 'capacity', 'age_restriction', 'image', 'gallery',
        'organization_id', 'venue_id', 'category_id', 'status_id',
    ];

    protected $casts = ['gallery' => 'array'];

    public function organization() { return $this->belongsTo(Organization::class, 'organization_id', 'organization_id'); }
    public function venue()        { return $this->belongsTo(Venue::class,        'venue_id',        'venue_id'); }
    public function category()     { return $this->belongsTo(Category::class,     'category_id',     'id'); }
    public function status()       { return $this->belongsTo(Status::class,       'status_id',       'status_id'); }
    public function reviews()      { return $this->hasMany(Review::class,         'event_id',        'event_id'); }
    public function tickets()      { return $this->hasMany(Ticket::class,         'event_id',        'event_id'); }
}
