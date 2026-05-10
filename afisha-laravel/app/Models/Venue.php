<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Venue extends Model
{
    protected $table      = 'venues';
    protected $primaryKey = 'venue_id';
    public    $timestamps = false;

    protected $fillable = [
        'name', 'description', 'address', 'image', 'age', 'category_id',
    ];

    public function category()  { return $this->belongsTo(Category::class, 'category_id', 'id'); }
    public function reviews()   { return $this->hasMany(Review::class, 'venue_id', 'venue_id'); }
    public function schedule()  { return $this->hasMany(VenueSchedule::class, 'venue_id', 'venue_id'); }
    public function contacts()  { return $this->hasMany(VenueContact::class, 'venue_id', 'venue_id'); }
}
