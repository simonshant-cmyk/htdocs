<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $table      = 'tickets';
    protected $primaryKey = 'ticket_id';
    public    $timestamps = false;

    protected $fillable = ['user_id', 'event_id', 'price', 'quantity', 'status', 'paid_at', 'payment_method'];

    public function event() { return $this->belongsTo(Event::class, 'event_id', 'event_id'); }
    public function user()  { return $this->belongsTo(User::class,  'user_id',  'user_id'); }
}
