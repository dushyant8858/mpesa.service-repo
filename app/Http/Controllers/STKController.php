<?php

namespace App\Http\Controllers;

use DateTime;
use App\Services\Account;
use App\Services\Log;
use App\Services\Mpesa;
use App\Services\Redis;
use App\Services\Sms;
use App\Services\Token;
use App\Payment;
use App\Booking;
use App\BusBooking;
use App\FlightBooking;
use App\CarBooking;
use App\TrainBooking;
use App\Services\Http;
use Osen\Mpesa\STK;
use Laminas\ApiTools\ApiProblem\ApiProblem;
use Illuminate\Http\Request;

class StkController extends Controller
{
    private $entityManager;
    private $operator;
    private $serializer;

    public function __construct(Redis $redis, Http $http)
    {
    }

    /**
     * 
     * @param  Request $request
     * @param  Mpesa $mpesa
     * @param  Log $log
     * @param  Http $http
     * @param  Account $account
     * @param  mixed $operator
     *
     * @return Response
     */
    public function __(Request $request, Mpesa $mpesa, Log $log, Http $http, Token $token, Sms $sms, Account $account, Redis $redis, $operator = "buupass")
    {
        $this->operator = $operator;

        $default_paybill = "796440";
        $setOperator = "";
        $allOperators = file_get_contents("../operators.json");
        $allOperators = json_decode($allOperators, true);
        $base = substr($operator, 0, 4);

        foreach ($allOperators as $key => $value) {
            if (strtoupper($base) == $key) {
                $setOperator = (object) $value;
                $slug = $setOperator->alias;
            }
        }

        $params = json_decode($request->getContent(), true);

        if (isset($params["data"])) {
            $pay_data = json_decode($params["data"], true);
        } else {
            $pay_data = $params;
        }

        $operator = null;
        if (isset($pay_data["operator"])) {
            if (is_array($pay_data["operator"])) {
                //Check for alias
                if (isset($pay_data["operator"]["alias"])) {
                    $operator = $pay_data["operator"]["alias"];
                }
            } else {
                $operator = $pay_data["operator"];
            }
        }
        $mpesa->setOperator($operator);

        $first_name = $last_name  = "";
        if (isset($pay_data["payee"]["first_name"]) or isset($pay_data["payee"]["last_name"])) {
            $full_name = $pay_data["payee"]["first_name"] . " " . $pay_data["payee"]["last_name"];
        } elseif (isset($pay_data["first_name"]) or isset($pay_data["last_name"])) {
            $full_name = $first_name . " " . $last_name;
        } elseif (isset($pay_data["payee"]["full_name"])) {
            $full_name = $pay_data["payee"]["full_name"];
        } else {
            $full_name = $pay_data["full_name"];
        }
        $pay_data["full_name"] = $full_name;
        $pay_data["currency"]  = $params["currency"] ?? "KES";
        $transactionId         = "";
        $timestamp             = (date("Ymdhis"));
        $paybill               = $setOperator->shortcode ?? $default_paybill;

        $paybill_configurations = $setOperator;

        if (isset($params["total_fare"])) {
            $params["amount"] = $pay_data["amount"] = $params["total_fare"];
        }

        if (isset($pay_data["payee"]["phone"])) {
            $pay_data["phone"] = $pay_data["payee"]["phone"];
        }

        // check for mandatory fields
        foreach (["amount", "phone", "order_reference"] as $field) {
            if (!isset($pay_data[$field]) or $pay_data[$field] == "") {
                $errorResponse = array_merge($params, ["status" => "error", "message" => $field . " field is required"]);
                exit(json_encode($errorResponse));
                break;
            }
        }

        $paybill = $params["paybill_no"] ?? $paybill;
        if ($operator == "modern_coast") {
            $mCAccessToken = $token->generate($operator);
            $payeePhone = $pay_data["phone"];

            try {
                $paybill      = "525600";
                $client = $http->to("http://173.212.223.234", [
                    "Authorization" => "Bearer " . $mCAccessToken,
                    "Origin"        => "*",
                    "Content-Type"  => "application/json",
                    "Accept"        => "application/json",
                ]);
                $redis->set("payData:" . $payeePhone, \json_encode($pay_data));
                $outBoundData = $pay_data["incoming_payload"]["payment_request_payload"];
                $redis->set("payData_type:" . $payeePhone, gettype($outBoundData));
                $mcRequestPaymentPayload = $client->post("/api/v3/stkpush", ["json" => $outBoundData]);
                $stk_response                         = $mcRequestPaymentPayload->getBody();
                $pay_data["payment_request_response"] = $stk_response;
                $stk_response                         = str_replace("success", "", $stk_response);
                $stk_response                         = json_decode($stk_response, true);
                $stk_error                            = false;
            } catch (\Exception $ex) {
                $stk_response                         = [];
                $stk_error                            = true;
                $pay_data["payment_request_response"] = "A-" . $ex->getMessage();
                $redis->set("payData_error:" . $payeePhone, $pay_data["payment_request_response"]);
                // if ($ex instanceof BadResponseException) {
                //     $pay_data["payment_request_response"] = "B-" . $ex->getResponse()->getBody();
                //     $redis->set("payData_error_full:" . $payeePhone, $pay_data["payment_request_response"]);
                // }
            }
        } else {
            // all bus operators checkout here but for moderncoast

            $system_transaction_id = $account->set($operator, substr(($pay_data["order_reference"] ?? "000"), 0, 15));
            $TransactionDesc   = $pay_data["description"] ?? "Fare Payment";

            $log->channel(["file"])->new("=== " . date("Y-m-d H:i:s") . " ===\n", "stk_push_" . date("Y-m-d") . ".log", "{$operator}_stk", "ERROR");
            $log->new(" User : " . $pay_data["full_name"] . ", Phone : " . $pay_data["phone"] . "\n", "stk_push_" . date("Y-m-d") . ".log", "{$operator}_stk", "ERROR");

            try {
                $response = STK::send(
                    $pay_data["phone"],
                    $pay_data["amount"],
                    $system_transaction_id,
                    "{$operator} Transaction",
                    "{$operator} Transaction Request",
                    function ($response) use ($log, $request, $operator, $account, $setOperator) {
                        $handle = "stk_push_" . date("Y-m-d") . ".log";
                        $log->new($response, $handle, "{$operator}_stk", "INFO");

                        if (isset($response["errorCode"])) {
                            switch ($response["errorCode"]) {
                                case "value":
                                    $message = "[PAYMENT ERROR] Request {$response["requestId"]} to {$setOperator->name}: {$response["errorMessage"]}";
                                    break;

                                default:
                                    $message = "[PAYMENT ERROR] Request {$response["requestId"]} to {$setOperator->name}: {$response["errorMessage"]}";
                                    break;
                            }

                            $handle = "stk_push_error_" . date("Y-m-d") . ".log";
                            $log->channel(["file", "redis", "sms"])->new($message, $handle, "{$operator}_stk", "ERROR");

                            return $response;
                        } elseif (isset($response["responseCode"])) {
                            switch ($response["responseCode"]) {
                                case "value":
                                    $message = "[PAYMENT ERROR] From {$setOperator->name}: {$response["responseDesc"]}";
                                    break;

                                default:
                                    $message = "[PAYMENT ERROR] From {$setOperator->name}: {$response["responseDesc"]}";
                                    break;
                            }

                            $handle = "stk_push_error_" . date("Y-m-d") . ".log";
                            $log->channel(["file", "redis", "sms"])->new($message, $handle, "{$operator}_stk", "ERROR");
                            return $response;
                        } else {
                            return $response; //$response["MerchantRequestID"];
                        }
                    }
                );

                $log->channel(["file"])->new("=== " . date("Y-m-d H:i:s") . " ===", "stk_push_" . date("Y-m-d") . ".log", "{$operator}_stk", "ERROR");

                $stk_response = $response;
                $stk_error    = false;
            } catch (\Exception $e) {
                $handle = "stk_push_error_" . date("Y-m-d") . ".log";
                $log->channel(["file", "redis", "sms"])->new($e->getMessage(), $handle, "{$operator}_stk", "ERROR");

                $response = [
                    "errorCode" => 1,
                    "errorMessage" => $e->getMessage()
                ];

                $stk_response = $response;
                $stk_error    = true;

                $log->channel(["file", "redis"])->new($response, "stk_push_" . date("Y-m-d") . ".log", "{$operator}_stk", "ERROR");
                $log->channel(["file"])->new("=== " . date("Y-m-d H:i:s") . " ===", "stk_push_" . date("Y-m-d") . ".log", "{$operator}_stk", "ERROR");
            }
        }

        if (isset($stk_response["errorMessage"])) {
            //Send SMS
            $stk_error = ["status" => "error", "message" => "Error Checking Out", "stk_response" => $stk_response];
        } elseif (isset($stk_response["fault"])) {
            //Send Instructions
            $stk_error = ["status" => "error", "message" => "Fault While processing", "stk_response" => $stk_response];
        }

        $transaction_id      = $pay_data["transaction_id"] = ($stk_response["MerchantRequestID"] ?? null);
        $operator_booking_id = $pay_data["order_reference"] ?? "BP" . $account->set($operator);
        $checkout_time = $pay_data["checkout_time"] = date("Y-m-d H:i:s");

        $booking_id = "";
        $payment_id = "";

        $booking_data = isset($pay_data["booking_data"]) ? json_decode($pay_data["booking_data"], true) : $pay_data;

        $pay_data["operator_booking_id"] = $booking_data["operator_booking_id"] = $operator_booking_id;
        $booking_data["agent_id"] = $booking_data["booking_agent_id"] ?? "buupass";
        $booking_data["agent_id"] = $booking_data["paybill"] ?? $paybill;
        $booking_data["trade_id"] = $booking_data["order_reference"] ?? random_bytes(12);
        $booking_data["booking_status"] = 0;

        $booking                = $this->save_booking($request, $log, $booking_data);
        $booking_id             = $booking->getId();
        $booking_no             = $booking->getCustomBookingNo() ?? "";

        $pay_data["booking_id"]             = $booking_id;
        $pay_data["payment_channel"]        = $booking->getPaymentChannel() ?? "mpesa";
        $pay_data["booking_status"]         = 0;
        $pay_data["payment_status"]         = "pending";
        $pay_data["system_transaction_id"]  = $system_transaction_id;
        $pay_data["provider_transaction_id"] = $transaction_id;
        $pay_data["gateway_confirmation_response"] =    null;
        $pay_data["confirmation_meta_data"] =    null;
        $pay_data["collector_transaction_reference"] =    null;
        $pay_data["collector_customer_reference"] =    null;
        $pay_data["collector_payment_status"] =    0;
        $pay_data["collector_response_code"] =    null;
        $pay_data["collector_response_code_description"] =    null;
        $pay_data["collector_metadata"] =    null;
        $pay_data["status_info"] =    null;
        $pay_data["status_description"] =    null;
        $pay_data["instructions_count"] =    null;
        $pay_data["receipt"] =    null;
        $pay_data["line"] = null;

        $log->channel(["file"])->new([$pay_data["phone"] => $booking_data], "booking_data_" . date("Y-m-d") . ".log", "{$operator}_booking", "INFO");

        // api clients should check this key for any errors
        $pay_data["stk_error"] = $stk_error;

        $payment               = new Payment();
        foreach (Payment::getFillable() as $field) {

            try {
                if (in_array($field, ["created_at", "updated_at", "departure_time", "arrival_time", "booking_date"])) {
                    $time = $pay_data[$field] ?? date("Y-m-dH:i:s");
                    $payment->$field = new DateTime($time);
                } else {
                    if (isset($pay_data[$field])) {
                        $payment->$field = $pay_data[$field];
                    }
                }
            } catch (\Throwable $th) {
                throw $th;
            }
        }
        
        $payment->save();

        $mode = $request->get("mode", "bus");
        $sms->send_instructions($payment, $booking->getId(), $booking_data, $mode);

        $payment_id = $pay_data["payment_id"] = $payment->getId();

        $payment_response = $payment;
        $booking_response = $booking;

        if ($operator == "modern_coast") {
            $booking = $this->brepository->find($booking_id);
        }

        if ($stk_error) {
            $stk_response = $stk_error;

            return compact("stk_response", "payment_response", "booking_response");
        }

        $status = "success";

        if ($operator == "easy_coach") {
            $extracted_paybill   = $params["paybill_no"] ?? $paybill;
            $extracted_amount    = $pay_data["amount"] ?? "";
            $phone_number        = $pay_data["phone"] ?? "";
            $booking_id          = $booking->id;
            $four_minutes_from_now = time() + (4 * 60);
            $date               = new DateTime();
            $date->setTimezone(new \DateTimeZone("Africa/Nairobi"));
            $date->setTimestamp($four_minutes_from_now);
            $payment_time = $date->format("h:i:s A");
            $message  = "Please pay Ksh {$extracted_amount} to Paybill {$extracted_paybill} Account number {$booking_id} by {$payment_time}. Ignore this message if you have already paid.";

            $result = $sms->send($phone_number, $message);
        }

        if (is_null($transaction_id)) {
            $transaction_id = $booking_response->getId();
        }

        return compact("transaction_id", "checkout_time", "payment_id", "status", "booking_id", "operator_booking_id", "booking_no", "stk_response", "payment_response", "booking_response");
        
    }

