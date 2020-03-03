<?php

/**
 * @package BuuPay Mpesa
 * @subpackage App\Services\Sms
 * @author Mauko Maunde <mauko@buupass.com>
 * @copyright BuuPass Kenya Limited
 * @since v2
 * 
 * @version v2.1
 */

namespace App\Services;

use App\Booking;
use App\Payment;
use GuzzleHttp\Client;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;

class Sms
{
    private $client;

    /**
     * __construct
     *
     * @return void
     */
    public function __construct()
    {
        $this->client = new Client([
            "base_uri"  => "https://sms.buupass.com",
            "timeout"   => 10
        ]);
    }

    /**
     * new
     *
     * @param  mixed $to Number(s) to send message to
     * @param  string $message Message content to send
     *
     * @return void
     */
    public function send($to, string $message = "", $return = true)
    {
        $sms = $this->client->post(
            "/send",
            [
                "form_params" => [
                    "message" => $message,
                    "key"     => "b2dd03c8-39d4-4d8f-98ff-823fe69b080e",
                    "to"      => is_array($to) ? implode(",", $to) : $to,
                ]
            ]
        );

        $response = json_decode($sms->getBody(), true);

        if ($response[0]["status"] !== "Success") {
            $this->log("Could not send SMS Notification");
        }

        if ($return) {
            return $response;
        }
    }

    /**
     * log
     *
     * @param  mixed $message
     * @param  mixed $name
     * @param  mixed $level
     * @param  mixed $handle
     *
     * @return void
     */
    public function log($message, $name = "logger", $level = "DEBUG", $handle = "sms_notify_error.log")
    {
        $message    = is_array($message) ? json_encode($message) : $message;
        $action     = strtolower($level);
        $logger     = new Logger($name);

        $logger->pushHandler(new StreamHandler("../logs/" . $handle, $level));
        $logger->pushHandler(new FirePHPHandler());
        $logger->$action($message);
    }

    /**
     * send_instructions
     *
     * @param  mixed $payment
     * @param  mixed $booking
     * @param  mixed $data
     * @param  mixed $mode
     *
     * @return void
     */
    public function send_instructions(Payment $payment, $booking, array $data, string $mode)
    {
        $bmode = "\App\\".ucwords($mode)."Booking";
        $booking = $bmode::find($booking);

        //Prevent Endless Loop
        if ($payment->getInstructionsCount() > 0) {
            return;
        }

        $bpOperator = $booking->getOperator();
        if ($payment->getPaymentStatus() == 'pending' && ($bpOperator == 'easy_coach')) {

            $booking_data = json_decode($data, true);

            $route_title = isset($booking_data['trip']['route']['title']) ? $booking_data['trip']['route']['title'] : $booking->source . ' - ' . $booking->destination;
            $departure_time = isset($booking_data['trip']['departure_time']) ? $booking_data['trip']['departure_time'] : '';
            $departure_date = isset($booking_data['trip']['departure_date']) ? $booking_data['trip']['departure_date'] : '';
            $children = isset($booking_data['children']) ? $booking_data['children'] : '';
            $adults = isset($booking_data['adults']) ? $booking_data['adults'] : '';
            $phone = $booking->getPhone();
            $booking_no = $booking->getCustomBookingNo();
            //Check Paybill
            $paybill = isset($booking_data['paybill']) ? $booking_data['paybill'] : '796440';

            $date = date("h:i A", strtotime($booking->getDepartureTime()));
            $message = <<<SMS
            Use the following instructions to confirm reservation :\n\n
            Pay {$payment->getAmount()} Ksh \n
            Paybill No: {$paybill} \n"
            Acct No: {$booking->getId()} \n"
            Pay within 4 minutes or ticket cancels.\n
            Note confirmation SMS might take 10 minutes to come \n\n
            $route_title \n
            On {$date} - {$booking->getDateOfTravel()}\n
            {$booking->getTotalPassengers()} person(s) - {$payment->getAmount()} Ksh
    SMS;

            return $this->send($phone, $message, true);
        }
    }
}
