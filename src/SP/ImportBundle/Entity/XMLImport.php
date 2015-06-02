<?php

namespace SP\ImportBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * XMLImport
 *
 */
abstract class XMLImport implements SupplierImportInterface
{

    /**
     * Returns all venues for the supplier
     *
     * @return mixed
     */
    abstract public function getAllVenues();


    /**
     * Returns all productions for the supplier
     *
     * @return mixed
     */
    abstract public function getAllProductions();

    /**
     * Requests the given url
     *
     * @param $url
     * @param $request
     * @return mixed
     */
    public function query($url, $request, $options) {
        $ch = curl_init();
        curl_setopt_array($ch, $options);
        curl_setopt($ch, CURLOPT_URL, $url . $request);

        $output = curl_exec($ch);

        return $output;
    }

    /**
     * Adds a curl handle to a curl multi handle
     *
     * @param $multiHandle
     * @param $curlHandle
     * @param array $options
     * @return mixed
     */
    public function addCurlHandle($multiHandle, $curlHandle, Array $options)
    {
        curl_setopt_array($curlHandle, $options);
        curl_multi_add_handle($multiHandle, $curlHandle);

        return $multiHandle;
    }

    /**
     * Returns the content of the queried node
     *
     * @param \DOMXPath $xPath
     * @param $tagQuery
     * @return array|null|string
     */
    public function getNode(\DOMXPath $xPath, $tagQuery)
    {
        $query = $xPath->query($tagQuery);
        $return = null;
        if ( $query->length === 1 ) {
            $return = $query->item(0)->textContent;
        } elseif ( $query->length > 1) {
            $return = array();
            foreach( $query as $domNode ) {
                array_push($return, $domNode->textContent);
            }
        }

        return $return;
    }
}
