<?php

namespace SP\ImportBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * S1ProductionsRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class S1ProductionsRepository extends EntityRepository
{
    public function getDistinctVenueId()
    {
        $qb = $this->createQueryBuilder('v')
                    ->select('v.s1VenueId')
                    ->distinct()
                    ->orderBy('v.s1VenueId', 'ASC');

        return $qb->getQuery()->getResult();
    }
}
