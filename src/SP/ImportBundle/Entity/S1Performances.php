<?php

namespace SP\ImportBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * S1Performances
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="SP\ImportBundle\Entity\S1PerformancesRepository")
 */
class S1Performances
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="s1ShowId", type="integer")
     */
    private $s1ShowId;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255, nullable=true)
     */
    private $type;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="date", nullable=true)
     */
    private $date;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="time", type="time", nullable=true)
     */
    private $time;


    /**
     * @ORM\ManyToOne(targetEntity="SP\ImportBundle\Entity\S1Productions", inversedBy="performances")
     * @ORM\JoinColumn(nullable=false)
     */
    private $show;

    public function __construct(array $performance)
    {
        $this->update($performance);
    }

    public function update(array $performance)
    {
        $this->setS1ShowId($performance['s1ShowId']);
        $this->setType($performance['type']);
        $this->setDate(new \DateTime($performance['date']));
        $this->setTime(new \DateTime($performance['time']));
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set showId
     *
     * @param integer $showId
     * @return S1Performances
     */
    public function setS1ShowId($showId)
    {
        $this->s1ShowId = $showId;

        return $this;
    }

    /**
     * Get showId
     *
     * @return integer 
     */
    public function getS1ShowId()
    {
        return $this->s1ShowId;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return S1Performances
     */
    public function setType($type)
    {
        $this->type = $type;

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
     * Set date
     *
     * @param \DateTime $date
     * @return S1Performances
     */
    public function setDate($date)
    {
        $this->date = $date;

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
     * Set time
     *
     * @param \DateTime $time
     * @return S1Performances
     */
    public function setTime($time)
    {
        $this->time = $time;

        return $this;
    }

    /**
     * Get time
     *
     * @return \DateTime 
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * Set show
     *
     * @param \SP\ImportBundle\Entity\S1Productions $show
     * @return S1Performances
     */
    public function setShow(\SP\ImportBundle\Entity\S1Productions $show)
    {
        $this->show = $show;

        return $this;
    }

    /**
     * Get show
     *
     * @return \SP\ImportBundle\Entity\S1Productions
     */
    public function getShow()
    {
        return $this->show;
    }
}
