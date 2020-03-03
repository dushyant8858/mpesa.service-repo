<?php

namespace App\Http\Controllers;

use App\Services\Http;
use App\Services\Log;
use App\Services\Mpesa;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse as Response;

class SaveController extends AbstractController
{
    /**
     * @Route("/save/{operator}", name="save", methods={"POST"})
     *
     * @param  Request $request
     * @param  Mpesa $mpesa
     * @param  Log $log
     * @param  Http $http
     * @param  mixed $operator
     *
     * @return Response
     */
    public function __(Request $request, Mpesa $mpesa, Log $log, Http $http, $operator = "buupass")
    {
        $handle = "c2b_save_".date("Y-m-d").".log";
        $log->new(json_encode($request->getContent()), $handle, "{$operator}_save", "INFO");

        return new Response($request->request->all());
    }
}