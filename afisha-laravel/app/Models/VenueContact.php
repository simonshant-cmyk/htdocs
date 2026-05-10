<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VenueContact extends Model
{
    protected $table      = 'contacts';
    protected $primaryKey = 'id';
    public    $timestamps = false;

    protected $fillable = ['venue_id', 'number', 'email', 'website', 'social_media'];
}
