<?php

namespace App\Http\Controllers;

use DateTime;
use App\Services\Account;
use App\Services\Log;
use App\Services\Mpesa;
use App\Services\Redis;
use App\Services\Sms;
use App\Services\Token;
use App\Booking;
use App\BusBooking;
use App\FlightBooking;
use App\CarBooking;
use App\TrainBooking;
use App\Services\Http;
use Laminas\ApiTools\ApiProblem\ApiProblem;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    private $cols;
    private $operator;

    public function __construct(Redis $redis, Http $http)
    {
        $this->cols = (new Booking)->getFillable();
    }

    /**
     * index
     * Show all Bookings
     * 
     * @param  Request $request
     *
     * 
     */
    public function index(Request $request)
    {
        return Booking::all();
    }

    /**
     * Store Booking
     */
    public function store(Request $request)
    {
        $booking = new Booking;
        foreach ($this->cols as $field) {
            try {
                if (in_array($field, ["created_at", "updated_at"])) {
                    $time = $request->get($field, date("Y-m-dH:i:s"));
                    $booking->$field = new DateTime($time);
                } else {
                    $booking->$field = $request->get($field);
                }
            } catch (\Throwable $th) {
                throw $th;
            }
        }
        
        $booking->save();

        return $booking;
    }

    /**
     * Show single Booking
     */
    public function show($id)
    {
        return Booking::find($id);
    }

    /**
     * Update Booking
     */
    public function update($id, Request $request)
    {
        $booking = Booking::find($id);
        if (!$booking) {
            return ["error" => "Booking not found"];
        }

        foreach ($this->cols as $field) {

            try {
                if (in_array($field, ["created_at", "updated_at", "departure_time", "arrival_time", "booking_date"])) {
                    $time = strtotime($request->get($field, date("Y-m-d H:i:s")));
                    $booking->$field = new DateTime($time);
                } else {
                    $booking->$field = $request->get($field);
                }
            } catch (\Throwable $th) {
                throw $th;
            }
        }

        $booking->save();
        return $booking;
    }

    /**
     * Delete Booking
     */
    public function delete($id)
    {
        $booking = Booking::find($id);
        return $booking->delete();
    }
}
