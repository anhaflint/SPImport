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
    protected $colour;

    public function __construct($message, $colour)
    {
        $this->colour = $colour;
        $this->message = $message;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getColour()
    {
        return $this->colour;
    }
}