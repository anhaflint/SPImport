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

        $output->writeln($event->getMessage());
    }
}