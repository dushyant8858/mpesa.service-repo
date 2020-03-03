<?php

namespace App\Http\Controllers;

use App\Booking;
use App\Payment;
use App\Services\Http;
use App\Services\Log;
use App\Services\Mpesa;
use App\Services\Redis;
use App\Services\Token;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse as Response;
use Symfony\Component\HttpFoundation\Request;
use Osen\Mpesa\STK;

class StatusController extends AbstractController
{
    /**
     * @Route("/status/{transaction}/{operator}", name="status", methods={"POST"})
     *
     * @param  Request $request
     * @param  Mpesa $mpesa
     * @param  Log $log
     * @param  Http $http
     * @param  mixed $transaction
     * @param  mixed $operator
     *
     * @return Response
     */
    public function __(Request $request, Mpesa $mpesa, Log $log, $transaction, $operator = "buupass")
    {
        $mpesa->setOperator($operator, $request->getSchemeAndHttpHost());

        try {
            return new Response(STK::status($transaction, "TransactionStatusQuery", "Transaction Status Query", "Transaction Status Query",  function ($response) use ($log, $operator) {
                $handle = "c2b_status_" . date("Y-m-d") . ".log";
                $log->new($response, $handle, "{$operator}_c2b_status", "INFO");

                return true;
            }));
        } catch (\Exception $e) {
            $handle = "stk_push_error_" . date("Y-m-d") . ".log";
            $log->channel(["file", "redis", "sms"])->new($e->getMessage(), $handle, "{$operator}_stk", "ERROR");

            return new Response(
                [
                    "errorCode" => 1,
                    "errorMessage" => $e->getMessage()
                ]
            );
        }
    }

