<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Vote;
use Doctrine\Common\Collections\Expr\Comparison;
use Doctrine\ORM\EntityRepository;

/**
 * Vote Repository
 */
class VoteRepository extends EntityRepository
{
    /**
     * @param \DateTime $sessionDate Session Date
     * @param string    $comparison  Comparison
     * @param int       $limit       Limit
     * @param int       $offset      Offset
     *
     * @return Vote[]
     */
    public function findVotesBySessionDate(\DateTime $sessionDate, $comparison = Comparison::GT, $limit = 10, $offset = 0)
    {
        $qb = $this->createQueryBuilder('v');

        if ($comparison == Comparison::GT) {
            $expr = $qb->expr()->gt('s.date', ':session_date');
        } else {
            if ($comparison == Comparison::LT) {
                $expr = $qb->expr()->gt('s.date', ':session_date');
            } else {
                $expr = $qb->expr()->eq('s.date', ':session_date');
            }
        }

        return $qb->where($expr)
                  ->join('v.session', 's')
                  ->setParameters([
                      'session_date' => $sessionDate->format('Y-m-d'),
                  ])
                  ->setFirstResult($offset)
                  ->setMaxResults($limit)
                  ->getQuery()
                  ->getResult();
    }

    /**
     * Get count identical votes by deputy
     *
     * @param integer $id ID of deputy
     * @param integer $limit Limit
     *
     * @return array
     */
    public function getCountIdenticalVotesByDeputy($id, $limit)
    {
        $sql = "SELECT 
                vote_results_2.deputy_number,
                vote_results_2.deputy_full_name,
                count(vote_results_2.vote_id) as count_identical_votes
                FROM vote_results as vote_results_1
                LEFT JOIN vote_results as vote_results_2
                ON vote_results_1.vote_id = vote_results_2.vote_id
                AND vote_results_1.result = vote_results_2.result
                WHERE vote_results_1.deputy_number = :deputy_id 
                AND vote_results_2.deputy_number != :deputy_id
                AND vote_results_1.result != 'absent'
                AND vote_results_1.result 'not_voted'
                GROUP BY vote_results_2.deputy_number
                ORDER BY count_identical_votes DESC 
                LIMIT $limit";

        $params['deputy_id'] = (int) $id;
        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }
}
