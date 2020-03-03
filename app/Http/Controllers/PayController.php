<?php

namespace App\Http\Controllers;

use App\Services\Log;
use App\Services\Mpesa;
use Osen\Mpesa\B2C;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse as Response;
use Symfony\Component\HttpFoundation\Request;

class PayController extends AbstractController
{
    /**
     * Make a payment to an Mpesa number
     * 
     * @param  Request $request
     * @param  Mpesa $mpesa
     * @param  Log $log
     * @param  Http $http
     * @param  mixed $transaction
     * @param  mixed $operator
     * 
     * @Route("/pay/{operator}", name="pay", methods={"POST"})
     *
     * @return Response
     */
    public function __(Request $request, Mpesa $mpesa, Log $log, $operator = "buupass")
    {
        $setOperator = "";
        $allOperators = file_get_contents("../operators.json");
        $allOperators = json_decode($allOperators, true);
        $base = substr($operator, 0, 4);

        foreach ($allOperators as $key => $value) {
            if (strtoupper($base) == $key) {
                $setOperator = (object)$value;
                $slug = $setOperator->alias;
            }
        }

        $mpesa->setOperator($operator, $request->getSchemeAndHttpHost());
        
        try {
            return new Response(B2C::send($request->get("phone"), $request->get("amount"), "BusinessPayment", "$operator Transaction", "$operator Transaction Request", function ($response) use ($log, $request, $operator, $setOperator) 
            {
                $handle = "b2c_".date("Y-m-d").".log";
                $log->new($handle, [$request->get("phone"), $request->get("amount"), "BusinessPayment", $operator, "$operator Transaction"], "{$operator}_b2c", "INFO");

                $handle = "b2c_".date("Y-m-d").".log";
                $log->new($response, $handle, "{$operator}_b2c", "INFO");
                
                if (isset($response["errorCode"])) {
                    $error = $response["errorCode"];

                    switch ($error) {
                        case "value":
                            $message = "[PAYMENT ERROR] From {$setOperator->name}: {$response["errorMessage"]}";
                            break;
                        
                        default:
                            $message = "[PAYMENT ERROR] From {$setOperator->name}: {$response["errorMessage"]}";
                            break;
                    }

                    $handle = "b2c_error_".date("Y-m-d").".log";
                    $log->new($message, $handle, "{$operator}_b2c", "ERROR");
                    return $response;
                } elseif (isset($response["responseCode"])) {
                    $error = $response["responseCode"];

                    switch ($error) {
                        case "value":
                            $message = "[PAYMENT ERROR] From {$setOperator->name}: {$response["responseDesc"]}";
                            break;
                        
                        default:
                            $message = "[PAYMENT ERROR] From {$setOperator->name}: {$response["responseDesc"]}";
                            break;
                    }

                    $handle = "b2c_error_".date("Y-m-d").".log";
                    $log->new($message, $handle, "{$operator}_b2c", "ERROR");
                    return $response;
                } else {
                    return $response;
                }
            }));
        } catch (\Exception $e) {
            $handle = "b2c_error_".date("Y-m-d").".log";
            $log->new($e->getMessage(), $handle, "{$operator}_b2c", "ERROR");
            return new Response(
                [
                    "errorCode" => 1,
                    "errorMessage" => $e->getMessage()
                ]
            );
        }
    }
}
