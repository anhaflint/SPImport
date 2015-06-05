<?php

namespace SP\ImportBundle\Entity;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping as ORM;
use SP\ImportBundle\Event\ImportEvent;
use SP\ImportBundle\Event\ImportEvents;
use SP\ImportBundle\Event\ImportListener;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Supplier1Import
 *
 */
class Supplier1Import extends XMLImport
{
    private $em;

    private $name,
            $apiURL,
            $apiPwd,
            $apiUsr,
            $supplierId;

    public function __construct(EntityManager $em, $name, $url, $pwd, $usr, $id)
    {
        $this->em = $em;
        $this->name = $name;
        $this->apiURL = $url;
        $this->apiPwd = $pwd;
        $this->apiUsr = $usr;
        $this->supplierId = $id;
        $this->dispatcher = new EventDispatcher();
    }


    //============= Importer Methods ===============

    /**
     * Gets all venues or the venue requested
     * by the user from this supplier
     *
     * @param null $id
     * @return bool|mixed
     * @throws \Exception
     */
    public function getVenues( $id = null ) {
        //Venues Repository
        $venuesRepository = $this->em->getRepository('SPImportBundle:S1Venues');
        $this->dispatcher
             ->dispatch(
                 ImportEvents::IMPORT_EVENT,
                 new ImportEvent('Getting venues from ' . $this->getName() . ' supplier...')
             );

        //init curl multi handle and notify observers
        $mh = curl_multi_init();
        $this->dispatcher
             ->dispatch(
                 ImportEvents::IMPORT_EVENT,
                 new ImportEvent('Initiating connexion ')
             );

        //Get venue list
        $venues = $this->query($this->getUrl(), 'venue/', $this->optArray);

        //Generate Dom Document and XPath Document with venue list
        $domDocument    = new \DOMDocument();
        if( !($domDocument->loadXML($venues)) )
            throw new \Exception('Error : could not load all ' . $this->getName() .' venues XML feed');
        $xPathDocument  = new \DOMXPath($domDocument);

        //Notify observers
        $this->dispatcher
            ->dispatch(
                ImportEvents::IMPORT_EVENT,
                new ImportEvent('XML feed received from ' . $this->getName() . 'supplier')
            );

        //Get url list : if $id is null, get all venues, else select the url to load the data from
        if($id !== null && !is_integer($id)) {
            throw new \Exception('Error : The requested $id must be an integer');
        } elseif ( $id !== null && is_integer($id)) {
            $urlList = $xPathDocument->query('//venue[contains(@href, "' . $id . '")]/@href');
        } elseif ( $id === null ) {
            $urlList = $xPathDocument->query('//venue/@href');
        }

        // Error handling if there was a problem or the feed seems to be empty
        if( !$urlList ) {
            throw new \Exception('Error querying venues : invalid query');
        }  elseif ( $urlList->length == 0) {
            throw new \Exception('Warning : the feed you are asking  for seems to be empty, please check the feed source ');
        }

        //Add each link to multi handle to be requested asynchronously
        $this->dispatcher
            ->dispatch(
                ImportEvents::IMPORT_EVENT,
                new ImportEvent('Processing XML feed'
                ));
        $urlList = $this->DOMNodeListToArray($urlList);
        foreach ($urlList as $key => $url) {
           if($key <= 15) {
                //get url attributes to query
                $href = self::getOneVenueRequestUrl($url);

                //Init new curl handle for every link and add it to multi handle
                $mh = $this->addCurlHandle($mh, curl_init($this->apiURL . $href), $this->optArray);
           }
        }

        // Launch handles asynchronous execution
        do {
            while(($exec = curl_multi_exec($mh, $running)) == CURLM_CALL_MULTI_PERFORM);
            if($exec != CURLM_OK) {
                break;
            }
            while($ch = curl_multi_info_read($mh)) {
                $ch = $ch['handle'];

                //Get request response and Generate DOM & Xpath Documents
                $xmlContent = curl_multi_getcontent($ch);
                $domVenue   = new \DOMDocument();

                // Could not load the XML venue
                if(!($domVenue->loadXML($xmlContent)))
                    throw new \Exception('Error : Could not load ' . $this->getName() . '  venue');

                //Get venue and check if entry already exists in DB
                $xPathVenue = new \DOMXPath($domVenue);
                $venueInfo  = $this->fillVenue($xPathVenue);
                $this->dispatcher
                    ->dispatch(
                        ImportEvents::IMPORT_EVENT,
                        new ImportEvent('Processing one line of data for ' . $venueInfo['venueName'] )
                    );
                $venue      = $venuesRepository->findOneBy(array('venueId' => $venueInfo['venueId']));

                if($venue !== null) {
                    //Update the existing entry
                    $venue->update($venueInfo);
                    $this->dispatcher
                        ->dispatch(
                            ImportEvents::IMPORT_EVENT,
                            new ImportEvent('Updated one entry for venue : ' . $venueInfo['venueId'])
                        );
                } else {
                    //Create a new entry if it doesn't already exist
                    $venue = new S1Venues($venueInfo);
                    $this->dispatcher
                        ->dispatch(
                            ImportEvents::IMPORT_EVENT,
                            new ImportEvent('Created one entry for venue : ' . $venueInfo['venueId'])
                        );
                }

                //Flush DB and close curl handles
                $this->em->persist($venue);
                curl_multi_remove_handle($mh, $ch);
                curl_close($ch);
            }
        } while($running);

        $this->em->flush();
        curl_multi_close($mh);
        $this->dispatcher
             ->dispatch(
                 ImportEvents::IMPORT_EVENT,
                 new ImportEvent($this->getName() . ' Import over')
             );
        return true;
    }

