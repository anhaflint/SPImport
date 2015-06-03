<?php

namespace SP\ImportBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

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
        try {
            $venues = $supplier1->getVenues();
        } catch( \Exception $e) {
            $output->writeln($e->getMessage());
        }

        $output->writeln('The job is done');
    }
}