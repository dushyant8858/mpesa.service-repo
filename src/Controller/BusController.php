<?php

namespace App\Controller;

use App\Service\Http;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse as Response;
use Symfony\Component\Routing\Annotation\Route;

class BusController extends AbstractController
{
    /**
     * @Route("bus", name="bus")
     * 
     * @param  Http $http
     *
     * @return Response
     */
    public function __(Http $http)
    {
        return Response::fromJsonString(
            $http->client->to("http://mpesa.pay.bp")->get("/validate/easy_coach")->getBody()->getContents()
        );
    }
}