    public function getProductions( $id = null ) {
        $venuesRepository   = $this->em->getRepository('SPImportBundle:S1Venues');
        $venuesId           = $venuesRepository->findAllIds();

        foreach($venuesId as $key => $venue) {
            $shows = self::getVenueProductions($venue['venueId']);
        }



        return $venuesId;
    }

    public function getVenueProductions($venueId)
    {
        $this->dispatcher
            ->dispatch(
                ImportEvents::IMPORT_EVENT,
                new ImportEvent('Getting productions from ' . $this->getName() . ' supplier for venue ' . $venueId)
            );
        $showRepository     = $this->em->getRepository('SPImportBundle:S1Productions');
        $venueRepository    = $this->em->getRepository('SPImportBundle:S1Venues');
        //Get venue and venue location
        $venue              = $venueRepository->findOneBy(array('venueId' => $venueId));
        $location           = ($venue !== null) ? $venue->getLocationId() : null ;

        //Create accesing url
        $href               = $location . '/' . $venueId;

        //multi handle init
        $mh = curl_multi_init();

        //Get show list page for this venue
        $shows = $this->query($this->getUrl(), 'venue/' . $href . '/show', $this->optArray);

        //Init DomDocument and XPathDocument
        $domDocument   = new \DOMDocument();
        if(!($domDocument->loadXML($shows)))
            throw new \Exception('Could not load shows XML feed');
        $xPathShows    = new \DOMXPath($domDocument);

        //Get productions url list
        $urlList = $this->getNode($xPathShows, '//show/@href');

        foreach($urlList as $url) {
            $href   = self::getProductionRequestUrl($url);
            $mh     = $this->addCurlHandle($mh, curl_init($this->apiURL . $href), $this->optArray);
        }

        do {
            while(($exec = curl_multi_exec($mh, $running)) == CURLM_CALL_MULTI_PERFORM);
            if($exec != CURLM_OK) {
                break;
            }
            while($ch = curl_multi_info_read($mh)) {
                $ch = $ch['handle'];

                //Get request response and Generate DOM & Xpath Documents
                $xmlContent = curl_multi_getcontent($ch);
                $domShow    = new \DOMDocument();
                if(!($domShow->loadXML($xmlContent)))
                    throw new \Exception('Error : Could not load ' . $this->getName() . '  show');

                //Get show and check if entry already exists in DB
                $xPathShow              = new \DOMXPath($domShow);
                $showInfo               = $this->fillShow($xPathShow);
                $showInfo['venueId']    = $venueId;
                $this->dispatcher
                     ->dispatch(
                         ImportEvents::IMPORT_EVENT,
                         new ImportEvent('Processing one line of data for venue ' . $venueId . ' - Production : ' . $showInfo['showName'] )
                     );

                $show = $showRepository->findOneBy(array('showId' => $showInfo['showId']));

                if($show !== null) {
                    //Update the existing entry
                    $show->update($showInfo);
                    $this->dispatcher
                        ->dispatch(
                            ImportEvents::IMPORT_EVENT,
                            new ImportEvent('Updated one line of data for production : " ' . $showInfo['showName'] . '"')
                        );
                } else {
                    //Create a new entry if it doesn't already exist
                    $show = new S1Productions($showInfo, $venue);
                    $this->dispatcher
                        ->dispatch(
                            ImportEvents::IMPORT_EVENT,
                            new ImportEvent('Created entry for venue ' . $venueId . ' - Production : ' . $showInfo['showName'] )
                        );
                }

                // Flush DB and close curl handles
                $this->em->persist($show);
                curl_multi_remove_handle($mh, $ch);
                curl_close($ch);
            }
        } while($running);

        $this->em->flush();
        $this->dispatcher
            ->dispatch(
                ImportEvents::IMPORT_EVENT,
                new ImportEvent('Finished processing data for venue ' . $venueId )
            );

        return true;
    }


