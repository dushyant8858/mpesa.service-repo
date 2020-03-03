<?php

/**
 * @package BuuPay Mpesa
 * @subpackage App\Services\Log
 * @author Mauko Maunde <mauko@buupass.com>
 * @copyright BuuPass Kenya Limited
 * @since v2
 * 
 * @version v2.1
 */

namespace App\Services;

use App\Services\Sms;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;
use Monolog\Handler\SwiftMailerHandler;

class Log
{
    private $channel = "file";
    private $sms;
    private $message;
    private $logger;

    /**
     * __construct
     *
     * @param  App\Services\Sms $sms
     * @param App\Services\Redis $redis
     *
     * @return void
     */
    public function __construct(Sms $sms, Redis $redis)
    {
        $this->sms = $sms;
        $this->redis = $redis;
    }

    /**
     * @param $handle Name of log file
     * @param $message Data to write to file
     * @param $level 
     *  => DEBUG (100): Detailed debug information.
     *  => INFO (200): Interesting events. Examples: User logs in, SQL logs.
     *  => NOTICE (250): Normal but significant events.
     *  => WARNING (300): Exceptional occurrences that are not errors. Examples: Use of deprecated APIs, poor use of an API, undesirable things that are not necessarily wrong.
     *  => ERROR (400): Runtime errors that do not require immediate action but should typically be logged and monitored.
     *  => CRITICAL (500): Critical conditions. Example: Application component unavailable, unexpected exception.
     *  => ALERT (550): Action must be taken immediately. Example: Entire website down, messagebase unavailable, etc. This should trigger the SMS alerts and wake you up.
     *  => EMERGENCY (600): Emergency: system is unusable.
     */
    public function new($message, $handle = "app.log", $name = "logger", $level = "DEBUG")
    {
        $this->message = is_array($message) ? json_encode($message) : $message;

        $action = strtolower($level);
        $this->logger = new Logger($name);
        $this->logger->pushHandler(new StreamHandler("../logs/" . $handle, $level));

        if (is_array($this->channel)) {
            foreach ($this->channel as $channel) {
                $this->log($channel, $action, $level);
            }
        } else {
            $this->log($this->channel, $action);
        }
    }

    /**
     * log Log the message through set channel(s)
     *
     * @param  mixed $channel
     * @param  mixed $action
     *
     * @return void
     */
    public function log($channel, $action, $level = "DEBUG")
    {
        switch ($channel) {
            case "sms":
                //, "254799966665", "254727659180", "254716578712"
                $this->sms->send(["254705459494"], $this->message);

                break;

            case "file":
                $this->logger->pushHandler(new FirePHPHandler());
                $this->logger->$action($this->message);
                break;

            case "mail":
                //$action = "add".ucfirst($action);
                // Create the Transport
                $transporter = new \Swift_SmtpTransport("smtp.gmail.com", 465, "ssl");
                $transporter->setUsername("mauko@buupass.com");
                $transporter->setPassword("7#1R733Nl00p");

                // Create the Mailer using your created Transport
                $mailer = new \Swift_Mailer($transporter);

                // Create a message
                $message = (new \Swift_Message("New {$action} alert on BuuPass Payments."));
                $message->setFrom(["info@buupass.com" => "BuuPass"]);
                $message->setTo(["maukoese@gmail.com" => "Mauko", "tony@buupass.com" => "Tony"]);
                $message->setBody($this->message);
                $this->logger->pushHandler(new SwiftMailerHandler($mailer, $message, $level, false));
                $this->logger->$action($this->message);
                break;

            case "redis":
                $this->redis->set($action . time(), $this->message);
                break;

            default:
                $this->logger->pushHandler(new FirePHPHandler());
                $this->logger->$action($this->message);
                break;
        }
    }

    /**
     * channel - set the log channel
     *
     * @param  mixed $channel Channel to log to
     *
     * @return App\Services\Log
     */
    public function channel($channel = "file")
    {
        $this->channel = $channel;
        return $this;
    }
}
