<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;

/**
 * Session Entity
 *
 * @ORM\Table(name="sessions")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SessionRepository")
 *
 * @JMS\ExclusionPolicy("all")
 */
class Session
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
     * @var ArrayCollection|Vote[] $votes Votes
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Vote", mappedBy="session")
     */
    private $votes;

    /**
     * @var string $name Name
     *
     * @ORM\Column(type="string", length=255, nullable=false)
     *
     * @Assert\NotBlank()
     * @Assert\Length(min="2", max="255")
     * @Assert\Type(type="string")
     *
     * @JMS\Expose
     */
    protected $name;

    /**
     * @var \DateTime $date Date
     *
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @Assert\DateTime()
     *
     * @JMS\Expose
     */
    protected $date;

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
     * Get name
     *
     * @return string Name
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
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set date
     *
     * @param \DateTime $date Date
     *
     * @return $this
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Set votes
     *
     * @param ArrayCollection|Vote[] $votes Vote votes
     *
     * @return $this
     */
    public function setVotes(ArrayCollection $votes)
    {
        foreach ($votes as $result) {
            $result->setSession($this);
        }
        $this->votes = $votes;

        return $this;
    }

    /**
     * Get votes
     *
     * @return ArrayCollection|Vote[] Vote votes
     */
    public function getVotes()
    {
        return $this->votes;
    }
}
