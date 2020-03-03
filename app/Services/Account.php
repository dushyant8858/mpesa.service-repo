<?php

/**
 * @package BuuPay Mpesa
 * @subpackage App\Services\Account
 * @author Mauko Maunde <mauko@buupass.com>
 * @copyright BuuPass Kenya Limited
 * @since v2
 * 
 * @version v2.1
 */

namespace App\Services;

class Account
{
    /**
     * Set unique account identifier for STK Push
     *
     * @param  string $operator
     * @param  string $account
     *
     * @return void
     */
    public function set($operator = "buupass", $account =  null)
    {
        switch ($operator) {
            case "buupass":
            case "crown":
                $prefix = substr($operator, 0, 4);
                $account = is_null($account) ? $prefix . substr(md5(time()), 0, 5) : $account;
                break;

            default:
                $prefix = substr($operator, 0, 4);
                $account = is_null($account) ? $prefix . substr(md5(time()), 0, 5) : $account;
                break;
        }

        return strtoupper($account);
    }
}
