<?php

namespace SP\ImportBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use SP\ImportBundle\Event\ImportEvent;
use SP\ImportBundle\Event\ImportEvents;

/**
 * XMLImport
 *
 */
abstract class XMLImport implements SupplierImportInterface
{
    protected $dispatcher;

    protected $optArray = array(
        CURLOPT_AUTOREFERER => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => false
    );

    /**
     * Returns all venues for the supplier
     * Or just one if $id is defined
     *
     * @param $id
     * @return mixed
     */
    abstract public function getVenues( $id = null );


    /**
     * Returns all productions for the supplier
     * Or just one if $id is defined
     * @param $id
     * @return mixed
     */
    abstract public function getProductions( $id = null );

    /**
     * Requests the given url
     *
     * @param $url
     * @param $request
     * @param $options
     * @return mixed
     * @throws \Exception
     */
    protected function query($url, $request, $options) {
        $ch = curl_init();
        curl_setopt_array($ch, $options);
        curl_setopt($ch, CURLOPT_URL, $url . $request);

        $output = curl_exec($ch);
        $error = (( $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE) ) !== 200 && ( $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE) ) !== 204 ) ? true : false;

        if($httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE) === 204) {
            $this->dispatcher
                 ->dispatch(
                     ImportEvents::IMPORT_EVENT,
                     new ImportEvent('Warning : Ignoring one line of data for : ' . $url . $request, 'yellow')
                 );
        }

        // Throw Exception if http code is not 200
        if($error === true) {
            $errorMsg = 'Error loading feed : the request returned ' . $httpCode
                        . ' Using the url : ' . $url . $request;
            throw new \Exception($errorMsg);
        }

        return $output;
    }

    /**
     * Adds a curl handle to a curl multi handle
     *
     * @param $multiHandle
     * @param $curlHandle
     * @param array $options
     * @return mixed
     * @throws \Exception
     */
    protected function addCurlHandle($multiHandle, $curlHandle, Array $options)
    {
        curl_setopt_array($curlHandle, $options);
        $errorCode = curl_multi_add_handle($multiHandle, $curlHandle);

        //Error handling : curl_multi_handle return 0 if success
        $error = ($errorCode !== 0) ? true : false;
        if( $error === true ) {
            $errorMsg = 'Error loading curl handle. The process returned ' . $errorCode;
            throw new \Exception($errorMsg);
        }

        return $multiHandle;
    }

    /**
     * Turns a simple DOMNodeList into an array
     *
     * @param \DOMNodeList $list
     * @return array
     */
     protected function DOMNodeListToArray(\DOMNodeList $list)
    {
        $arrayList = array();
        foreach($list as $key => $value) {
            $arrayList[] = $value->textContent;
        }

        return $arrayList;
    }

    /**
     * Returns the content of the queried node
     *
     * @param \DOMXPath $xPath
     * @param $tagQuery
     * @return array|null|string
     * @throws \Exception
     */
    public function getNode(\DOMXPath $xPath, $tagQuery)
    {
        $query = $xPath->query($tagQuery);
        $error = ($query === false) ? true : false;
        $return = null;

        //Print directly if only one item
        if ( $query->length === 1 ) {
            $return = trim($query->item(0)->textContent);

            //Return array if more than one item
        } elseif ( $query->length > 1) {

            $return = array();
            foreach( $query as $domNode ) {
                array_push($return, trim($domNode->textContent));
            }
        }

        //Error handling if query failed
        if($error === true) {
            $errorMsg = 'There was a problem querying the XML feed using : ' . $tagQuery;
            throw new \Exception($errorMsg);
        }

        return $return;
    }
}
