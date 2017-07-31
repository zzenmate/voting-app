<?php

namespace AppBundle\Repository;

use AppBundle\DBAL\Types\VoteResultType;
use Doctrine\ORM\EntityRepository;

/**
 * VoteResult Repository
 */
class VoteResultRepository extends EntityRepository
{
    /**
     * Get count votes by deputy number
     *
     * @param int $deputyNumber Deputy number
     *
     * @return int
     */
    public function getCountVotesByDeputyNumber($deputyNumber)
    {
        $qb = $this->createQueryBuilder('vr');

        return $qb->select('count(vr.id) as count_votes')
                  ->where($qb->expr()->eq('vr.deputyNumber', ':deputy_number'))
                  ->andWhere($qb->expr()->neq('vr.result', ':absent'))
                  ->andWhere($qb->expr()->neq('vr.result', ':not_voted'))
                  ->setParameters([
                      'deputy_number' => $deputyNumber,
                      'absent' => VoteResultType::ABSENT,
                      'not_voted' => VoteResultType::NOT_VOTED,
                  ])
                  ->getQuery()
                  ->getOneOrNullResult();
    }
}
