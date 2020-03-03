<?php

namespace App\Http\Controllers;

use DateTime;
use App\Services\Account;
use App\Services\Log;
use App\Services\Mpesa;
use App\Services\Redis;
use App\Services\Sms;
use App\Services\Token;
use App\Payment;
use App\Booking;
use App\BusBooking;
use App\FlightBooking;
use App\CarBooking;
use App\TrainBooking;
use App\Services\Http;
use Laminas\ApiTools\ApiProblem\ApiProblem;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    private $cols;
    private $operator;

    public function __construct(Redis $redis, Http $http)
    {
        $this->cols = (new Payment)->getFillable();
    }

    /**
     * index
     * Show all Payments
     * 
     * @param  Request $request
     *
     * 
     */
    public function index(Request $request)
    {
        return Payment::all();
    }

    /**
     * Store Payment
     */
    public function store(Request $request)
    {
        $payment = new Payment;
        foreach ($this->cols as $field) {
            try {
                if (in_array($field, ["created_at", "updated_at"])) {
                    $time = $request->get($field, date("Y-m-dH:i:s"));
                    $payment->$field = new DateTime($time);
                } else {
                    $payment->$field = $request->get($field);
                }
            } catch (\Throwable $th) {
                throw $th;
            }
        }
        
        $payment->save();

        return $payment;
    }

    /**
     * Show single Payment
     */
    public function show($id)
    {
        return Payment::find($id);
    }

    /**
     * Update Payment
     */
    public function update($id, Request $request)
    {
        $payment = Payment::find($id);
        if (!$payment) {
            return ["error" => "Payment not found"];
        }

        foreach ($this->cols as $field) {

            try {
                if (in_array($field, ["created_at", "updated_at", "departure_time", "arrival_time", "booking_date"])) {
                    $time = strtotime($request->get($field, date("Y-m-d H:i:s")));
                    $payment->$field = new DateTime($time);
                } else {
                    $payment->$field = $request->get($field);
                }
            } catch (\Throwable $th) {
                throw $th;
            }
        }

        $payment->save();
        return $payment;
    }

    /**
     * Delete Payment
     */
    public function delete($id)
    {
        $payment = Payment::find($id);
        return $payment->delete();
    }
}
