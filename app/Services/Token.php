<?php

/**
 * @package  BuuPay Mpesa
 * @subpackage  App\Services\Db
 * @author Mauko Maunde <mauko@buupass.com>
 * @copyright  2020 BuuPass Kenya Limited
 * @since  v2
 * 
 * @version v2.1
 */

namespace App\Services;

use App\Services\Http;
use App\Services\Redis;

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
            if ($this->redis->exists("MC_access_token")) {
                return $this->redis->get("MC_access_token");
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
            $this->redis->set("MC_access_token", $accessToken, $tokenLifetime);
        } else {
            $accessToken = "";
        }

        return $accessToken;
    }

    public function operatorToken($operator)
    {
        if ($this->redis->exists("{$operator}_access_token")) {
            return $this->redis->get("{$operator}_access_token");
        }
        $guzzleClient = $this->client->to("https://dev.{$operator}.buupass.com");
        $response = $guzzleClient->postJson("/api/accounts/login/", [
            "username" => "voucher_admin",
            "password" => "bp@voucher19"
        ]);
        //{"data":{"token":"16ee4723586f88032d051f1ba7afa178b4bee0c6","expires_in":"3599.994309"}}
        if ($response->getStatusCode() !== 200) {
            return "";
        }
        $authenticationData = \json_decode($response->getBody(), true);
        if (!is_array($authenticationData) || empty($authenticationData)) {
            return "";
        }
        if (
            !isset($authenticationData["data"]["token"]) ||
            !isset($authenticationData["data"]["expires_in"])
        ) {
            return "";
        }
        $accessToken = $authenticationData["data"]["token"];
        // expire 5 minute early to allow for network issues
        $tokenLifetime = (int) $authenticationData["data"]["expires_in"];
        $this->redis->set("{$operator}_access_token", $accessToken, ($tokenLifetime - 300));

        return $accessToken;
    }
}
