<?php

namespace App\Controller;

use App\Entity\Payment;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse as Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("payments", name="payments")
 */
class PaymentController extends AbstractController
{
    private $columns;
    private $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $schemaManager = $entityManager->getConnection()->getSchemaManager();
        $columns = $schemaManager->listTableColumns("payment");

        $this->columns = array_diff(array_keys($columns), ["id"]);
        $this->repository = $entityManager->getRepository(Payment::class);
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
        $payments = $this->repository->findAll();
        $bs = [];
        $res = [];
        foreach ($payments as $b) {
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
        $payment = $this->repository->findOneBy($fields);
        if (!$payment) {
            return new Response(["error" => "Payment not found"]);
        }
        $res = [];
        foreach ($this->columns as $col) {
            $method = ucwords(str_replace("_", " ", $col));
            $method = "get" . str_replace(" ", "", $method);
            $res[$col] = $payment->$method();
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
        $payments = $this->repository->findBy($fields);
        $bs = [];
        $res = [];
        foreach ($payments as $b) {
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
    public function createPayment(Request $request, EntityManagerInterface $entityManager): Response
    {
        $payment = new Payment();
        foreach ($this->columns as $field) {
            $method = ucwords(str_replace("_", " ", $field));
            $method = "set" . str_replace(" ", "", $method);

            try {
                if (in_array($field, ["created_at", "updated_at"])) {
                    $time = $request->get($field, date("Y-m-dH:i:s"));
                    $payment->$method(new DateTime($time));
                } else {
                    $payment->$method($request->get($field));
                }
            } catch (\Throwable $th) {
                throw $th;
            }
        }

        $entityManager->persist($payment);
        $entityManager->flush();

        if (!$payment) {
            return new Response(["error" => "Payment not created"]);
        }
        $res = [];
        foreach ($this->columns as $col) {
            $method = ucwords(str_replace("_", " ", $col));
            $method = "get" . str_replace(" ", "", $method);
            $res[$col] = $payment->$method();
        }

        return new Response($res);
    }

    /**
     * @Route("/{id}", name="_show")
     */
    public function show($id)
    {
        $payment = $this->repository->find($id);
        if (!$payment) {
            return new Response(["error" => "Payment not found"]);
        }
        $res = [];
        foreach ($this->columns as $col) {
            $method = ucwords(str_replace("_", " ", $col));
            $method = "get" . str_replace(" ", "", $method);
            $res[$col] = $payment->$method();
        }

        return new Response($res);
    }

    /**
     * @Route("/edit/{id}", name="_edit")
     */
    public function update($id, Request $request, EntityManagerInterface $entityManager)
    {
        $payment = $this->repository->find($id);
        if (!$payment) {
            return new Response(["error" => "Payment not found"]);
        }

        foreach ($this->columns as $field) {
            $method = ucwords(str_replace("_", " ", $field));
            $method = "set" . str_replace(" ", "", $method);

            try {
                if (in_array($field, ["created_at", "updated_at", "departure_time", "arrival_time", "booking_date"])) {
                    $time = strtotime($request->get($field, date("Y-m-d H:i:s")));
                    $payment->$method(new DateTime($time));
                } else {
                    $payment->$method($request->get($field));
                }
            } catch (\Throwable $th) {
                throw $th;
            }
        }

        $entityManager->persist($payment);
        $entityManager->flush();
        $res = [];
        foreach ($this->columns as $col) {
            $method = ucwords(str_replace("_", " ", $col));
            $method = "get" . str_replace(" ", "", $method);
            $res[$col] = $payment->$method();
        }
    }

    /**
     * @Route("/delete/{id}", name="_delete")
     */
    public function delete($id, EntityManagerInterface $entityManager)
    {
        $payment = $this->repository->find($id);
        $entityManager->remove($payment);
        $entityManager->flush();
    }
}
