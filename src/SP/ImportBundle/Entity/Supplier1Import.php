<?php

namespace SP\ImportBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Supplier1Import
 *
 */
class Supplier1Import extends XMLImport
{

    private $name,
            $apiURL,
            $apiPwd,
            $apiUsr,
            $supplierId;

    public function __construct($name, $url, $pwd, $usr, $id)
    {
        $this->name = $name;
        $this->apiURL = $url;
        $this->apiPwd = $pwd;
        $this->apiUsr = $usr;
        $this->supplierId = $id;
    }


    //============= Importer Methods ===============

    public function getVenues( $id = null ) {
        //init curl multi handle
        $mh = curl_multi_init();
        $optArray = array(
            CURLOPT_AUTOREFERER => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false
        );

        //Get venue list
        $venues = $this->query($this->getUrl(), 'venue/', $optArray);


        //Generate Dom Document with venue list
        $domDocument = new \DOMDocument();
        $error = !($domDocument->loadXML($venues));
        // Could not load all the XML venues
        if($error === true) {
            throw new \Exception('Error : could not load all ' . $this->getName() .' venues XML feed');
        }

        $xPathDocument = new \DOMXPath($domDocument);

        //Get url list
        if($id !== null && !is_integer($id)) {
            throw new \Exception('Error : The requested $id must be an integer');
        } elseif ( $id !== null && is_integer($id)) {
            $urlList = $xPathDocument->query('//venue[contains(@href, "' . $id . '")]/@href');
        } elseif ( $id === null ) {
            $urlList = $xPathDocument->query('//venue/@href');
        }

        if( !$urlList ) {
            throw new \Exception('Error querying venues : invalid query');
        }

        //Add each link to multi handle to be requested asynchronously
        foreach ($urlList as $key => $url) {
            //get url attributes to query
            $href = self::getOneVenueRequestUrl($url->textContent);

            //Init new curl handle for every link and add it to multi handle
            $mh = $this->addCurlHandle($mh, curl_init($this->apiURL . $href), $optArray);
        }

        $results = array();

        do {
            // Launch handles asynchronous execution
            while(($exec = curl_multi_exec($mh, $running)) == CURLM_CALL_MULTI_PERFORM);
            if($exec != CURLM_OK) {
                break;
            }
            while($ch = curl_multi_info_read($mh)) {
                $ch = $ch['handle'];
                //Get request response
                $xmlContent = curl_multi_getcontent($ch);

                //Response to DOMDocument
                $domVenue = new \DOMDocument();
                $error = !($domVenue->loadXML($xmlContent));

                // Could not load the XML venue
                if($error === true) {
                    throw new \Exception('Error : Could not load ' . $this->getName() . '  venue');
                }
                $xPathVenue = new \DOMXPath($domVenue);

                //get venue name
                $venueName = $this->getNode($xPathVenue, "//name");

                //get Location id
                $locationId = $this->getNode($xPathVenue, "//location/@id");

                //get Address
                $addressLine1 = $this->getNode($xPathVenue, "//address/line1");
                $addressLine2 = $this->getNode($xPathVenue, "//address/line2");
                $postCode = $this->getNode($xPathVenue, "//address/postcode");
                $latitude = $this->getNode($xPathVenue, "//address/latitude");
                $longitude = $this->getNode($xPathVenue, "//address/longitude");

                //Get resources
                $resource = $this->getNode($xPathVenue, "//resources/resource/@uri");

                //Get Transport Info
                $railStation = $this->getNode($xPathVenue, "//transportInfo/railStation");
                $congestion = ($this->getNode($xPathVenue, "//transportInfo/inCongestionZone") !== null && $this->getNode($xPathVenue, "//transportInfo/inCongestionZone") === "yes") ? true : false;

                //Venue ID
                $venueId = explode('/', $this->getNode($xPathVenue, '//venue/@href'));
                $venueId = end($venueId);

                $results[] = array(
                    $venueId,
                    $venueName,
                    $locationId,
                    $addressLine1,
                    $addressLine2,
                    $postCode,
                    $latitude,
                    $longitude,
                    $resource,
                    $railStation,
                    $congestion);

                curl_multi_remove_handle($mh, $ch);
                curl_close($ch);
            }
        } while($running);

        curl_multi_close($mh);

       return $results;

    }

    public function getProductions( $id = null ) {
        echo '<p>productions</p>';
    }

    // ============ Utils ===================
    /**
     * returns the request to append to the service url
     *
     * @param $url
     * @return string
     */
    public function getOneVenueRequestUrl($url)
    {
        $url = explode('/', $url);
        $venueId = end($url);
        $location = $url[sizeof($url) - 2];
        return 'venue/' . $location . '/' . $venueId;
    }


    //============= Getters =================
    /**
     * Get supplierId
     *
     * @return integer 
     */
    public function getSupplierId()
    {
        return $this->supplierId;
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
     * Get url
     *
     * @return string 
     */
    public function getUrl()
    {
        return $this->apiURL;
    }

    /**
     * Get API password
     *
     * @return mixed
     */
    protected function getPwd()
    {
        return $this->apiPwd;
    }

    /**
     * Get API User
     *
     * @return mixed
     */
    protected function getUsr()
    {
        return $this->apiUsr;
    }
}
