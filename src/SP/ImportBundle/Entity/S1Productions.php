<?php

namespace SP\ImportBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * S1Productions
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="SP\ImportBundle\Repository\S1ProductionsRepository")
 */
class S1Productions
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
     * @ORM\Column(name="showId", type="integer")
     */
    private $showId;

    /**
     * @var string
     *
     * @ORM\Column(name="showName", type="string", length=255, nullable=true)
     */
    private $showName;

    /**
     * @var boolean
     *
     * @ORM\Column(name="isEvent", type="boolean", nullable=true)
     */
    private $isEvent;

    /**
     * @var string
     *
     * @ORM\Column(name="summary", type="text", nullable=true)
     */
    private $summary;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="priceFrom", type="decimal", nullable=true)
     */
    private $priceFrom;

    /**
     * @var string
     *
     * @ORM\Column(name="priceTo", type="decimal", nullable=true)
     */
    private $priceTo;

    /**
     * @var string
     *
     * @ORM\Column(name="ageRestriction", type="string", length=255, nullable=true)
     */
    private $ageRestriction;

    /**
     * @var boolean
     *
     * @ORM\Column(name="limitedStock", type="boolean", nullable=true)
     */
    private $limitedStock;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="bookingStarts", type="date", nullable=true)
     */
    private $bookingStarts;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="bookingEnds", type="date", nullable=true)
     */
    private $bookingEnds;

    /**
     * @var string
     *
     * @ORM\Column(name="whitelabelURI", type="string", length=255, nullable=true)
     */
    private $whitelabelURI;

    /**
     * @var string
     *
     * @ORM\Column(name="bannerBackgroundColour", type="string", length=255, nullable=true)
     */
    private $bannerBackgroundColour;

    /**
     * @var array
     *
     * @ORM\Column(name="resources", type="array", nullable=true)
     */
    private $resources;

    /**
     * @var array
     *
     * @ORM\Column(name="categories", type="array", nullable=true)
     */
    private $categories;

    /**
     * @var string
     *
     * @ORM\Column(name="showType", type="string", length=255, nullable=true)
     */
    private $showType;

    /**
     * @var integer
     *
     * @ORM\Column(name="s1VenueId", type="integer")
     */
    private $s1VenueId;

    /**
     * @ORM\ManyToOne(targetEntity="SP\ImportBundle\Entity\S1Venues")
     * @ORM\JoinColumn(nullable=false)
     */
    private $spVenue;

    /**
     * @ORM\OneToMany(targetEntity="SP\ImportBundle\Entity\S1Performances", mappedBy="show", cascade={"persist", "remove"})
     */
    private $performances;

    public function __construct(array $showInfo, S1Venues $venue)
    {
        self::update($showInfo);
        $this->setSPVenue($venue);
        $this->performances = new ArrayCollection();
    }

    /**
     * Update S1Production with all given data
     *
     * @param array $showInfo
     */
    public function update(array $showInfo)
    {
        $this->showId = $showInfo['showId'];
        $this->showName = $showInfo['showName'];
        $this->isEvent = $showInfo['isEvent'];
        $this->summary = $showInfo['summary'];
        $this->description = $showInfo['description'];
        $this->priceFrom = $showInfo['priceFrom'];
        $this->priceTo = $showInfo['priceTo'];
        $this->ageRestriction = $showInfo['ageRestriction'];
        $this->limitedStock = $showInfo['limitedStock'];
        $this->bookingStarts = new \DateTime($showInfo['bookingStarts']);
        $this->bookingEnds = new \DateTime($showInfo['bookingEnds']);
        $this->whitelabelURI = $showInfo['whitelabelURI'];
        $this->bannerBackgroundColour = $showInfo['bannerBackgroundColour'];
        $this->resources = $showInfo['resources'];
        $this->categories = $showInfo['categories'];
        $this->showType = $showInfo['showType'];
        $this->s1VenueId = $showInfo['venueId'];
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
     * Get Performances
     *
     *
     * @return ArrayCollection
     */
    public function getPerformances()
    {
        return $this->performances;
    }

    /**
     * Add performance
     *
     * @param S1Performances $performance
     * @return $this
     */
    public function addPerformance(S1Performances $performance)
    {
        $performance->setShow($this);
        $this->performances[] = $performance;
        return $this;
    }


    /**
     * Remove performance
     *
     * @param S1Performances $performance
     * @return $this
     */
    public function removePerformance(S1Performances $performance)
    {
        $this->performances->removeElement($performance);
        return $this;
    }

    /**
     * Removes all performances
     *
     * @return $this
     */
    public function flushPerformances(EntityManager $em)
    {
        foreach ($this->performances as $performance) {
            $this->removePerformance($performance);
            $em->remove($performance);
        }
        return $this;
    }

    /**
     * Set showName
     *
     * @param string $showName
     * @return S1Productions
     */
    public function setShowName($showName)
    {
        $this->showName = $showName;

        return $this;
    }

    /**
     * Get showName
     *
     * @return string 
     */
    public function getShowName()
    {
        return $this->showName;
    }

    /**
     * Set isEvent
     *
     * @param boolean $isEvent
     * @return S1Productions
     */
    public function setIsEvent($isEvent)
    {
        $this->isEvent = $isEvent;

        return $this;
    }

    /**
     * Get isEvent
     *
     * @return boolean 
     */
    public function getIsEvent()
    {
        return $this->isEvent;
    }

    /**
     * Set summary
     *
     * @param string $summary
     * @return S1Productions
     */
    public function setSummary($summary)
    {
        $this->summary = $summary;

        return $this;
    }

    /**
     * Get summary
     *
     * @return string 
     */
    public function getSummary()
    {
        return $this->summary;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return S1Productions
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set priceFrom
     *
     * @param string $priceFrom
     * @return S1Productions
     */
    public function setPriceFrom($priceFrom)
    {
        $this->priceFrom = $priceFrom;

        return $this;
    }

    /**
     * Get priceFrom
     *
     * @return string 
     */
    public function getPriceFrom()
    {
        return $this->priceFrom;
    }

    /**
     * Set priceTo
     *
     * @param string $priceTo
     * @return S1Productions
     */
    public function setPriceTo($priceTo)
    {
        $this->priceTo = $priceTo;

        return $this;
    }

    /**
     * Get priceTo
     *
     * @return string 
     */
    public function getPriceTo()
    {
        return $this->priceTo;
    }

    /**
     * Set ageRestriction
     *
     * @param string $ageRestriction
     * @return S1Productions
     */
    public function setAgeRestriction($ageRestriction)
    {
        $this->ageRestriction = $ageRestriction;

        return $this;
    }

    /**
     * Get ageRestriction
     *
     * @return string 
     */
    public function getAgeRestriction()
    {
        return $this->ageRestriction;
    }

    /**
     * Set limitedStock
     *
     * @param boolean $limitedStock
     * @return S1Productions
     */
    public function setLimitedStock($limitedStock)
    {
        $this->limitedStock = $limitedStock;

        return $this;
    }

    /**
     * Get limitedStock
     *
     * @return boolean 
     */
    public function getLimitedStock()
    {
        return $this->limitedStock;
    }

    /**
     * Set bookingStarts
     *
     * @param \DateTime $bookingStarts
     * @return S1Productions
     */
    public function setBookingStarts($bookingStarts)
    {
        $this->bookingStarts = $bookingStarts;

        return $this;
    }

    /**
     * Get bookingStarts
     *
     * @return \DateTime 
     */
    public function getBookingStarts()
    {
        return $this->bookingStarts;
    }

    /**
     * Set bookingEnds
     *
     * @param \DateTime $bookingEnds
     * @return S1Productions
     */
    public function setBookingEnds($bookingEnds)
    {
        $this->bookingEnds = $bookingEnds;

        return $this;
    }

    /**
     * Get bookingEnds
     *
     * @return \DateTime 
     */
    public function getBookingEnds()
    {
        return $this->bookingEnds;
    }

    /**
     * Set whitelabelURI
     *
     * @param string $whitelabelURI
     * @return S1Productions
     */
    public function setWhitelabelURI($whitelabelURI)
    {
        $this->whitelabelURI = $whitelabelURI;

        return $this;
    }

    /**
     * Get whitelabelURI
     *
     * @return string 
     */
    public function getWhitelabelURI()
    {
        return $this->whitelabelURI;
    }

    /**
     * Set bannerBackgroundColour
     *
     * @param string $bannerBackgroundColour
     * @return S1Productions
     */
    public function setBannerBackgroundColour($bannerBackgroundColour)
    {
        $this->bannerBackgroundColour = $bannerBackgroundColour;

        return $this;
    }

    /**
     * Get bannerBackgroundColour
     *
     * @return string 
     */
    public function getBannerBackgroundColour()
    {
        return $this->bannerBackgroundColour;
    }

    /**
     * Set resources
     *
     * @param array $resources
     * @return S1Productions
     */
    public function setResources($resources)
    {
        $this->resources = $resources;

        return $this;
    }

    /**
     * Get resources
     *
     * @return array 
     */
    public function getResources()
    {
        return $this->resources;
    }

    /**
     * Set categories
     *
     * @param array $categories
     * @return S1Productions
     */
    public function setCategories($categories)
    {
        $this->categories = $categories;

        return $this;
    }

    /**
     * Get categories
     *
     * @return array 
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * Set showType
     *
     * @param string $showType
     * @return S1Productions
     */
    public function setShowType($showType)
    {
        $this->showType = $showType;

        return $this;
    }

    /**
     * Get showType
     *
     * @return string 
     */
    public function getShowType()
    {
        return $this->showType;
    }

    /**
     * Set venue
     *
     * @param \SP\ImportBundle\Entity\S1Venues $venue
     * @return S1Productions
     */
    public function setSPVenue(\SP\ImportBundle\Entity\S1Venues $venue)
    {
        $this->spVenue = $venue;

        return $this;
    }

    /**
     * Get venue
     *
     * @return \SP\ImportBundle\Entity\S1Venues 
     */
    public function getSPVenue()
    {
        return $this->spVvenue;
    }

    /**
     * Set showId
     *
     * @param integer $showId
     * @return S1Productions
     */
    public function setShowId($showId)
    {
        $this->showId = $showId;

        return $this;
    }

    /**
     * Get showId
     *
     * @return integer 
     */
    public function getShowId()
    {
        return $this->showId;
    }

    /**
     * Set s1VenueId
     *
     * @param integer $s1VenueId
     * @return S1Productions
     */
    public function setS1VenueId($s1VenueId)
    {
        $this->s1VenueId = $s1VenueId;

        return $this;
    }

    /**
     * Get s1VenueId
     *
     * @return integer 
     */
    public function getS1VenueId()
    {
        return $this->s1VenueId;
    }

}
