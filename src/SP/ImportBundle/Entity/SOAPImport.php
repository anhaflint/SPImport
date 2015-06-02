<?php

namespace SP\ImportBundle\Entity;

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
