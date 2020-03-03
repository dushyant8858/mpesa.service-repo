<?php

namespace App\Controller;

use App\Entity\Payment;
use App\Service\Http;
use App\Service\Log;
use App\Service\Mpesa;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse as Response;
use Symfony\Component\HttpFoundation\Request;
use Osen\Mpesa\C2B;

class ConfirmationController extends AbstractController
{
    /**
     * @Route("/confirm", name="confirm", methods={"POST"})
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
    public function __(Request $request, Mpesa $mpesa, Log $log, Http $http, EntityManagerInterface $entityManager)
    {
        $allOperators = file_get_contents("../operators.json");
        $allOperators = json_decode($allOperators, true);
        $operator = "buupass";
        $mpesa->setOperator($operator, $request->getSchemeAndHttpHost());

        return new Response(C2B::confirm(function ($response) use ($request, $log, $http, $allOperators, $entityManager, $operator) {
            $handle = "c2b_confirm_" . date("Y-m-d") . ".log";
            $log->new($response, $handle, "{$operator}_confirm");

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

                $objPayments = $this->prepository->findBy(['system_transaction_id' => $response['transaction_id']]);
                $payment     = $this->prepository->find($objPayments[0]->payment_id);
                // check if user has paid. $response['status_info'] value is set above
                if ($response['status_info'] == 'paid') {
                    //Since C2B will confirm payments no need
                    //$payment->bookings->is_stk = true;
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
