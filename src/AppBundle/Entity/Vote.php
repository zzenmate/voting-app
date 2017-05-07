<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;

/**
 * Vote Entity
 *
 * @ORM\Table(name="votes")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\VoteRepository")
 *
 * @JMS\ExclusionPolicy("all")
 */
class Vote
{
    /**
     * @var int $id ID
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @JMS\Expose
     */
    protected $id;

    /**
     * @var ArrayCollection|VoteResult[] $results Vote results
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\VoteResult", mappedBy="vote")
     *
     * @JMS\Expose
     */
    private $results;

    /**
     * @var Session $session Session
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Session", inversedBy="votes")
     * @ORM\JoinColumn(nullable=false)
     *
     * @Assert\NotBlank()
     *
     * @JMS\Expose
     */
    protected $session;

    /**
     * @var string $name Name
     *
     * @ORM\Column(type="text", nullable=false)
     *
     * @Assert\NotBlank()
     * @Assert\Length(min="2")
     * @Assert\Type(type="string")
     *
     * @JMS\Expose
     */
    protected $name;

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
    protected $number;

    /**
     * @var string $type Type
     *
     * @ORM\Column(type="string", length=255, nullable=false)
     *
     * @Assert\NotBlank()
     * @Assert\Length(min="2", max="255")
     * @Assert\Type(type="string")
     *
     * @JMS\Expose
     */
    protected $type;

    /**
     * Get ID
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get session
     *
     * @return Session
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * Set session
     *
     * @param Session $session Session
     *
     * @return $this
     */
    public function setSession($session)
    {
        $this->session = $session;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name
     *
     * @param string $name Name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get number
     *
     * @return int
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Set number
     *
     * @param int $number Number
     *
     * @return $this
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set type
     *
     * @param string $type Type
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Set results
     *
     * @param ArrayCollection|VoteResult[] $results Vote results
     *
     * @return $this
     */
    public function setResults(ArrayCollection $results)
    {
        foreach ($results as $result) {
            $result->setVote($this);
        }
        $this->results = $results;

        return $this;
    }

    /**
     * Get results
     *
     * @return ArrayCollection|VoteResult[] Vote results
     */
    public function getResults()
    {
        return $this->results;
    }

    /**
     * Add results
     *
     * @param VoteResult $result Results
     *
     * @return $this
     */
    public function addVoteResult(VoteResult $result)
    {
        $this->results->add($result);

        return $this;
    }
    /**
     * Remove vote result
     *
     * @param VoteResult $result Event Group
     *
     * @return $this
     */
    public function removeVoteResult(VoteResult $result)
    {
        $this->results->remove($result);

        return $this;
    }
}
