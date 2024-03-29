<?php

namespace SP\ImportBundle\Services;


/**
 * SupplierImportInterface
 *
 */
Interface SupplierImportInterface
{
    /**
     * Returns all venues for the supplier
     * Or just one if $id is defined
     *
     * @param $id
     * @return mixed
     */
    public function getVenues($id);

    /**
     * Returns all productions for the supplier
     * Or just one production if $id is defined
     *
     * @param $id
     * @return mixed
     */
    public function getProductions($id);

    /**
     * Returns all performances for the supplier
     *
     * @return mixed
     */
    public function getPerformances();
}
