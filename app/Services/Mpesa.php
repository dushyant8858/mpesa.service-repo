<?php
/**
 * @package BuuPay Mpesa
 * @subpackage App\Services\Mpesa
 * @author Mauko Maunde <mauko@buupass.com>
 * @copyright BuuPass Kenya Limited
 * @since v2
 * 
 * @version v2.1
 */

namespace App\Services;

use Osen\Mpesa\STK;
use Osen\Mpesa\C2B;
use Osen\Mpesa\B2C;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class Mpesa
{
    /**
     * setOperator Set M-PESA Operator Details for STK Push, et al
     *
     * @param  string $slug
     * @param  string $base
     *
     * @return void
     */
    public function setOperator($slug = "buupass")
    {
        $operators = json_decode(file_get_contents("../operators.json"), true);
        $prefix = strtoupper(substr($slug, 0, 4));
        $operator = $operators[$prefix];
        $operator = (object)$operator;

        STK::init(
            array(
                "env"               => $operator->env ?? "sandbox",
                "type"              => $operator->type ?? 4,
                "shortcode"         => $operator->shortcode ?? "174379",
                "headoffice"        => $operator->headoffice ?? "174379",
                "key"               => $operator->key ?? "kM3NaLxdueY4p4ORDH59MqaBBAknXurI",
                "secret"            => $operator->secret ?? "wNl3eLMJkgfb0TQl",
                "username"          => $operator->username ?? "",
                "password"          => $operator->password ?? "",
                "passkey"           => $operator->passkey ?? "bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919",
                "validation_url"    => $operator->validation ?? url("validate/{$operator->alias}"),
                "confirmation_url"  => $operator->confirmation ?? url("confirm"),
                "callback_url"      => $operator->callback ?? url("reconcile/{$operator->alias}"),
                "results_url"       => $operator->results ?? url("results/{$operator->alias}")
            )
        );

        C2B::init(
            array(
                "env"               => $operator->env ?? "sandbox",
                "type"              => $operator->type ?? 4,
                "shortcode"         => $operator->shortcode ?? "174379",
                "headoffice"        => $operator->headoffice ?? "174379",
                "key"               => $operator->key ?? "kM3NaLxdueY4p4ORDH59MqaBBAknXurI",
                "secret"            => $operator->secret ?? "wNl3eLMJkgfb0TQl",
                "username"          => $operator->username ?? "",
                "password"          => $operator->password ?? "",
                "passkey"           => $operator->passkey ?? "bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919",
                "validation_url"    => $operator->validation ?? url("validate/{$operator->alias}"),
                "confirmation_url"  => $operator->confirmation ?? url("confirm"),
                "callback_url"      => $operator->callback ?? url("reconcile/{$operator->alias}"),
                "results_url"       => $operator->results ?? url("results/{$operator->alias}")
            )
        );

        B2C::init(
            array(
                "env"               => $operator->env ?? "sandbox",
                "type"              => $operator->type ?? 4,
                "shortcode"         => $operator->shortcode ?? "174379",
                "headoffice"        => $operator->headoffice ?? "174379",
                "key"               => $operator->key ?? "kM3NaLxdueY4p4ORDH59MqaBBAknXurI",
                "secret"            => $operator->secret ?? "wNl3eLMJkgfb0TQl",
                "username"          => $operator->username ?? "",
                "password"          => $operator->password ?? "",
                "validation_url"    => $operator->validation ?? url("validate/{$operator->alias}"),
                "confirmation_url"  => $operator->confirmation ?? url("confirm"),
                "results_url"       => $operator->results ?? url("results/{$operator->alias}")
            )
        );
    }
}
