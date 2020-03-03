<?php

/**
 * @package  BuuPay Mpesa
 * @subpackage  App\Service\Db
 * @author Mauko Maunde <mauko@buupass.com>
 * @copyright  2020 BuuPass Kenya Limited
 * @since  v2
 * 
 * @version v2.1
 */

namespace App\Service;

use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\ODM\MongoDB\DocumentManager;
use MongoDB\Client;
use App\Service\Http;
use App\Service\Redis;

class Token
{
    /**
     * @param $connection Mongodb connection
     */
    public function __construct(Http $http, Redis $redis)
    {
        $this->redis = $redis;
        $this->http = $http;
    }

    public function generate($operator = "modern_coast")
    {
        if ($operator == "modern_coast") {
            if ($this->redis->exists("MC_ACCESS_TOKEN")) {
                return $this->redis->get("MC_ACCESS_TOKEN");
            }

            $client = $this->http->to("http://173.212.223.234", [
                "Content-Type" => "application/x-www-form-urlencoded",
                "Accept" => "application/json",
            ]);

            $response = $client->post("/oauth/token", [
                "form_params" => [
                    "grant_type" => "client_credentials",
                    "client_id" => 3,
                    "client_secret" => "KdRVtxusxDUP8L3f4waFpyZIuLZb7JRePiXvzZ52",
                    "scope" => "*"
                ]
            ]);

            if ($response->getStatusCode() !== 200) {
                return "";
            }

            $authenticationData = \json_decode($response->getBody(), true);
            if (!is_array($authenticationData) || empty($authenticationData)) {
                return "";
            }

            $accessToken = $authenticationData["access_token"];
            // expire 5 minute early to allow for network issues
            $tokenLifetime = ((time() + $authenticationData["expires_in"]) - (time() + 300));
            $this->redis->set("MC_ACCESS_TOKEN", $accessToken, $tokenLifetime);
        } else {
            $accessToken = "";
        }

        return $accessToken;
    }
}
