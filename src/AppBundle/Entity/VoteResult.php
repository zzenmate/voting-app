<?php

namespace AppBundle\Entity;

use AppBundle\DBAL\Types\VoteResultType;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Fresh\DoctrineEnumBundle\Validator\Constraints as DoctrineAssert;
use JMS\Serializer\Annotation as JMS;

/**
 * VoteResult Entity
 *
 * @ORM\Table(name="vote_results")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\VoteResultRepository")
 *
 * @JMS\ExclusionPolicy("all")
 */
class VoteResult
{
    /**
     * @var int $id ID
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var Vote $vote Vote
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Vote", inversedBy="results")
     * @ORM\JoinColumn(nullable=false)
     *
     * @Assert\NotBlank()
     */
    protected $vote;

    /**
     * @var int $number Number
     *
     * @ORM\Column(type="integer", nullable=false)
     *
     * @Assert\NotBlank()
     * @Assert\Type(type="integer")
     *
     * @JMS\Expose
     */
    protected $deputyNumber;

    /**
     * @var string $fullName Full name
     *
     * @ORM\Column(type="string", length=255, nullable=false)
     *
     * @Assert\NotBlank()
     * @Assert\Length(min="2", max="255")
     * @Assert\Type(type="string")
     *
     * @JMS\Expose
     */
    protected $deputyFullName;

    /**
     * @var VoteResultType $result Result
     *
     * @ORM\Column(name="result", type="VoteResultType", nullable=false)
     *
     * @DoctrineAssert\Enum(entity="AppBundle\DBAL\Types\VoteResultType")
     *
     * @JMS\Expose
     */
    protected $result;

    /**
     * Get ID
     *
     * @return int Id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get vote
     *
     * @return Vote Vote
     */
    public function getVote()
    {
        return $this->vote;
    }

    /**
     * Set vote
     *
     * @param Vote $vote Vote
     *
     * @return $this
     */
    public function setVote($vote)
    {
        $this->vote = $vote;

        return $this;
    }

    /**
     * Get deputy number
     *
     * @return int DeputyNumber
     */
    public function getDeputyNumber()
    {
        return $this->deputyNumber;
    }

    /**
     * Set deputy number
     *
     * @param int $deputyNumber Deputy number
     *
     * @return $this
     */
    public function setDeputyNumber($deputyNumber)
    {
        $this->deputyNumber = $deputyNumber;

        return $this;
    }

    /**
     * Get deputy full name
     *
     * @return string
     */
    public function getDeputyFullName()
    {
        return $this->deputyFullName;
    }

    /**
     * Set deputy full name
     *
     * @param string $deputyFullName Deputy full name
     *
     * @return $this
     */
    public function setDeputyFullName($deputyFullName)
    {
        $this->deputyFullName = $deputyFullName;

        return $this;
    }

    /**
     * Get result
     *
     * @return VoteResultType Result
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Set result
     *
     * @param VoteResultType $result
     *
     * @return $this
     */
    public function setResult($result)
    {
        $this->result = $result;

        return $this;
    }
}
