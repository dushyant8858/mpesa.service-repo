<?php

namespace App;


use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'content', 'booking_id'
    ];

   /**
     * A post belongs to a booking
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
    public function booking()
    {
        return $this->belongsTo('App\Booking');
    }
}