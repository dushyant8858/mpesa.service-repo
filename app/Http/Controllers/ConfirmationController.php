<?php

namespace App\Http\Controllers;

use App\Booking;
use App\Payment;
use App\Services\Http;
use App\Services\Log;
use App\Services\Mpesa;
use App\Services\Redis;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse as Response;
use Symfony\Component\HttpFoundation\Request;
use Osen\Mpesa\C2B;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class ConfirmationController extends AbstractController
{
    private $entityManager;
    private $operator;
    private $serializer;

    public function __construct(Mpesa $mpesa, Log $log, Http $http)
    {
        $this->mpesa = $mpesa;
        $this->logger = $log;
        $this->client = $http;
    }
    /**
     * C2B Confirmation
     *
     * @param  Request $request
     * @param  Mpesa $mpesa M-PESA Service
     * @param  Log $log Log Log Service
     * @param  Http $http Http Client
     * @param  EntityManagerInterface $entityManager
     *
     * @return Response
     *
     * @return void
     */
    public function confirm(Request $request)
    {
        $allOperators = file_get_contents("../operators.json");
        $allOperators = json_decode($allOperators, true);
        $operator = "buupass";
        $this->mpesa->setOperator($operator, $request->getSchemeAndHttpHost());

        return new Response(C2B::confirm(function ($response) use ($request, $allOperators,$operator) {
            $handle = "c2b_confirm_" . date("Y-m-d") . ".log";
            $this->log->new($response, $handle, "{$operator}_confirm");

            $payment = [];
            $data    = [];

            if (isset($response['"TransID"'])) {
                // STK payment was received
                $response                = $response['Body']['stkCallback'];
                $response['gateway_response']   = json_encode($response);
                $data['transaction_id']       = $response['transaction_id']       = $response['BillRefNumber'];
                $data['response_description'] = $response['response_description'] = $response['ResultDesc'] ?? '';
                $response['response_code']      = $data['response_code']      = $response['ResultCode'] ?? 0;
                $response['payment_status']     = $data['payment_status']     = $response['ResultCode'] == 0 ? 'paid' : 'pending';

                $response['status_description'] = $response['ResultDesc'] ?? '';
                $response['status_info']        = '';
                foreach (['paid' => 'successfully', 'timeout' => 'timeout', 'cancelled' => 'cancelled', 'locked' => 'lock'] as $info_key => $info_status) {
                    //Check for the status from the description
                    if (strpos($response['status_description'], $info_status) !== false) {
                        $response['status_info'] = $info_key;
                        break;
                    }
                }

                $payment = Payment::whereSystemTransactionId($response['transaction_id'])->first();
                // check if user has paid. $response['status_info'] value is set above
                if ($response['status_info'] == 'paid') {
                    //Since C2B will confirm payments no need
                    //$payment->bookings->is_stk = true;
                    // confirm a bookings with the respective backend
                    //$confirmation_array = $payment->bookings->trigger_confirmation($response);
                    return ['info' => 'success', "message" => "Successfully paid"];
                } else {
                    $payment->setGatewayConfirmationResponse(json_encode($response));
                    $payment->booking->setGatewayConfirmationResponse(json_encode($response));
                    $payment->save();

                    return ['info' => 'error', "message" => "Payment status is : " . $response['status_info']];
                }
            }

            return ['info' => 'error', "message" => "Invalid STK Callback"];
        }));
    }

    function retry_confirmation(Request $request)
    {
        error_reporting(E_ALL);
        ini_set("display_errors", "On");
        $booking = $this->brepository->find($request->get('booking_id'));
        if ($booking->confirmation_response) {
            if ($booking->has_receipts()) {
                $booking->sms_receipt(false, true);
            }
        } else {
            $response = $booking->trigger_operator_confirmation();
            if ($booking->has_receipts()) {
                $booking->sms_receipt(false, true);
            }
        }
        //If Booking confirmation response is still NULL after the retry it means the client booking cannot be confirmed on Easycoach side.
        if ($booking->confirmation_response) {
            die($booking->sms);
        } else {
            exit("Invalid PNR No. for client. Could not send SMS.");
        }
    }
}
