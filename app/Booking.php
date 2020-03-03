<?php

namespace App;


use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'content', 'booking_id'
    ];

    protected $casts = [
        "passengers" => 'array',
        "seats" => 'array'
    ];

   /**
     * A has one payment
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
   */
    public function payment()
    {
        return $this->hasOne('App\Payment');
    }
}