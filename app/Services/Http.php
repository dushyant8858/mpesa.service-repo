<?php

/**
 * @package BuuPay Mpesa
 * @subpackage App\Services\Http
 * @author Mauko Maunde <mauko@buupass.com>
 * @copyright BuuPass Kenya Limited
 * @since v2
 * 
 * @version v2.1
 */

namespace App\Services;

use \GuzzleHttp\Client;

class Http
{
    public $client;
    public $base;
    public $headers = array();
    public $options = array();
    public $response;

    public function __construct()
    {
        $this->client = new Client;
    }

    /**
     * to
     *
     * @param  mixed $base
     * @param  mixed $timeout
     *
     * @return object Http
     */
    public function to($base = "", $headers = [], $timeout = 2.0)
    {
        $client = new Client(
            [
                "base_uri" => $base,
                "timeout"  => $timeout,
                "headers" => $headers
            ]
        );

        return $client;
    }

    /**
     * to
     *
     * @param  mixed $base
     * @param  mixed $timeout
     *
     * @return object Http
     */
    public function from($base = "/", $headers = [], $timeout = 2.0)
    {
        $client = new Client(
            [
                "base_uri" => $base,
                "timeout"  => $timeout,
                "headers" => $headers
            ]
        );

        return $client;
    }
}
