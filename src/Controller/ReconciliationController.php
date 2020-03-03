<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Entity\Payment;
use App\Service\Http;
use App\Service\Log;
use App\Service\Mpesa;
use App\Service\Redis;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse as Response;
use Symfony\Component\HttpFoundation\Request;
use Osen\Mpesa\STK;

class ReconciliationController extends AbstractController
{
    private $bcolumns;
    private $pcolumns;
    private $brepository;
    private $prepository;

    public function __construct(EntityManagerInterface $entityManager, Redis $redis, Http $http)
    {
        $schemaManager = $entityManager->getConnection()->getSchemaManager();
        $bcolumns = $schemaManager->listTableColumns("bookings");
        $pcolumns = $schemaManager->listTableColumns("payment");

        $this->bcolumns = array_diff(array_keys($bcolumns), ["id"]);
        $this->pcolumns = array_diff(array_keys($pcolumns), ["id"]);

        $this->brepository = $entityManager->getRepository(Booking::class);
        $this->prepository = $entityManager->getRepository(Payment::class);
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
    public function __(Request $request, Mpesa $mpesa, Log $log, Http $http, EntityManagerInterface $entityManager, $operator = "buupass")
    {
        $mpesa->setOperator($operator, $request->getSchemeAndHttpHost());

        return new Response(STK::reconcile(function ($response) use ($log, $http, $entityManager, $operator) {
            $handle = "stk_reconcile_" . date("Y-m-d") . ".log";
            $log->new($response, $handle, "{$operator}_confirm", "INFO");

            $payment = [];
            $data    = [];

            if (isset($response['Body']['stkCallback'])) {
                // STK payment was received
                $response                = $response['Body']['stkCallback'];
                $response['gateway_response']   = json_encode($response);
                $data['transaction_id']       = $response['transaction_id']       = $response['CheckoutRequestID'];
                $data['response_description'] = $response['response_description'] = $response['ResultDesc'];
                $response['response_code']      = $data['response_code']      = $response['ResultCode'];
                $response['payment_status']     = $data['payment_status']     = $response['ResultCode'];

                $response['status_description'] = $response['ResultDesc'];
                $response['status_info']        = '';
                foreach (['paid' => 'successfully', 'timeout' => 'timeout', 'cancelled' => 'cancelled', 'locked' => 'lock'] as $info_key => $info_status) {
                    //Check for the status from the description
                    if (strpos($response['status_description'], $info_status) !== false) {
                        $response['status_info'] = $info_key;
                        break;
                    }
                }

                $objPayments = $this->prepository->findBy(['system_transaction_id' => $response['transaction_id']]);
                $payment     = $this->prepository->find($objPayments->getId());
                // check if user has paid. $response['status_info'] value is set above
                if ($response['status_info'] == 'paid') {
                    //Since C2B will confirm payments no need
                    $payment->bookings->is_stk = true;
                    // confirm a bookings with the respective backend
                    //$confirmation_array = $payment->bookings->trigger_confirmation($response);
                    return ['info' => 'success', "message" => "Successfully paid"];
                } else {
                    $payment->setGatewayConfirmationResponse(json_encode($response));
                    $payment->bookings->setGatewayConfirmationResponse(json_encode($response));
                    $entityManager->persist($payment);
                    $entityManager->flush();

                    return ['info' => 'error', "message" => "Payment status is : " . $response['status_info']];
                }
            }

            return ['info' => 'error', "message" => "Invalid STK Callback"];

            return true;
        }));
    }
}
