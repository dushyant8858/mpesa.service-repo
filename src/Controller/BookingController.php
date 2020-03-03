<?php

namespace App\Controller;

use App\Entity\Booking;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse as Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;


/**
 * @Route("bookings", name="bookings")
 */
class BookingController extends AbstractController
{
    private $columns;
    private $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $schemaManager = $entityManager->getConnection()->getSchemaManager();
        $columns = $schemaManager->listTableColumns("booking");

        $this->columns = array_diff(array_keys($columns), ["id"]);
        $this->repository = $entityManager->getRepository(Booking::class);
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
        $bookings = $this->repository->findAll();
        $bs = [];
        $res = [];
        foreach ($bookings as $b) {
            foreach ($this->columns as $col) {
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
        $booking = $this->repository->findOneBy($fields);
        if (!$booking) {
            return new Response(["error" => "Booking not found"]);
        }
        $res = [];
        foreach ($this->columns as $col) {
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
        $bookings = $this->repository->findBy($fields);
        $bs = [];
        $res = [];
        foreach ($bookings as $b) {
            foreach ($this->columns as $col) {
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
        foreach ($this->columns as $field) {
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
        foreach ($this->columns as $col) {
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
        $booking = $this->repository->find($id);
        if (!$booking) {
            return new Response(["error" => "Booking not found"]);
        }
        $res = [];
        foreach ($this->columns as $col) {
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
        $booking = $this->repository->find($id);
        if (!$booking) {
            return new Response(["error" => "Booking not found"]);
        }

        foreach ($this->columns as $field) {
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
        foreach ($this->columns as $col) {
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
        $booking = $this->repository->find($id);
        $entityManager->remove($booking);
        $entityManager->flush();
    }
}
