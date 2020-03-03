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
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Osen\Mpesa\STK;

class ReconciliationController extends AbstractController
{
    private $entityManager;
    private $operator;
    private $serializer;

    public function __construct(EntityManagerInterface $entityManager, Redis $redis, Http $http)
    {
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];

        $this->serializer = new Serializer($normalizers, $encoders);
        $this->entityManager = $entityManager;
    }

    public function getRepository($model = "Payment")
    {
        $model = "\App\\" . ucfirst($model);
        return $this->entityManager->getRepository($model);
    }

    public function getColumns($table = "payments")
    {
        $schemaManager = $this->entityManager->getConnection()->getSchemaManager();
        $bcolumns = $schemaManager->listTableColumns($table);

        return array_diff(array_keys($bcolumns), ["id"]);
    }
    /**
     * @Route("/reconcile/{operator}", name="reconcile", methods={"POST"})
     *
     * @param  Request $request
     * @param  Mpesa $mpesa
     * @param  Log $log
     * @param  Http $http
     * @param  mixed $operator
     *
     * @return Response
     */
    public function __(Request $request, Mpesa $mpesa, Log $log, Http $http, Token $token, EntityManagerInterface $entityManager, $operator = "buupass")
    {
        $mpesa->setOperator($operator, $request->getSchemeAndHttpHost());

        return new Response(STK::reconcile(function ($response) use ($log, $http, $entityManager, $operator, $token) {
            $handle = "stk_reconcile_" . date("Y-m-d") . ".log";
            $log->new($response, $handle, "{$operator}_confirm", "INFO");

            $payment = [];
            $data    = [];

            if (isset($response['Body']['stkCallback'])) {
                // STK payment was received
                $response                = $response['Body']['stkCallback'];
                $response['gateway_response']   = json_encode($response);
                $data['transaction_id']       = $response['transaction_id']       = $response['MerchantRequestID'];
                $data['response_description'] = $response['response_description'] = $response['ResultDesc'];
                $response['response_code']      = $data['response_code']      = $response['ResultCode'];
                $response['payment_status']     = $data['payment_status']($response['ResultCode'] == 0 ? 'paid' : 'pending');

                $response['status_description'] = $response['ResultDesc'];
                $response['status_info']        = '';
                foreach (['paid' => 'successfully', 'timeout' => 'timeout', 'cancelled' => 'cancelled', 'locked' => 'lock'] as $info_key => $info_status) {
                    //Check for the status from the description
                    if (strpos($response['status_description'], $info_status) !== false) {
                        $response['status_info'] = $info_key;
                        break;
                    }
                }

                $objPayments = $this->getRepository()->findBy(['system_transaction_id' => $response['transaction_id']]);

                $payment     = $objPayments[0];
                $payment->setGatewayConfirmationResponse(json_encode($response));

                $booking = $this->getRepository('Booking')->find($payment->getBookingId());
                $booking->setGatewayConfirmationResponse(json_encode($response));
                // check if user has paid. $response['status_info'] value is set above
                if ($response['status_info'] == 'paid') {
                    //Since C2B will confirm payments no need
                    //$payment->bookings->is_stk = true;
                    // confirm a bookings with the respective backend
                    $confirmation_array = $booking->trigger_confirmation($response);
                    return ['info' => 'success', "message" => "Successfully paid"];
                } else {
                    $entityManager->persist($payment);
                    $entityManager->persist($booking);
                    $entityManager->flush();

                    return ['info' => 'error', "message" => "Payment status is : " . $response['status_info']];
                }
            }

            return ['info' => 'error', "message" => "Invalid STK Callback"];

            return true;
        }));
    }
}
