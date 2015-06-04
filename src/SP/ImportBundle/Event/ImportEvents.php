<?php
/**
 * Created by PhpStorm.
 * User: Claire Remy
 * Date: 04/06/2015
 * Time: 13:51
 */

namespace SP\ImportBundle\Event;




final class ImportEvents {

    /**
     * This event is triggered every time the importer connects to a remote supplier API
     *
     * The Event listener receive an instance of
     * SP\ImportBundle\Event\FilterConnectionEvent
     */
    const IMPORT_EVENT = 'import.event';
}