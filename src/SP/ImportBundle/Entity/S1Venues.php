<?php

namespace SP\ImportBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * S1Venues
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="SP\ImportBundle\Entity\S1VenuesRepository")
 */
class S1Venues
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
     * @ORM\Column(name="venueId", type="integer", nullable=true)
     */
    private $venueId;

    /**
     * @var string
     *
     * @ORM\Column(name="venueName", type="string", length=255, nullable=true)
     */
    private $venueName;

    /**
     * @var string
     *
     * @ORM\Column(name="locationId", type="string", length=255, nullable=true)
     */
    private $locationId;

    /**
     * @var string
     *
     * @ORM\Column(name="addressLine1", type="string", length=255, nullable=true)
     */
    private $addressLine1;

    /**
     * @var string
     *
     * @ORM\Column(name="addressLine2", type="string", length=255, nullable=true)
     */
    private $addressLine2;

    /**
     * @var string
     *
     * @ORM\Column(name="postCode", type="string", length=255, nullable=true)
     */
    private $postCode;

    /**
     * @var string
     *
     * @ORM\Column(name="latitude", type="decimal", precision=16, scale=10, nullable=true)
     */
    private $latitude;

    /**
     * @var string
     *
     * @ORM\Column(name="longitude", type="decimal", precision=16, scale=10, nullable=true)
     */
    private $longitude;

    /**
     * @var array
     *
     * @ORM\Column(name="resources", type="array", nullable=true)
     */
    private $resources;

    /**
     * @var string
     *
     * @ORM\Column(name="nearestTube", type="string", length=255, nullable=true)
     */
    private $nearestTube;

    /**
     * @var string
     *
     * @ORM\Column(name="tubeDirection", type="string", length=255, nullable=true)
     */
    private $tubeDirection;

    /**
     * @var string
     *
     * @ORM\Column(name="railStation", type="string", length=255, nullable=true)
     */
    private $railStation;

    /**
     * @var string
     *
     * @ORM\Column(name="busRoutes", type="string", length=255, nullable=true)
     */
    private $busRoutes;

    /**
     * @var string
     *
     * @ORM\Column(name="nightRoutes", type="string", length=255, nullable=true)
     */
    private $nightRoutes;

    /**
     * @var string
     *
     * @ORM\Column(name="carPark", type="string", length=255, nullable=true)
     */
    private $carPark;

    /**
     * @var boolean
     *
     * @ORM\Column(name="inCongestionZone", type="boolean", nullable=true)
     */
    private $inCongestionZone;

    public function __construct(array $venue) {
        self::update($venue);
    }

    public function update(array $venue) {
        $this->venueId = $venue['venueId'];
        $this->venueName = $venue['venueName'];
        $this->locationId = $venue['locationId'];
        $this->addressLine1 = $venue['addressLine1'];
        $this->addressLine2 = $venue['addressLine2'];
        $this->postCode = $venue['postCode'];
        $this->latitude = $venue['latitude'];
        $this->longitude = $venue['longitude'];
        $this->railStation = $venue['railStation'];
        $this->inCongestionZone = $venue['inCongestionZone'];
        $this->resources = $venue['resources'];
        $this->nearestTube = $venue['nearestTube'];
        $this->tubeDirection = $venue['tubeDirection'];
        $this->busRoutes = $venue['busRoutes'];
        $this->nightRoutes = $venue['nightRoutes'];
        $this->carPark = $venue['carPark'];
        if(!is_array($venue['resources']) && $venue['resources'] !== null) {
            $this->resources = array($venue['resources']);
        } elseif (is_array($venue['resources'])) {
            $this->resources = $venue['resources'];
        }
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
     * Set venueId
     *
     * @param integer $venueId
     * @return S1Venues
     */
    public function setVenueId($venueId)
    {
        $this->venueId = $venueId;

        return $this;
    }

    /**
     * Get venueId
     *
     * @return integer
     */
    public function getVenueId()
    {
        return $this->venueId;
    }

    /**
     * Set venueName
     *
     * @param string $venueName
     * @return S1Venues
     */
    public function setVenueName($venueName)
    {
        $this->venueName = $venueName;

        return $this;
    }

    /**
     * Get venueName
     *
     * @return string 
     */
    public function getVenueName()
    {
        return $this->venueName;
    }

    /**
     * Set locationId
     *
     * @param string $locationId
     * @return S1Venues
     */
    public function setLocationId($locationId)
    {
        $this->locationId = $locationId;

        return $this;
    }

    /**
     * Get locationId
     *
     * @return string 
     */
    public function getLocationId()
    {
        return $this->locationId;
    }

    /**
     * Set addressLine1
     *
     * @param string $addressLine1
     * @return S1Venues
     */
    public function setAddressLine1($addressLine1)
    {
        $this->addressLine1 = $addressLine1;

        return $this;
    }

    /**
     * Get addressLine1
     *
     * @return string 
     */
    public function getAddressLine1()
    {
        return $this->addressLine1;
    }

    /**
     * Set addressLine2
     *
     * @param string $addressLine2
     * @return S1Venues
     */
    public function setAddressLine2($addressLine2)
    {
        $this->addressLine2 = $addressLine2;

        return $this;
    }

    /**
     * Get addressLine2
     *
     * @return string 
     */
    public function getAddressLine2()
    {
        return $this->addressLine2;
    }

    /**
     * Set postCode
     *
     * @param string $postCode
     * @return S1Venues
     */
    public function setPostCode($postCode)
    {
        $this->postCode = $postCode;

        return $this;
    }

    /**
     * Get postCode
     *
     * @return string 
     */
    public function getPostCode()
    {
        return $this->postCode;
    }

    /**
     * Set latitude
     *
     * @param string $latitude
     * @return S1Venues
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * Get latitude
     *
     * @return string 
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Set longitude
     *
     * @param string $longitude
     * @return S1Venues
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * Get longitude
     *
     * @return string 
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Set resource
     *
     * @param array $resource
     * @return S1Venues
     */
    public function setResource($resource)
    {
        $this->resource = $resource;

        return $this;
    }

    /**
     * Get resource
     *
     * @return array 
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Set railStation
     *
     * @param string $railStation
     * @return S1Venues
     */
    public function setRailStation($railStation)
    {
        $this->railStation = $railStation;

        return $this;
    }

    /**
     * Get railStation
     *
     * @return string 
     */
    public function getRailStation()
    {
        return $this->railStation;
    }

    /**
     * Set inCongestionZone
     *
     * @param boolean $inCongestionZone
     * @return S1Venues
     */
    public function setInCongestionZone($inCongestionZone)
    {
        $this->inCongestionZone = $inCongestionZone;

        return $this;
    }

    /**
     * Get inCongestionZone
     *
     * @return boolean 
     */
    public function getInCongestionZone()
    {
        return $this->inCongestionZone;
    }

    /**
     * Set resources
     *
     * @param array $resources
     * @return S1Venues
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
     * Set nearestTube
     *
     * @param string $nearestTube
     * @return S1Venues
     */
    public function setNearestTube($nearestTube)
    {
        $this->nearestTube = $nearestTube;

        return $this;
    }

    /**
     * Get nearestTube
     *
     * @return string 
     */
    public function getNearestTube()
    {
        return $this->nearestTube;
    }

    /**
     * Set tubeDirection
     *
     * @param string $tubeDirection
     * @return S1Venues
     */
    public function setTubeDirection($tubeDirection)
    {
        $this->tubeDirection = $tubeDirection;

        return $this;
    }

    /**
     * Get tubeDirection
     *
     * @return string 
     */
    public function getTubeDirection()
    {
        return $this->tubeDirection;
    }

    /**
     * Set busRoutes
     *
     * @param string $busRoutes
     * @return S1Venues
     */
    public function setBusRoutes($busRoutes)
    {
        $this->busRoutes = $busRoutes;

        return $this;
    }

    /**
     * Get busRoutes
     *
     * @return string 
     */
    public function getBusRoutes()
    {
        return $this->busRoutes;
    }

    /**
     * Set nightRoutes
     *
     * @param string $nightRoutes
     * @return S1Venues
     */
    public function setNightRoutes($nightRoutes)
    {
        $this->nightRoutes = $nightRoutes;

        return $this;
    }

    /**
     * Get nightRoutes
     *
     * @return string 
     */
    public function getNightRoutes()
    {
        return $this->nightRoutes;
    }

    /**
     * Set carPark
     *
     * @param string $carPark
     * @return S1Venues
     */
    public function setCarPark($carPark)
    {
        $this->carPark = $carPark;

        return $this;
    }

    /**
     * Get carPark
     *
     * @return string 
     */
    public function getCarPark()
    {
        return $this->carPark;
    }
}
