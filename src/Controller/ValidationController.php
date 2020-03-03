<?php

namespace App\Controller;

use App\Service\Log;
use App\Service\Mpesa;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse as Response;
use Osen\Mpesa\STK;
use Symfony\Component\HttpFoundation\Request;

class ValidationController extends AbstractController
{
    /**
     * @Route("/validate/{operator}", name="validate", methods={"POST"})
     *
     * @param  Request $request
     * @param  Mpesa $mpesa
     * @param  Log $log
     * @param  Http $http
     * @param  mixed $operator
     *
     * @return Response
     */
    public function __(Request $request, Mpesa $mpesa, Log $log, $operator = "buupass")
    {
        $mpesa->setOperator($operator, $request->getSchemeAndHttpHost());

        return new Response(STK::validate(function ($response) use ($log, $operator) {
            $handle = "c2b_validate_" . date("Y-m-d") . ".log";
            $log->new($response, $handle, "{$operator}_validate", "INFO");

            return true;
        }));
    }
}
