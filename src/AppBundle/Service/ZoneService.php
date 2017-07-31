<?php

namespace AppBundle\Service;

use AppBundle\Repository\VoteRepository;
use AppBundle\Repository\VoteResultRepository;

/**
 * Zone Service
 */
class ZoneService
{
    /** @var VoteRepository $voteRepository */
    protected $voteRepository;

    /** @var VoteResultRepository $voteResultRepository */
    protected $voteResultRepository;

    /**
     * Constructor
     *
     * @param VoteRepository       $voteRepository       Vote repository
     * @param VoteResultRepository $voteResultRepository Vote result repository
     */
    public function __construct(VoteRepository $voteRepository, VoteResultRepository $voteResultRepository)
    {
        $this->voteRepository       = $voteRepository;
        $this->voteResultRepository = $voteResultRepository;
    }

    /**
     * Get count identical votes by deputy
     *
     * @param int $deputyID ID of deputy
     * @param int $limit    Limit
     *
     * @return array
     */
    public function getCountIdenticalVotesByDeputy($deputyID, $limit)
    {
        $countIdenticalVotesByDeputy = $this->voteRepository->getCountIdenticalVotesByDeputy($deputyID, $limit);
        $totalCountVotes = $this->getTotalCountVotesByDeputy($deputyID);

        foreach ($countIdenticalVotesByDeputy as $key => $countIdenticalVote) {
            $countIdenticalVotesByDeputy[$key]['impact_coefficient'] = $countIdenticalVote['count_identical_votes'] / $totalCountVotes;
        }

        return $countIdenticalVotesByDeputy;
    }

    /**
     * Get total count votes by deputy
     *
     * @param int $deputyID ID of deputy
     *
     * @return int
     */
    public function getTotalCountVotesByDeputy($deputyID)
    {
        return $this->voteResultRepository->getCountVotesByDeputyNumber($deputyID);
    }
}