    /**
     * @Route("/status/query/{transaction}/{operator}", name="status_query", methods={"POST"})
     *
     * @param  mixed $params
     * @param  mixed $entityManager
     * @param  mixed $http
     * @param  mixed $token
     * @param  mixed $redis
     *
     * @return void
     */
    public static function query_status($params, EntityManagerInterface $entityManager, Http $http, Token $token, Redis $redis)
    {
        $token = $token->generate("modern_coast");
        $brepository = $entityManager->getRepository(Booking::class);
        $prepository = $entityManager->getRepository(Payment::class);

        $default_paybill = "";

        $paybill        = $params["to_paybill"] ?? $params["paybill"] ?? $default_paybill;
        $timestamp      = date("YmdHis");
        $transaction_id = $params["transaction_id"] ?? false;
        if (empty($transaction_id)) {
            return [
                "status" => "error",
                "message"     => "Error Confirming, your payment. Kindly follow the payment instructions to complete your payment. If you are unable to get the payment popup on your phone, check for payment instructions on your phone.",
                "stk_response"   => [], "payment_response" => ""
            ];
        }

        $objPayments = $prepository->findOneBy(["system_transaction_id" => $transaction_id]);
        $payment     = $prepository->find($objPayments[0]->payment_id);

        if (!$payment) {
            $objPayments = $prepository->findOneBy(["booking_id" => $transaction_id]);
            $payment     = $prepository->find($objPayments[0]->payment_id);
        }

        $bookings = $payment->bookings ?: $brepository->find($payment->booking_id);

        if ($bookings->operator == "modern_coast") {
            $client = $http->to("http://173.212.223.234", [
                "headers" => [
                    "Authorization" => "Bearer {$token}",
                    "Origin"        => "*",
                    "Content-Type"  => "application/json",
                    "Accept"        => "application/json",
                ],
            ]);
            // response {"payment_unique_number":null,"mpesa_transaction_no":"NHS57S5T3N","mpesa_amount":10,"mpesa_paybill":"541559"}
            $data = json_decode($bookings->data, true);
            if (isset($data["incoming_payload"]["ticket_confirmation_payload"])) {
                $mcTicketConfirmationPayload = $data["incoming_payload"]["ticket_confirmation_payload"];
                $discountedTicketAmount      = $mcTicketConfirmationPayload["ticket_amount"];
                $mcTradeId                   = $bookings->trade_id;
                $response                    = \json_encode([]);
                try {
                    $apiRawResponse = $client->fetch("/api/v3/find-mpesa", [
                        "ticket_amount"         => $discountedTicketAmount,
                        "customer_phone_number" => "0" . substr(trim($bookings->phone), -9),
                        "customer_phone_code"   => "254",
                        "payment_unique_number" => $mcTradeId,
                        "ticket_channel_id"     => 6,
                        "ticket_currency_id"    => 1,
                    ]);
                    $response = $apiRawResponse->getBody();
                    $redis->set("modern_coast_find_mpesa_" . time(), $response, 3600);
                } catch (\Exception $ex) {
                    $redis->set("modern_coast_find_mpesa_error_" . time(), $ex->getMessage(), 3600);
                }
                $response_arr = json_decode($response, true);
                //{"payment_unique_number":null,"mpesa_transaction_no":"NHS485V3PW","mpesa_amount":10,"mpesa_paybill":"541559"}
                if ((isset($response_arr["mpesa_transaction_no"]) && ($response_arr["mpesa_transaction_no"]))
                    && (isset($response_arr["mpesa_amount"]) && ($response_arr["mpesa_amount"] = $discountedTicketAmount))
                ) {
                    $data["query_status_response"]                                = $response_arr;
                    $mcTicketConfirmationPayload["payment"]["payment_ref_number"] = $response_arr["mpesa_transaction_no"];

                    try {
                        $mcTicketConfirmationResponse = $client->post(
                            "/api/v3/save-ticket",
                            ["json" => $mcTicketConfirmationPayload]
                        );
                        $data["ticket_confirmation_response"] = json_decode($mcTicketConfirmationResponse->getBody(), true);
                        $bookings->booking_status              = 1;
                        $bookings->data                        = json_encode($data);
                        $bookings->save();
                        $redis->set("modern_coast_save_ticket_" . time(), $response, 3600);
                        return [
                            "status" => "success",
                            "message" => "Successfully Confirmed via transactional check",
                            "stk_response" => [],
                            "payment_response" => ""
                        ];
                    } catch (\Exception $ex) {
                        $redis->set("modern_coast_save_ticket_error_" . time(), $ex->getMessage(), 3600);
                        return [];
                    }
                } else {
                    return [
                        "status" => "error",
                        "message"     => "Kindly confirm the payment request sent to your phone.  If you have already confirmed wait for a minute for your transaction confirmation to be verified.",
                        "stk_response"   => [],
                        "payment_response" => ""
                    ];
                }
            }

            if ($payment) {
                if ($payment->payment_status == "paid") {
                    return [
                        "status" => "success",
                        "message" => "Successfully Confirmed via transactional check",
                        "stk_response" => [],
                        "payment_response" => $payment->to_array()
                    ];
                } else {
                    return [
                        "status" => "error",
                        "message" => "Kindly confirm the payment request sent to your phone. If you have already confirmed wait for a minute for your transaction confirmation to be verified.",
                        "stk_response" => [],
                        "payment_response" => $payment->to_array()
                    ];
                }
            }
        } elseif ($bookings->operator == "mpesa_ticket") {
            if ($bookings->booking_status == 1) {
                return [
                    "status" => "success",
                    "message" => "Successfully Confirmed via transactional check",
                    "stk_response" => [],
                    "payment_response" => $bookings->to_array()
                ];
            } else {
                return [
                    "status" => "error",
                    "message" => "Kindly confirm the payment request sent to your phone. If you have already confirmed wait for a minute for your transaction confirmation to be verified.",
                    "stk_response" => [],
                    "payment_response" => $payment->to_array()
                ];
            }
        } else {
            //$payment=Payment::find_by_system_transaction_id($transaction_id);
            $objPayments = $prepository->findOneBy(["system_transaction_id" => $transaction_id]);
            $payment     = $prepository->find($objPayments[0]->payment_id);
            if (!$payment) {
                $objPayments = $prepository->findOneBy(["booking_id" => $transaction_id]);
                $payment     = $prepository->find($objPayments[0]->payment_id);
            }
            if ($payment) {
                if ($payment->payment_status == "paid") {
                    return [
                        "status" => "success",
                        "message" => "Successfully Confirmed via transactional check",
                        "stk_response" => [],
                        "payment_response" => $payment->to_array()
                    ];
                } else {
                    return
                        [
                            "status" => "error",
                            "message" => "Kindly confirm the payment request sent to your phone. If you have already confirmed wait for a minute for your transaction confirmation to be verified.",
                            "stk_response" => [],
                            "payment_response" => $payment->to_array()
                        ];
                }
            }

            $payment_response = is_object($payment) ? (array) $payment : $payment;
            return [
                "status" => "error",
                "message" => "Error Confirming, your payment. Kindly follow the payment instructions to complete your payment. If you are unable to get your payment popup on your phone, click Back to Payment Options and use PesaPal.",
                "stk_response" => [],
                "payment_response" => $payment_response
            ];
        }
    }
}
