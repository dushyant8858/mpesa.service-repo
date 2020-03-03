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

class BusBookingController extends Controller
{
    private $cols;
    private $operator;

    public function __construct(Redis $redis, Http $http)
    {
        $this->cols = (new BusBooking)->getFillable();
    }

    /**
     * index
     * Show all BusBookings
     * 
     * @param  Request $request
     *
     * 
     */
    public function index(Request $request)
    {
        return BusBooking::all();
    }

    /**
     * Store BusBooking
     */
    public function store(Request $request)
    {
        $booking = new BusBooking;
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
     * Show single BusBooking
     */
    public function show($id)
    {
        return BusBooking::find($id);
    }

    /**
     * Update BusBooking
     */
    public function update($id, Request $request)
    {
        $booking = BusBooking::find($id);
        if (!$booking) {
            return ["error" => "BusBooking not found"];
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
     * Delete BusBooking
     */
    public function delete($id)
    {
        $booking = BusBooking::find($id);
        return $booking->delete();
    }
}
