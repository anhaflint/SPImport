<?php

namespace SP\ImportBundle\Command;

use SP\ImportBundle\Event\ImportListener;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

class ImportCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('sp:import')
            ->setDescription('Import from suppliers into SP database')
            ->addArgument('supplier', InputArgument::OPTIONAL, 'From which supplier would you like to import data ?');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $supplier = $input->getArgument('supplier');
        $supplier1 = $this->getApplication()->getKernel()->getContainer()->get('sp1.import');

        //Listen to importer events
        $dispatcher = $supplier1->getDispatcher();
        $listener = new ImportListener();
        $dispatcher->addListener('import.event', array($listener, 'onConsoleImportEvent'));

        try {
            $supplier1->getVenues();
        } catch( \Exception $e) {
            $output->writeln($e->getMessage());
        }
    }
}