    // ================================== Venues Utils =========================================
    /**
     * returns the request to append to the service url
     *
     * @param $url
     * @return string
     */
    private function getOneVenueRequestUrl($url)
    {
        $url = explode('/', $url);
        $venueId = end($url);
        $location = $url[sizeof($url) - 2];
        return 'venue/' . $location . '/' . $venueId;
    }

    /**
     * Fills an array with all venue information extracted from the XML feed
     *
     * @param $xPathVenue
     * @return array
     * @throws \Exception
     */
    private function fillVenue($xPathVenue)
    {
        $venue = array();

        //get venue name
        $venue['venueName']         = $this->getNode($xPathVenue, "//name");
        //get Location id
        $venue['locationId']        = $this->getNode($xPathVenue, "//location/@id");
        //get Address
        $venue['addressLine1']      = $this->getNode($xPathVenue, "//address/line1");
        $venue['addressLine2']      = $this->getNode($xPathVenue, "//address/line2");
        $venue['postCode']          = $this->getNode($xPathVenue, "//address/postcode");
        $venue['latitude']          = $this->getNode($xPathVenue, "//address/latitude");
        $venue['longitude']         = $this->getNode($xPathVenue, "//address/longitude");
        //Get resources
        $venue['resources']         = $this->getNode($xPathVenue, "//resources/resource/@uri");
        //Get Transport Info
        $venue['railStation']       = $this->getNode($xPathVenue, "//transportInfo/railStation");
        //Turn yes/no into boolean variables
        $venue['inCongestionZone']  = ($this->getNode($xPathVenue, "//transportInfo/inCongestionZone") !== null && $this->getNode($xPathVenue, "//transportInfo/inCongestionZone") === "yes") ? true : false;
        $venue['nearestTube']       = $this->getNode($xPathVenue, "//transportInfo/nearestTube");
        $venue['tubeDirection']     = $this->getNode($xPathVenue, "//transportInfo/tubeDirection");
        $venue['busRoutes']         = $this->getNode($xPathVenue, "//transportInfo/busRoutes");
        $venue['nightRoutes']       = $this->getNode($xPathVenue, "//transportInfo/nightRoutes");
        $venue['carPark']           = $this->getNode($xPathVenue, "//transportInfo/carPark");
        //Venue ID
        $venue['venueId']           = explode('/', $this->getNode($xPathVenue, '//venue/@href'));
        $venue['venueId']           = end($venue['venueId']);

        return $venue;
    }

    //======================================= Productions Utils =================================
    /**
     * Returns the url to append to API url to request a specific production
     *
     * @param $url
     * @return string
     */
    private function getProductionRequestUrl($url)
    {
        $href = explode('/', $url);
        return implode('/', array_slice($href, sizeof($href) - 3));
    }

    private function getShowId($url)
    {
        $url = explode('/', $url);
        return $url[sizeof($url) - 1];
    }

    private function fillShow($xPathShow)
    {
        $show = array();
        $show['showId']                 = self::getShowId($this->getNode($xPathShow, '//show/@href'));
        $show['showName']               = $this->getNode($xPathShow, '//name');
        $show['isEvent']                = ($this->getNode($xPathShow, '//isEvent') !== null && $this->getNode($xPathShow, '//isEvent') == 'yes') ? true : false;
        $show['summary']                = $this->getNode($xPathShow, '//summary');
        $show['description']            = $this->getNode($xPathShow, '//description');
        $show['priceFrom']              = $this->getNode($xPathShow, '//priceFrom');
        $show['priceTo']                = $this->getNode($xPathShow, '//priceTo');
        $show['ageRestriction']         = $this->getNode($xPathShow, '//ageRestriction');
        $show['limitedStock']           = ($this->getNode($xPathShow, '//limitedStock') !== null && $this->getNode($xPathShow, '//limitedStock') == 'yes') ? true : false;
        $show['bookingStarts']          = $this->getNode($xPathShow, '//show/bookingStarts');
        $show['bookingEnds']            = $this->getNode($xPathShow, '//show/bookingEnds');
        $show['whitelabelURI']          = $this->getNode($xPathShow, '//whitelabelURI');
        $show['bannerBackgroundColour'] = $this->getNode($xPathShow, '//bannerBackgroundColour');
        $show['resources']              = $this->getNode($xPathShow, '//resources/resource/@uri');
        $show['categories']             = $this->getNode($xPathShow, '//categories');
        $show['showType']               = $this->getNode($xPathShow, '//showType');

        return $show;
    }

    //======================================= Getters ===========================================
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


    /**
     * Returns the Event dispatcher used to display the service process
     *
     * @return EventDispatcher
     */
    public function getDispatcher()
    {
        return $this->dispatcher;
    }
}
