<?php

namespace App\Http\Controllers;

use App\Booking;
use App\Payment;
use App\Services\Http;
use App\Services\Redis;
use App\HireBooking;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\JsonResponse as Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

/**
 * @Route("hire_bookings", name="hire_bookings")
 */
class HireController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager, Redis $redis, Http $http)
    {
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];

        $this->serializer = new Serializer($normalizers, $encoders);
        $this->entityManager = $entityManager;
    }

    public function getRepository($model = "Hire")
    {
        $model = "\App\Bookings\\" . ucfirst($model);
        return $this->entityManager->getRepository($model);
    }

    public function getColumns($table = "car_bookings")
    {
        $schemaManager = $this->entityManager->getConnection()->getSchemaManager();
        $bcolumns = $schemaManager->listTableColumns($table);

        return array_diff(array_keys($bcolumns), ["id"]);
    }

    /**
     * index
     *
     * @Route("/", name="_all")
     * @param  Request $request
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $bookings = $this->getrepository()->findAll();
        $bs = [];
        $res = [];
        foreach ($bookings as $b) {
            foreach ($this->getcolumns() as $col) {
                $method = ucwords(str_replace("_", " ", $col));
                $method = "get" . str_replace(" ", "", $method);
                $res[$col] = $b->$method();
            }

            $bs[] = $res;
        }

        return new Response($bs);
    }

    /**
     * findOneBy
     *
     * @param  mixed $fields
     *
     * @return void
     */
    public function findOneBy(array $fields)
    {
        $booking = $this->getrepository()->findOneBy($fields);
        if (!$booking) {
            return new Response(["error" => "Booking not found"]);
        }
        $res = [];
        foreach ($this->getcolumns() as $col) {
            $method = ucwords(str_replace("_", " ", $col));
            $method = "get" . str_replace(" ", "", $method);
            $res[$col] = $booking->$method();
        }

        return new Response($res);
    }

    /**
     * findManyBy
     *
     * @param  mixed $fields
     *
     * @return void
     */
    public function findManyBy(array $fields)
    {
        $bookings = $this->getrepository()->findBy($fields);
        $bs = [];
        $res = [];
        foreach ($bookings as $b) {
            foreach ($this->getcolumns() as $col) {
                $method = ucwords(str_replace("_", " ", $col));
                $method = "get" . str_replace(" ", "", $method);
                $res[$col] = $b->$method();
            }

            $bs[] = $res;
        }

        return new Response($bs);
    }

    /**
     * @Route("/new", name="_create")
     */
    public function createBooking(Request $request, EntityManagerInterface $entityManager): Response
    {
        $booking = new Booking();
        foreach ($this->getcolumns() as $field) {
            $method = ucwords(str_replace("_", " ", $field));
            $method = "set" . str_replace(" ", "", $method);

            try {
                if (in_array($field, ["created_at", "updated_at", "departure_time", "arrival_time", "booking_date"])) {
                    $time = $request->get($field, date("Y-m-dH:i:s"));
                    $booking->$method(new DateTime($time));
                } else {
                    $booking->$method($request->get($field));
                }
            } catch (\Throwable $th) {
                throw $th;
            }
        }

        $entityManager->persist($booking);
        $entityManager->flush();

        if (!$booking) {
            return new Response(["error" => "Booking not created"]);
        }
        $res = [];
        foreach ($this->getcolumns() as $col) {
            $method = ucwords(str_replace("_", " ", $col));
            $method = "get" . str_replace(" ", "", $method);
            $res[$col] = $booking->$method();
        }

        return new Response($res);
    }

    /**
     * @Route("/{id}", name="_show")
     */
    public function show($id)
    {
        $booking = $this->getrepository()->find($id);
        if (!$booking) {
            return new Response(["error" => "Booking not found"]);
        }
        $res = [];
        foreach ($this->getcolumns() as $col) {
            $method = ucwords(str_replace("_", " ", $col));
            $method = "get" . str_replace(" ", "", $method);
            $res[$col] = $booking->$method();
        }

        return new Response($res);
    }

    /**
     * @Route("/edit/{id}", name="_edit")
     */
    public function update($id, Request $request, EntityManagerInterface $entityManager)
    {
        $booking = $this->getrepository()->find($id);
        if (!$booking) {
            return new Response(["error" => "Booking not found"]);
        }

        foreach ($this->getcolumns() as $field) {
            $method = ucwords(str_replace("_", " ", $field));
            $method = "set" . str_replace(" ", "", $method);

            try {
                if (in_array($field, ["created_at", "updated_at", "departure_time", "arrival_time", "booking_date"])) {
                    $time = strtotime($request->get($field, date("Y-m-d H:i:s")));
                    $booking->$method(new DateTime($time));
                } else {
                    $booking->$method($request->get($field));
                }
            } catch (\Throwable $th) {
                throw $th;
            }
        }

        $entityManager->persist($booking);
        $entityManager->flush();
        $res = [];
        foreach ($this->getcolumns() as $col) {
            $method = ucwords(str_replace("_", " ", $col));
            $method = "get" . str_replace(" ", "", $method);
            $res[$col] = $booking->$method();
        }
    }

    /**
     * @Route("/delete/{id}", name="_delete")
     */
    public function delete($id, EntityManagerInterface $entityManager)
    {
        $booking = $this->getrepository()->find($id);
        $entityManager->remove($booking);
        $entityManager->flush();
    }
}
