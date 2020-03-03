<?php

namespace App\Http\Controllers;

use App\Booking;
use App\Payment;
use App\Services\Http;
use App\Services\Redis;
use App\Services\Log;
use App\Services\Mpesa;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse as Response;
use Osen\Mpesa\STK;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class ValidationController extends AbstractController
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

    public function getRepository($model = "Booking")
    {
        return $this->entityManager->getRepository($model);
    }

    public function getColumns($table = "bookings")
    {
        $schemaManager = $this->entityManager->getConnection()->getSchemaManager();
        $bcolumns = $schemaManager->listTableColumns($table);

        return array_diff(array_keys($bcolumns), ["id"]);
    }
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
