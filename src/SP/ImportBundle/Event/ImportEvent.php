<?php
/**
 * Created by PhpStorm.
 * User: Claire Remy
 * Date: 04/06/2015
 * Time: 14:01
 */

namespace SP\ImportBundle\Event;


use Symfony\Component\EventDispatcher\Event;

class ImportEvent extends Event{
    protected $message;

    public function __construct($message)
    {
        $this->message = $message;
    }

    public function getMessage()
    {
        return $this->message;
    }
}