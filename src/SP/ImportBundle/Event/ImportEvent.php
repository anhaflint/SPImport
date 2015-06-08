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
    protected $backgroundColour;

    public function __construct($message, $colour, $bgColour = 'black')
    {
        $this->colour = $colour;
        $this->message = $message;
        $this->backgroundColour = $bgColour;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getColour()
    {
        return $this->colour;
    }

    public function getBackgroundColour()
    {
        return $this->backgroundColour;
    }
}