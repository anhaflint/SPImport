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
            ->addOption('supplier', null, InputOption::VALUE_OPTIONAL, 'From which supplier would you like to import data ?')
            ->addOption('type', null, InputOption::VALUE_OPTIONAL, 'Which data would you like to import ? venues, productions, performances ? defaults to all.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $supplier   = $input->getOption('supplier');
        $type       = $input->getOption('type');
        $supplier1  = $this->getApplication()->getKernel()->getContainer()->get('sp1.import');

        //Listen to importer events
        $dispatcher = $supplier1->getDispatcher();
        $listener   = new ImportListener();
        $dispatcher
            ->addListener('import.event', array($listener, 'onConsoleImportEvent'));

        try {
            switch($type)
            {
                case 'venues' :
                    $supplier1->getVenues();
                    break;
                case 'productions' :
                    $supplier1->getProductions();
                    break;
                default :
                    $supplier1->getVenues();
                    $supplier1->getProductions();
                    break;
            }
        } catch( \Exception $e) {
            $output->writeln($e->getMessage());
        }
    }
}