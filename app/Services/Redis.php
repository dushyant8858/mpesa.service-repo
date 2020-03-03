<?php

/**
 * @package BuuPay Mpesa
 * @subpackage App\Services\Db
 * @author Mauko Maunde <mauko@buupass.com>
 * @copyright BuuPass Kenya Limited
 * @since v2
 * 
 * @version v2.1
 */

namespace App\Services;

use Predis\Client;

/**
 * Description of Redis
 *
 * @author david
 */
class Redis
{

    /**
     *
     * @var string 
     */
    private static $predisClient;

    /**
     * 
     * @param string $key
     * @param string $contents
     * @param int $expiry
     * @return boolean void|false
     */
    public static function set($key, $contents = null, $expiry = 600)
    {
        $predis = self::predis();
        $predis->set($key, $contents);
        $predis->expire($key, $expiry);
    }

    /**
     * 
     * @param string $key
     * @return string|null
     */
    public static function get($key)
    {
        $predis = self::predis();
        if ($predis->exists($key)) {
            return $predis->get($key);
        }
        return null;
    }

    /**
     * 
     * @param string $key
     * @return int
     */
    public static function del($key)
    {
        $predis = self::predis();
        return $predis->del($key);
    }

    /**
     * 
     * @param string $key
     * @return boolean
     */
    public static function exists($key)
    {
        $predis = self::predis();
        return $predis->exists($key);
    }

    /**
     * 
     * @return PredisClient
     */
    private static function predis()
    {
        if (self::$predisClient) {
            return self::$predisClient;
        }
        self::$predisClient = new Client();
        //$client = new Client("tcp://127.0.0.1:6379");
        return self::$predisClient;
    }

    /**
     * 
     * @param string $prefix
     * @return string
     */
    public static function generateKey($prefix)
    {
        return strtolower(implode("_", func_get_args()));
    }
}
