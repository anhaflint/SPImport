<?php

namespace SP\ImportBundle\Services\XMLImport\Supplier1;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping as ORM;
use SP\ImportBundle\Event\ImportEvent;
use SP\ImportBundle\Event\ImportEvents;
use Symfony\Component\EventDispatcher\EventDispatcher;
use SP\ImportBundle\Services\XMLImport\XMLImport;

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
                 new ImportEvent('Getting venues from ' . $this->getName() . ' supplier...', 'black', 'white')
             );

        //init curl multi handle and notify observers
        $mh = curl_multi_init();
        $this->dispatcher
             ->dispatch(
                 ImportEvents::IMPORT_EVENT,
                 new ImportEvent('Initiating connexion ', 'white')
             );

        //Get venue list
        $venues = $this->query($this->getUrl(), 'venue/', $this->optArray);

        //Generate Dom Document and XPath Document with venue list
        $domDocument    = new \DOMDocument();
        if( !($domDocument->loadXML($venues)) ) {
            throw new \Exception('Error : could not load all ' . $this->getName() .' venues XML feed');
        }
        $xPathDocument  = new \DOMXPath($domDocument);

        //Notify observers
        $this->dispatcher
            ->dispatch(
                ImportEvents::IMPORT_EVENT,
                new ImportEvent('XML feed received from ' . $this->getName() . 'supplier', 'white')
            );

        //Get url list : if $id is null, get all venues, else select the url to load the data from
        if($id !== null && intval($id) === 0) {
            throw new \Exception('Error : The requested $id must be an integer');
        } elseif ( $id !== null && intval($id) > 0) {
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
                new ImportEvent('Processing XML feed', 'white'
                ));
        $urlList = $this->DOMNodeListToArray($urlList);

        //Add all urls to curl multi handle
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
            //Get the running boolean of the execution
            while(($exec = curl_multi_exec($mh, $running)) == CURLM_CALL_MULTI_PERFORM);
            if($exec != CURLM_OK) {
                break;
            }
            //Execute all urls of the multi handle
            while($ch = curl_multi_info_read($mh)) {
                $ch = $ch['handle'];

                //Get request response and Generate DOM & Xpath Documents
                $xmlContent = curl_multi_getcontent($ch);
                $domVenue   = new \DOMDocument();

                // Could not load the XML venue
                if(!($domVenue->loadXML($xmlContent)))
                    throw new \Exception('Error : Could not load ' . $this->getName() . '  venue');

                //Get XPathDocument and DOMDocument
                $xPathVenue = new \DOMXPath($domVenue);
                $venueInfo  = $this->fillVenue($xPathVenue);
                $this->dispatcher
                    ->dispatch(
                        ImportEvents::IMPORT_EVENT,
                        new ImportEvent('Processing one line of data for ' . $venueInfo['venueName'] , 'white')
                    );


                //Get venue and check if entry already exists in DB
                $venue = $venuesRepository->findOneBy(array('venueId' => $venueInfo['venueId']));
                if($venue !== null) {
                    //Update the existing entry
                    $venue->update($venueInfo);
                    $this->dispatcher
                        ->dispatch(
                            ImportEvents::IMPORT_EVENT,
                            new ImportEvent('Updated one entry for venue : ' . $venueInfo['venueId'], 'cyan')
                        );
                } else {
                    //Create a new entry if it doesn't already exist
                    $venue = new S1Venues($venueInfo);
                    $this->dispatcher
                        ->dispatch(
                            ImportEvents::IMPORT_EVENT,
                            new ImportEvent('Created one entry for venue : ' . $venueInfo['venueId'], 'cyan')
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
                 new ImportEvent( 'Finished importing ' . $this->getName() . ' venues ', 'green')
             );
        return true;
    }

    /**
     * Import productions form S1 supplier
     *
     * @param null $id
     * @return bool
     * @throws \Exception
     */
    public function getProductions( $id = null ) {
        $this->dispatcher
            ->dispatch(
                ImportEvents::IMPORT_EVENT,
                new ImportEvent('Starting productions import', 'black', 'white')
            );
        $venuesRepository   = $this->em->getRepository('SPImportBundle:S1Venues');
        $showRepository     = $this->em->getRepository('SPImportBundle:S1Productions');

        //Init curl mutli handle and query the shows list
        $mh = curl_multi_init();
        $shows = $this->query($this->getUrl(), 'show', $this->optArray);

        //Create DOMDocument and XPathDocument for the shows page
        $domDocument   = new \DOMDocument();
        if (!($domDocument->loadXML($shows))) {
            libxml_clear_errors();
            throw new \Exception('Error : Could not load shows XML feed');
        }
        $xPathShows = new \DOMXPath($domDocument);

        //Get productions url list
        if($id === null) {
            $urlList = $this->getNode($xPathShows, '/shows/show/@href');
        } else {
            $urlList = $this->getNode($xPathShows, '/shows/show[contains(@href, "' . $id . '")]/@href');
        }

        //Make url a list if it is the only show
        if(is_string($urlList)) $urlList = array($urlList);
        //Add each show url to the curl multi handle
        foreach ($urlList as $url) {
            $href   = self::getProductionRequestUrl($url);
            $mh     = $this->addCurlHandle($mh, curl_init($this->apiURL . $href), $this->optArray);
        }

        do {
            while ( ($exec = curl_multi_exec($mh, $running) ) == CURLM_CALL_MULTI_PERFORM) ;
            if ($exec != CURLM_OK) {
                break;
            }
            while ($ch = curl_multi_info_read($mh)) {
                $ch = $ch['handle'];

                //Get request response and Generate DOM & Xpath Documents
                $xmlContent = curl_multi_getcontent($ch);
                $domShow    = new \DOMDocument();
                if (!($domShow->loadXML($xmlContent)))
                    throw new \Exception('Error : Could not load ' . $this->getName() . '  show');
                $xPathShow  = new \DOMXPath($domShow);

                //Getting information for the show
                $showInfo   = $this->fillShow($xPathShow);


                //Get venue list for this show
                $venueList  = $this->getNode($xPathShow, '/show/venues/venue/@href');
                if(is_string($venueList)) $venueList = array($venueList);

                //Check if show already exists in DB
                $show = $showRepository->findOneBy(array('showId' => $showInfo['showId']));
                foreach($venueList as $key => $venueUrl) {
                    $venue = $venuesRepository->findOneBy(array('venueId' => self::getId($venueUrl)));
                    $showInfo['venueId'] = self::getId($venueUrl);

                    //Check if the listed venue exists in DB
                    if($venue !== null) {
                        $this->dispatcher
                            ->dispatch(
                                ImportEvents::IMPORT_EVENT,
                                new ImportEvent('Processing one line of data for Production : ' . $showInfo['showName'], 'white')
                            );
                        if ($show !== null) {
                            //If show already exists in DB, update it with new information
                            $show->update($showInfo);
                            $this->dispatcher
                                ->dispatch(
                                    ImportEvents::IMPORT_EVENT,
                                    new ImportEvent('Updated one line of data for production : " ' . $showInfo['showName'] . '"', 'cyan')
                                );
                        } else {
                            //Create a new entry if it doesn't already exist
                            $show = new S1Productions($showInfo, $venue);
                            $this->dispatcher
                                ->dispatch(
                                    ImportEvents::IMPORT_EVENT,
                                    new ImportEvent('Created entry for venue ' . self::getId($venueUrl) , 'cyan')
                                );
                        }

                        // Persist entities
                        $this->em->persist($show);
                    } else {
//                        $this->dispatcher
//                            ->dispatch(
//                                ImportEvents::IMPORT_EVENT,
//                                new ImportEvent('Warning : Could not find venue ' . $showInfo['venueId'] . ' - Production : ' . $showInfo['showName'], 'cyan')
//                            );
                    }
                }
                curl_multi_remove_handle($mh, $ch);
                curl_close($ch);
            }
        } while ($running);

        $this->em->flush();
        curl_multi_close($mh);
        $this->dispatcher
            ->dispatch(
                ImportEvents::IMPORT_EVENT,
                new ImportEvent('Finished processing data for productions', 'green')
            );

        return true;
    }

    /**
     * Import performances from S1 Supplier
     *
     * @return bool
     * @throws \Exception
     */
    public function getPerformances()
    {
        $this->dispatcher
            ->dispatch(
                ImportEvents::IMPORT_EVENT,
                new ImportEvent('Starting performances import', 'black', 'white')
            );
        $venueRepository    = $this->em->getRepository('SPImportBundle:S1Venues');
        $showRepository     = $this->em->getRepository('SPImportBundle:S1Productions');

        //Get venues that are actually used from the productions list
        $venueList = $showRepository->getDistinctVenueId();

        //Get locations id for these venues
        foreach ($venueList as $key => $venueArray) {
            $venue = $venueRepository->findOneBy(array('venueId' => $venueArray['s1VenueId']));
            $venueList[$key]['locationId'] = $venue->getLocationId();
        }


        //init curl multi handle and add the urls
        $mh = curl_multi_init();
        foreach($venueList as $key => $venue) {
            $href   = self::getVenueShowUrl($venue['s1VenueId'], $venue['locationId']);
            $mh     = $this->addCurlHandle($mh, curl_init($this->apiURL . $href), $this->optArray);
        }

        do {
            while ( ($exec = curl_multi_exec($mh, $running) ) == CURLM_CALL_MULTI_PERFORM) ;
            if ($exec != CURLM_OK) {
                break;
            }
            while ($ch = curl_multi_info_read($mh)) {
                $ch = $ch['handle'];

                //Get request response and Generate DOM & Xpath Documents
                $xmlContent     = curl_multi_getcontent($ch);
                $domVenue       = new \DOMDocument();
                if (!($domVenue->loadXML($xmlContent)))
                    throw new \Exception('Error : Could not load ' . $this->getName() . '  show');
                $xmlVenue = simplexml_import_dom($domVenue);

                //Get show list ids for this venue
                $showListId = $xmlVenue->xpath('/venue/show/@href');
                $showListId = (is_string($showListId)) ? array($showListId) : $showListId;
                $showListId = array_map("self::getId", $showListId);

                //Get venue id
                $venueId = $xmlVenue->xpath('/venue/@href');
                $venueId = self::getId((string) $venueId[0]['href']);

                //Get performances for each show in this venue
                foreach ($showListId as $showId) {
                    //Get existing show in DB and flush all its performances
                    $spShow = $showRepository->findOneBy(array('showId' => $showId ));
                    if($spShow !== null) {
                        $spShow->flushPerformances($this->em);
                    }

                    //Get all performances for this show
                    $showPerformances   = $xmlVenue->xpath('/venue/show[contains(@href, "' . $showId . '")]/performances');
                    $showPerformances   = $showPerformances[0];

                    //Get the count of performances for this show
                    $performanceCount  = sizeof($showPerformances);

                    //Get all the performances into the database
                    foreach($showPerformances as $performance) {
                        //Store performance in array temporarily
                        $performanceArray = array(
                            's1ShowId'  => $showId,
                            'date'      => (string) $performance['date'] ,
                            'time'      => (string) $performance['time'] ,
                            'type'      => (string) $performance['type']
                        );
                        $newPerformance = new S1Performances($performanceArray);
                        $spShow->addPerformance($newPerformance);
                        $this->em->persist($newPerformance);
                    }
                    $this->dispatcher
                        ->dispatch(
                            ImportEvents::IMPORT_EVENT,
                            new ImportEvent('Created ' . $performanceCount . ' performances for show ID ' . $showId, 'cyan')
                        );
                }

                $this->dispatcher
                    ->dispatch(
                        ImportEvents::IMPORT_EVENT,
                        new ImportEvent('Finished performance import for venue ' . $venueId, 'green')
                    );
                $this->em->flush();
                curl_multi_remove_handle($mh, $ch);
                curl_close($ch);
            }
        } while ($running);

        try {
            $this->em->flush();
            $this->dispatcher
                ->dispatch(
                    ImportEvents::IMPORT_EVENT,
                    new ImportEvent('Finished performance import', 'green')
                );
        } catch(\Exception $e) {
            $this->dispatcher
                ->dispatch(
                    ImportEvents::IMPORT_EVENT,
                    new ImportEvent('Error : an error occured during performances import : ' . $e->getMessage(), 'red')
                );
            return false;
        }
        curl_multi_close($mh);
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
     * Returns the url to append to the base url
     * to request all shows for a specific venue
     *
     * @param $venueId
     * @param $venueLocation
     * @return string
     * @throws \Exception
     */
    private function getVenueShowUrl($venueId, $venueLocation)
    {
        if($venueId !== null && $venueLocation !== null)
        {
            return 'venue/' . $venueLocation . '/' . $venueId . '/show';
        } else {
            throw new \Exception('Error : no venue id or location id was given');
        }
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

    /**
     * Returns the id contained in a url
     *
     * @param $url
     * @return mixed
     */
    private function getId($url)
    {
        $url = explode('/', $url);
        return $url[sizeof($url) - 1];
    }

    /**
     * Returns an array containing information about the production
     *
     * @param $xPathShow
     * @return array
     * @throws \Exception
     */
    private function fillShow($xPathShow)
    {
        $show = array();

        $show['showId']                 = self::getId($this->getNode($xPathShow, '/show/@href'));
        $show['showName']               = $this->getNode($xPathShow, '/show/name');
        $show['isEvent']                = ($this->getNode($xPathShow, '/show/isEvent') !== null && $this->getNode($xPathShow, '/show/isEvent') == 'yes') ? true : false;
        $show['summary']                = $this->getNode($xPathShow, '/show/summary');
        $show['description']            = $this->getNode($xPathShow, '/show/description');
        $show['priceFrom']              = $this->getNode($xPathShow, '/show/priceFrom');
        $show['priceTo']                = $this->getNode($xPathShow, '/show/priceTo');
        $show['ageRestriction']         = $this->getNode($xPathShow, '/show/ageRestriction');
        $show['limitedStock']           = ($this->getNode($xPathShow, '/show/limitedStock') !== null && $this->getNode($xPathShow, '//limitedStock') == 'yes') ? true : false;
        $show['bookingStarts']          = $this->getNode($xPathShow, '/show/bookingStarts');
        $show['bookingEnds']            = $this->getNode($xPathShow, '/show/bookingEnds');
        $show['whitelabelURI']          = $this->getNode($xPathShow, '/show/whitelabelURI');
        $show['bannerBackgroundColour'] = $this->getNode($xPathShow, '/show/bannerBackgroundColour');
        $show['resources']              = $this->getNode($xPathShow, '/show/resources/resource/@uri');
        $show['categories']             = $this->getNode($xPathShow, '/show/categories');
        $show['showType']               = $this->getNode($xPathShow, '/show/showType');

        return $show;
    }

    //======================================= Performances utils ================================

    private function fillPerformance(\DOMElement $DOMperformance, $showId)
    {
        $performance = array();

        $performance['showId'] = $showId;
        $performance['type'] = $DOMperformance->getAttribute('type');
        $performance['date'] = $DOMperformance->getAttribute('date');
        $performance['time'] = $DOMperformance->getAttribute('time');

        return $performance;
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
