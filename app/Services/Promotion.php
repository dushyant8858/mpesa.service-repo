<?php

/**
 * @package  BuuPay Mpesa
 * @subpackage  App\Services\Db
 * @author Mauko Maunde <mauko@buupass.com>
 * @copyright  2020 BuuPass Kenya Limited
 * @since  v2
 * 
 * @version v2.1
 */

namespace App\Services;

use App\Booking;
use App\Payment;
use App\Services\Http;
use App\Services\Redis;

class Promotion
{
    /**
     * @param $connection Mongodb connection
     */
    public function __construct(Http $http, Redis $redis, Token $token)
    {
        $this->redis = $redis;
        $this->http = $http;
        $this->token = $token;
    }

    public function bind_voucher(Payment $payment, $operator)
    {
        if (($payment->booking_status == 1) &&
            (!empty($payment->operator)) &&
            ($payment->operator != 'mpesa_ticket')
        ) {
            try {
                $phoneNumber = $payment->phone;
                $bookingId = (int) $payment->booking_id;
                $redisKey = 'tecno_' . $phoneNumber . '_' . $bookingId;
                if ($this->redis->exists($redisKey)) {
                    return;
                }
                $guzzleClient = $this->client->to('https://dev.tecno.buupass.com', [
                    'headers' => [
                        'Authorization' => 'Token ' . $this->token->operatorToken($operator),
                        'Accept' => 'application/json',
                    ]
                ]);
                $response = $guzzleClient->post('/api/voucher/bind/', [
                    'form_params' => [
                        "phone_number" => $phoneNumber,
                        "voucher_type" => "500_250_buupass",
                        "booking_id" => $bookingId,
                    ]
                ]);
                $body = $response->getBody();
                if (empty($body)) {
                    return;
                }
                $responseData = json_decode($body, true);
                if (empty($responseData)) {
                    return;
                }
                $this->redis->set('tecno_voucher_response_' . $bookingId . '_' . time(), json_encode($responseData), 1800);
                $this->redis->set($redisKey, 1, 600);
                if ($responseData['success']) {
                    // success
                } else {
                    // fail
                }
            } catch (\Exception $ex) {
                // error
                // if ($ex instanceof \adResponseException) {
                //     $this->redis->set('tecno_voucher_response_error_' . time(), $ex->getResponse()->getBody(), 900);
                // } else {
                //     $this->redis->set('tecno_voucher_response_error_' . time(), $ex->getMessage(), 900);
                // }
            }
        }
    }
}
