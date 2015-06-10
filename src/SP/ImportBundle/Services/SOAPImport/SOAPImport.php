<?php

namespace SP\ImportBundle\Services\SOAPImport;

use Doctrine\ORM\Mapping as ORM;

/**
 * SOAPImport
 *
 */
abstract class SOAPImport implements SupplierImportInterface
{
    abstract public function getAllVenues();

    abstract public function getAllProductions();
}
