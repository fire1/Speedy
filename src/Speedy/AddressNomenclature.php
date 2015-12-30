<?php
/**
 * Created by PhpStorm.
 * Author: Angel Zaprianov <me@fire1.eu>
 * Date: 12/18/15
 * Time: 8:56 AM
 */

namespace Fire1\Speedy;

/**
 * Class AddressNomenclature
 * @package Speedy
 */
class AddressNomenclature
{

    protected $conn;
    protected $facade = null;
    protected $nomen;
    /**
     * @var \AddrNomen
     */
    protected $_street;

    /**
     * @param ConnectionSpeedyApi $connectionEpsFacade
     */
    public function __construct(ConnectionSpeedyApi $connectionEpsFacade)
    {
        $this->conn = $connectionEpsFacade;
        $this->facade = $connectionEpsFacade->getEpsFacade();
        $this->conn->includeClass('AddrNomen.class');
    }

    /**
     * @param null $name
     * @param null $postcode
     * @return array
     */
    public function getCities($name = null, $postcode = null)
    {
        $cities = array();
        if (isset($this->resultLogin)) {

            if ($postcode) {
                require_once((__DIR__) . '/lib/speedy-eps-lib/ver01/ParamFilterSite.class.php');

                $paramFilterSite = new \ParamFilterSite();
                $paramFilterSite->setPostCode($postcode);
                $paramFilterSite->setName($name);
                $listSitesEx = $this->facade->listSitesEx($paramFilterSite);
                $listSites = array();

                /* @var \ResultSiteEx $result */
                foreach ($listSitesEx as $result) {
                    if ($result->isExactMatch()) {
                        $listSites[] = $result->getSite();
                    }
                }
            } else {
                $listSites = $this->facade->listSites(null, $name);
            }

            if ($listSites) {
                /* @var \ResultSite $city */
                foreach ($listSites as $city) {
                    $cities[] = array(
                        'id' => $city->getId(),
                        'label' => $city->getType() . ' ' . $city->getName() . ' (' . $city->getPostCode() . '), общ. ' . $city->getMunicipality() . ', обл. ' . $city->getRegion(),
                        'value' => $city->getType() . ' ' . $city->getName() . ' (' . $city->getPostCode() . '), общ. ' . $city->getMunicipality() . ', обл. ' . $city->getRegion(),
                        'postcode' => $city->getPostCode(),
                        'nomenclature' => $city->getAddrNomen()->getValue()
                    );
                }
            }

        }

        return $cities;
    }


    /** Sets street name
     * @param string $street_name
     */
    protected function setAddress($street_name)
    {
        $this->_street = new \AddrNomen($street_name);
        $this->facade->getSitesByAddrNomenType($this->_street);
    }


    public function getAddressNomenclature()
    {
        $this->facade->getAddressNomenclature($nomen_type);
    }

}