    /**
     * save_booking
     *
     * @param  mixed $request
     * @param  mixed $entityManager
     * @param  mixed $log
     * @param  mixed $params
     *
     * @return void
     */
    public function save_booking(Request $request, Log $log, $params = array())
    {

        $client_phone   = isset($params["payee"]["phone"]) ? $params["payee"]["phone"] : $params["phone"];
        $source         = isset($params["trip"]["route"]["from"]) ? $params["trip"]["route"]["from"] : $params["from"];
        $destination    = isset($params["trip"]["route"]["to"]) ? $params["trip"]["route"]["to"] : $params["to"];
        $operator_alias = null;
        if (isset($params["operator"])) {
            if (is_array($params["operator"])) {
                if (isset($params["operator"]["alias"])) {
                    $operator_alias = $params["operator"]["alias"];
                }
            } else {
                $operator_alias = $params["operator"];
            }
        }

        $date_of_travel = $params["trip"]["departure_date"] ?? $params["departure"] ?? $params["departure_date"];

        if ($params["booking_channel"] == "ussd") {
            $bus_id                 = isset($params["trip"]["route"]["bus_id"]) ? $params["trip"]["route"]["bus_id"] : $params["bus_id"];
            $bus_capacity           = isset($params["trip"]["route"]["bus_capacity"]) ? $params["trip"]["route"]["bus_capacity"] : $params["bus_capacity"];
            $schedule_id            = isset($params["trip"]["route"]["schedule_id"]) ? $params["trip"]["route"]["schedule_id"] : $params["schedule_id"];
            $schedule_code          = isset($params["trip"]["route"]["schedule_code"]) ? $params["trip"]["route"]["schedule_code"] : $params["schedule_code"];
            $seats_available        = isset($params["trip"]["route"]["seats_available"]) ? $params["trip"]["route"]["seats_available"] : $params["seats_available"];
            $number_of_booked_seats = $bus_capacity - $seats_available;
        } else {
            if (isset($params["trip"]["route"]["bus_id"])) {
                $bus_id                 = isset($params["trip"]["route"]["bus_id"]) ? $params["trip"]["route"]["bus_id"] : $params["bus_id"];
                $bus_capacity           = isset($params["trip"]["route"]["bus_capacity"]) ? $params["trip"]["route"]["bus_capacity"] : $params["bus_capacity"];
                $schedule_id            = isset($params["trip"]["route"]["schedule_id"]) ? $params["trip"]["route"]["schedule_id"] : $params["schedule_id"];
                $schedule_code          = isset($params["trip"]["route"]["schedule_code"]) ? $params["trip"]["route"]["schedule_code"] : $params["schedule_code"];
                $seats_available        = isset($params["trip"]["route"]["seats_available"]) ? $params["trip"]["route"]["seats_available"] : $params["seats_available"];
                $number_of_booked_seats = $bus_capacity - $seats_available;
            } else {
                $number_of_booked_seats = isset($params["trip"]["number_of_booked_seats"]) ? $params["trip"]["number_of_booked_seats"] : $params["number_of_booked_seats"];
                $bus_id                 = isset($params["trip"]["bus_id"]) ? $params["trip"]["bus_id"] : $params["bus_id"];
                $bus_capacity           = isset($params["trip"]["bus_capacity"]) ? $params["trip"]["bus_capacity"] : $params["bus_capacity"];
                $schedule_id            = isset($params["trip"]["schedule_id"]) ? $params["trip"]["schedule_id"] : $params["schedule_id"];
                $schedule_code          = isset($params["trip"]["schedule_code"]) ? $params["trip"]["schedule_code"] : $params["schedule_code"];
                $seats_available        = isset($params["trip"]["seats_available"]) ? $params["trip"]["seats_available"] : $params["seats_available"];
            }
        }

        $bus_id = (int) $bus_id;
        $passengers = $params["passengers"] ?? [];
        $mode = $request->get("mode", "bus");

        switch ($mode) {
            case "bus":
                $sql = BusBooking::where("bus_id", $bus_id)->where("date_of_travel",  "LIKE", "%$date_of_travel%")->where("booking_status",1)->get();
                break;

            case "flight":
                $sql = FlightBooking::where("flight_id", $bus_id)->where("date_of_travel",  "LIKE", "%$date_of_travel%")->where("booking_status",1)->get();
                break;

            default:
            $sql = BusBooking::where("bus_id", $bus_id)->where("date_of_travel",  "LIKE", "%$date_of_travel%")->where("booking_status",1)->get();
                break;
        }

        $buupass_booked_bus_seats = count($sql);

        $seats = [];
        foreach ($passengers as $key => $passenger) {
            $seats[] = $passenger["seat"];
        }

        $po = [
            "booking_organisation_id" => isset($params["booking_organisation_id"]) ? $params["booking_organisation_id"] : null,
            "booking_agent_id"        => isset($params["booking_agent_id"]) ? $params["booking_agent_id"] : null,
            "agent_id"                => isset($params["agent_id"]) ? $params["agent_id"] : null,
            "referral_code"           => isset($params["referral_code"]) ? $params["referral_code"] : null,
            "paybill"                 => $params["paybill"] ?? null,
            "operator"                => $operator_alias,
            "trade_id"                => $params["operator_booking_id"],
            "custom_booking_no"       => $params["operator_booking_id"],

            //START : OCCUPANCY TRACKING
            "schedule_id"             => $schedule_id,
            "schedule_code"           => $schedule_code,
            "seats_available"         => $seats_available,
            "seats"                   => $seats,
            "bus_id"                  => $bus_id,
            "bus_capacity"            => $bus_capacity,
            "number_of_booked_seats"  => $number_of_booked_seats,
            "buupass_booked_seats"    => $buupass_booked_bus_seats,
            "operator_booked_seats"   => $number_of_booked_seats - $buupass_booked_bus_seats,
            //END : OCCUPANCY TRACKING

            "departure_time"          => isset($params["trip"]["departure_time"]) ? $params["trip"]["departure_time"] : $params["departure"],
            "arrival_time"            => isset($params["trip"]["arrival_time"]) ? $params["trip"]["arrival_time"] : $params["arrival"],
            "phone"                   => $client_phone,
            "email"                   => $params["payee"]["email"] ?? $params["email"],
            "name"                    => $params["payee"]["full_name"] ?? $params["name"],
            "passengers"              => $passengers,
            "total_passengers"        => isset($params["passengers"]) && is_array($params["passengers"]) ? count($params["passengers"]) : $params["passengers"],
            "total_children"          => isset($params["children"]) ? count($params["children"]) : 0,
            "total_amount"            => isset($params["totalFare"]) ? $params["totalFare"] : $params["amount"],
            "payment_channel"         => isset($params["mode"]) ? $params["mode"] : "mpesa",
            "booking_channel"         => isset($params["booking_channel"]) ? $params["booking_channel"] : "web",
            "source"                  => $source,
            "referral_source"         => isset($params["referral_source"]) ? $params["referral_source"] : null,
            "destination"             => $destination,
            "route"                   => $source . "-" . $destination,
            "remote_reference"        => $params["operator_booking_id"],
            "booking_date"            => date("Y-m-d"),
            "date_of_travel"          => $date_of_travel,
            "booking_status"         => 0
        ];


        $b = new Booking;
        foreach ($this->getColumns() as $field) {

            try {
                if (in_array($field, ["created_at", "updated_at", "departure_time", "arrival_time", "booking_date", "date_of_travel"])) {
                    $time = $po[$field] ?? date("Y-m-dH:i:s");
                    $b->$field =new DateTime($time);
                } else {
                    if (isset($po[$field])) {
                        $b->$field =$po[$field];
                    }
                }
            } catch (\Throwable $th) {
                $handle = "booking_error_" . date("Y-m-d") . ".log";
                $log->channel(["file", "redis"])->new($th->getMessage(), $handle, "{$this->operator}_stk", "ERROR");
            }
        }
        $b->save();

        $po["booking_id"] = $b->getId();

        $bmode = ucwords($mode)."Booking";
        $booking = new $bmode;
        foreach ($bmode::getFillable() as $field) {

            try {
                if (in_array($field, ["created_at", "updated_at", "departure_time", "arrival_time", "booking_date", "date_of_travel"])) {
                    $time = $po[$field] ?? date("Y-m-dH:i:s");
                    $booking->$field = new DateTime($time);
                } else {
                    if (isset($po[$field])) {
                        $booking->$field =$po[$field];
                    }
                }
            } catch (\Throwable $th) {
                $handle = "booking_error_" . date("Y-m-d") . ".log";
                $log->channel(["file", "redis"])->new($th->getMessage(), $handle, "{$this->operator}_stk", "ERROR");
            }
        }
        
        $booking->save();

        return $booking;
    }
}
