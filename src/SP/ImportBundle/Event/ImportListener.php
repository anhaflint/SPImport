<?php
/**
 * Created by PhpStorm.
 * User: Claire Remy
 * Date: 04/06/2015
 * Time: 14:29
 */

namespace SP\ImportBundle\Event;


use Symfony\Component\Console\Output\ConsoleOutput;

class ImportListener {

    public function onConsoleImportEvent(ImportEvent $event)
    {
        $output = new ConsoleOutput();
        $colourStart = '<fg=' . $event->getColour() . '>';
        $colourEnd = '</fg=' . $event->getColour() . '>';
        $message = $event->getMessage();
        if(strpos($message, 'Warning') !== false) {
            $colourStart = '<fg=yellow>';
            $colourEnd = '</fg=yellow>';
        } elseif (strpos($message, 'Error') !== false) {
            $colourStart = '<fg=red>';
            $colourEnd = '</fg=red>';
        }
        $output->writeln($colourStart . $message . $colourEnd);
    }
}