<?php
/*
* Copyright (C) 2015 Angel Zaprianov <me@fire1.eu>
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
* Project: FireShop
*
* Date: 9/19/2015
* Time: 17:27
*
* @author Angel Zaprianov <me@fire1.eu>
*/

namespace Fire1\Speedy;

/**
 * Class ShippingWrapperHelper
 *
 * Speedy API Version: 2.8.0
 * Release date: 6 Oct 2015
 *
 * @data 24 Dec 2015
 * @see https://www.speedy.bg/eps/docs/eps-lib-php.html#ahn-h-02
 * @version 0.4
 * @author <me@fire1.eu> Angel Zaprianov
 * @package Speedy
 */
class ShippingWrapperHelper implements ShippingWrapperInterface
{
    /** Error info
     * @var null
     */
    public $error = null;
    /** Error status
     * @var bool
     */
    protected $_error = false;
    /**
     * @var int 0
     */
    protected $package_count = 0;
    /**
     * @var \ParamAddress
     */
    protected $_address;
    /**
     * @var \ParamClientData
     */
    protected $_receiver;
    /**
     * @var \ParamPicking
     */
    protected $_picking;
    /**
     * @var \ParamClientData
     */
    protected $_sender;

    protected $addEx;
    /**
     * @var \EPSFacade
     */
    protected $eps;
    /**
     * @var ConnectionSpeedyApi
     */
    protected $conApi;

    /**
     * @var integer
     */
    protected $working_days = 0;

    /** Detect fragile for Insurance
     * @var array
     */
    protected $_fragile = array();


    /**
     * @param \EPSFacade $eps_facade
     * @param ConnectionSpeedyApi $cApi
     */
    public function __construct(\EPSFacade $eps_facade, ConnectionSpeedyApi $cApi)
    {
        $this->conApi = $cApi;
        $this->eps = $eps_facade;
    }

    /**
     * @return \date
     */
    public function getDate()
    {
//        return date("Y-m-d");
        return time();
    }

    /**
     * initialization Client & Receiver
     */
    protected function initReceiverAndSender()
    {
        $this->_picking = new \ParamPicking();
        $this->_receiver = new \ParamClientData();
    }

    /** Sets working days
     * @param string $strData
     */
    public function setWorkingDays($strData)
    {
        $this->working_days = strtotime($strData);
    }

    /**
     * @return int
     */
    public function getWorkingDays()
    {
        return $this->working_days;
    }

    /**
     * @return array
     */
    public function getServices()
    {
        $services = array();
        $time = time();
        $listServices = $this->eps->listServices($this->getDate());
        if ($listServices) {
            /* @var \ResultCourierService $service */
            foreach ($listServices as $service) {
                if ($service->getTypeId() == 26 || $service->getTypeId() == 36) {
                    continue;
                }
                $services[$service->getTypeId()] = $service->getName();
            }
        }
        return $services;
    }

    /**
     * @param \ResultSite $city
     * @return string
     */
    private function getCityFormat(\ResultSite $city)
    {
        return $city->getType() . ' ' . $city->getName() . ' (' . $city->getPostCode() . '), общ. ' . $city->getMunicipality() . ', обл. ' . $city->getRegion();
    }

    /**
     * @param \ResultSite $city
     * @return mixed
     */
    protected function getCityNomenclature(\ResultSite $city)
    {

        return $city->getAddrNomen()->getValue();
    }

    /**
     * @param \ParamFilterSite $paramFilterSite
     * @return array
     */
    protected function getListedSites(\ParamFilterSite $paramFilterSite)
    {
        $listSitesEx = $this->eps->listSitesEx($paramFilterSite);
        $listSites = array();

        /* @var \ResultSiteEx $result */
        foreach ($listSitesEx as $result) {
            if ($result->isExactMatch()) {
                $listSites[] = $result->getSite();
            }
        }
        return $listSites;
    }

    /** Gets service from given sites ID
     * @param integer $fromSiteId
     * @param integer $toSiteId
     * @return array
     */
    protected function getServiceForDestination($fromSiteId, $toSiteId)
    {
        $output = array();
        $arrListServices = $this->eps->listServicesForSites(time(), $fromSiteId, $toSiteId);
        /* @var \ResultCourierServiceExt $service */
        foreach ((array)$arrListServices as $service):
            $output[] = array('id' => $service->getTypeId(), 'name' => $service->getName());
        endforeach;
        return $output;
    }

    /**
     * @param \ParamFilterCountry $paramFilter
     * @param null $lang
     */
    public function getCountriesSite(\ParamFilterCountry $paramFilter, $lang = null)
    {
        $this->eps->listCountriesEx($paramFilter, $lang);
    }

    /** Returns cities
     * @param string $name City
     * @param int $postcode Post code of city
     * @return array array[label] array[value] array[type] array[postcode] array[nomenclature]  array[days]
     */
    public function getCities($name = null, $postcode = null)
    {
        $cities = array();
        if ($postcode) {
            $paramFilterSite = new \ParamFilterSite();
            $paramFilterSite->setPostCode($postcode);
            $paramFilterSite->setName($name);
            /* @var \ResultSiteEx $listSites */
            $listSites = $this->getListedSites($paramFilterSite);
        } else {
            /* @var \ResultSiteEx $listSites */
            $listSites = $this->eps->listSites(null, $name);
        }

        if ($listSites) {
            /* @var \ResultSite $city */
            foreach ($listSites as $city) {
                $cities[] = array(
                    'id' => $city->getId(),
                    'label' => $this->getCityFormat($city),
                    'value' => $this->getCityFormat($city),
                    'type' => $city->getType(),
                    'postcode' => $city->getPostCode(),
                    'nomenclature' => $this->getCityNomenclature($city),
                    'days' => $city->getServingDays()
                );
            }
        }


        return $cities;
    }

    /**
     * Returns speedy street names
     * @param string $name Street name only
     * @param int $city_id City ID
     * @return array
     */
    public function getStreets($name = null, $city_id = null)
    {
        $streets = array();

        $listStreets = $this->eps->listStreets($name, $city_id);
        if ($listStreets) {
            /* @var \ResultStreet $street */
            foreach ($listStreets as $street) {
                $streets[] = array(
                    'id' => $street->getId(),
                    'label' => ($street->getType() ? $street->getType() . ' ' : '') . $street->getName(),
                    'value' => ($street->getType() ? $street->getType() . ' ' : '') . $street->getName()
                );
            }
        }
        return $streets;
    }

    /** Get blocks from given city
     * @param null $name
     * @param integer $city_id
     * @return array
     */
    public function getBlocks($name = null, $city_id = null)
    {

        $blocks = array();
        $listBlocks = $this->eps->listBlocks($name, $city_id);

        if (is_array($listBlocks) || !empty($listBlocks)) {
            foreach ($listBlocks as $block) {
                $blocks[] = array('label' => $block, 'value' => $block);
            }
        } else {
            return false;
        }
        return $blocks;
    }

    /**
     * This is primary model of required form input data
     * @return array
     */
    protected function getAddressModel()
    {
        return array(
            'city' => null,
            'str_nm' => null,
            'str_no' => null,
            'zip' => null,
            'flr_no' => null,
            'blk_no' => null,
            'ent_no' => null,
            'note' => null,
        );
    }

    /** Returns country from name / iso code
     * @param string $name
     * @param string $isoCode
     * @param null $lang
     * @return int
     */
    public function getSpeedyCountryId($name = 'Bulgaria', $isoCode = 'bg', $lang = null)
    {
        $country = new \ParamFilterCountry();
        $country->setSearchString($name);
        $country->setIsoAlpha2($isoCode);
        /* @var \ResultCountry $country_result */
        $country_result = $this->eps->listCountriesEx($country, $lang)[0];
        return $country_result->getCountryId();
    }

    /** Gets site ID from Speedy
     * @param $site_type
     * @param $site_name
     * @param $site_code
     * @param string $country
     * @param string $iso
     * @param null $lang
     * @return bool|int
     */
    public
    function getSpeedySiteId($site_type, $site_name, $site_code, $country = 'Bulgaria', $iso = 'bg', $lang = null)
    {
        $paramFilterSite = new \ParamFilterSite();
        $paramFilterSite->setRegion($site_name);
        $paramFilterSite->setType($site_type);
        $paramFilterSite->setName($site_name);
        $paramFilterSite->setPostCode($site_code);
        $paramFilterSite->setCountryId($this->getSpeedyCountryId($country, $iso, $lang));
        $arrResultSiteEx = $this->eps->listSitesEx($paramFilterSite);

//        $resultSite = $resultSiteEx->getSite();
        if (count($arrResultSiteEx) == 0) {
            // Населеното място на получателя не е намерено
            return false;
        } else {
            /* @var \ResultSiteEx $resultSiteEx */
            $resultSiteEx = $arrResultSiteEx[0];
        }
        $resultSite = $resultSiteEx->getSite();
        return $resultSite->getId();
    }

    /**
     * @param $city
     * @param $zip
     * @return int
     */
    public function __getSpeedySiteId($city, $zip)
    {
        /* @var \ResultSite $spdCity */
        $spdCity = $this->getCities($city, $zip)[0];
        return $spdCity->getId();

    }

    /** Search site ID
     * @param array $address
     * @return bool
     */
    public function getResolvedSiteFromInputAddressArray(array $address)
    {
        if ($siteId = $this->getSpeedySiteId($address['str_tp'], $address['str_nm'], $address['zip']) !== false) {
            return $siteId;
        }

        if ($siteId = $this->getSpeedySiteId($address['qtr_nm'], $address['qtr_tp'], $address['zip']) !== false) {
            return $siteId;
        }

    }

    /** Reloads address array
     * @param array $input
     * @param array $model
     * @return array
     */
    protected function getReloadedAddressArray(array $input, array $model = array())
    {
        if ($input instanceof ReceiverStreetModel || is_subclass_of($input, 'ReceiverStreetModel')) {
            return $input->getContainer();
        }

        return (new ReceiverStreetModel($input, $model))->getContainer();
    }


    /** Sets Receiver address
     *
     * array['city']        City name
     * array['str_tp']      Street type
     * array['str_nm']      Street name
     * array['str_no']      Street Number
     * array['zip']         Postal code
     * array['flr_no']      Floor Number
     * array['blk_no']      Block Number
     * array['ent_no']      Entrance  Number
     * array['apr_no']      Apartment  Number
     * array['note']        Address Note
     * @param array $address
     * @return \ParamAddress
     */
    public function setReceiverAddress(array $address = array(), $arrCustomKeys = array())
    {

        $address = $this->getReloadedAddressArray($address, $arrCustomKeys);

        $this->initReceiverAndSender();
        //
        // Това е само проба да се вземе тъпия "SiteID"
        $siteId = $this->getResolvedSiteFromInputAddressArray($address);

        //
        // Смесване на масива със задължителни ключове
        $address = array_merge($this->getAddressModel(), $address);

        //
        // Взимане на града От тъпотията на Спиди [Бекъп ако няма $siteId]
        $speedyCty = $this->getCities($address['city'], $address['zip'])[0];
        // dump($speedyCty);

        $this->setWorkingDays($speedyCty['days']);
        //
        // Взимане на улицата от тъпотията на Спиди ....
        $speedyStr = $this->getStreets($address['str_nm'], $speedyCty['id'])[0]; // ->listSitesEx

        //
        // Push to speedy object
        return $this->setSpeedyParamAddresses($siteId, $speedyCty, $speedyStr, $address);
    }

    /** Sets collected data to speedy address param
     * @param $siteId
     * @param $speedyCty
     * @param $speedyStr
     * @param $address
     * @return \ParamAddress
     */
    protected function setSpeedyParamAddresses($siteId, $speedyCty, $speedyStr, $address)
    {
        $this->_address = new \ParamAddress();

        // Разобличаване на дестинацията
        $this->_address->setSiteId(empty($siteId) ? $speedyCty['id'] : $siteId);
        //
        // Квартал (ползвам го като не задължителен)
        $this->_address->setQuarterName($address['qtr_nm']);
        $this->_address->setQuarterType($address['qtr_tp']);
        $this->_address->setQuarterId(empty($siteId) ? $speedyCty['id'] : $siteId);
        //
        // Задаване на улицата
        $this->_address->setStreetId($speedyStr['id']);
        $this->_address->setStreetName($speedyStr['value']);
        $this->_address->setStreetNo($address['str_no']);
        $this->_address->setStreetType($address['str_tp']);
        //
        // И другите параметри ...
        $this->_address->setBlockNo($address['blk_no']);
        $this->_address->setEntranceNo($address['ent_no']);
        $this->_address->setFloorNo($address['flr_no']);
        $this->_address->setApartmentNo($address['apr_no']);
        $this->_address->setAddressNote($address['note']);
        return $this->_address;
    }

    /**
     * @param bool|false $isFragile
     */
    protected function setFrigile($isFragile = false)
    {
        $this->_fragile[] = $isFragile;
    }

    /** Sets Receiver date
     * @param string $realName
     * @param integer $phone
     * @param string $email
     * @return \ParamClientData
     */
    public function setReceiverData($realName, $phone, $email)
    {
        $this->_receiver->setAddress($this->_address);
        $this->_receiver->setPartnerName($realName); // realName
        $this->_receiver->setPhones($this->getPhones($phone));
        $this->_receiver->setEmail($email);
        return $this->_receiver;
    }

    /**
     * @param $phone
     * @return array
     */
    protected function getPhones($phone)
    {
        $receiverPhone = new \ParamPhoneNumber();
        $receiverPhone->setNumber($phone);
        return array(0 => $receiverPhone);
    }

    /** Sets goods package for shipping
     * @param integer $width Width Size of package in cm.
     * @param integer $height Height Size of package in cm.
     * @param integer $depth Depth Size of package in cm.
     * @param float $weight Weight in kg. of package
     * @param string $infoText
     * @param boolean $fragile
     */
    public function setPackage($width, $height, $depth, $weight, $infoText = null, $fragile = false)
    {
        $this->package_count++;
        $this->_picking->setParcelsCount(1);
        $this->_picking->setWeightDeclared($weight);
        $this->_picking->setContents($infoText);
        $this->_picking->setPacking('Пакет');
        $this->_picking->setPackId(null);
        $this->_picking->setDocuments(false);
        $this->_picking->setPalletized(false);
        $this->_picking->setFragile($fragile);
        $this->setFrigile($fragile);

        $size = new \Size();
        $size->setDepth($depth);
        $size->setHeight($height);
        $size->setWidth($width);
        $this->_picking->setSize($size);
        // (0=sender, 1=receiver or 2=third party)
        $this->_picking->setPayerType(1);
    }

    /**
     * TODO Да се направи валидни дни за взимане на пратката при положени че има празници
     * @param $serviceTypeId
     * @return bool
     */
    protected function getAllowedDays($serviceTypeId)
    {
//        $arrTakingDates = $this->eps->getAllowedDaysForTaking(
//            $serviceTypeId, $this->_sender->getAddress()->getSiteId(), null, time()
//        );

//        if (count($arrTakingDates) == 0) {
//            $this->setErrorTrue();
//            return false;
//        } else {
//            return $arrTakingDates[0];
//        }
    }

    /**
     * Resolve sender from app configuration
     */
    protected function resolveSender()
    {
        $this->_sender = new \ParamClientData();
        $this->_sender->setClientId($this->conApi->getClient()->getClientId());
//        $address = new \ParamAddress();
//        $address->setSiteId($this->getSpeedySiteId('пловдив', 4000));
//        $address->setStreetName('Ангел Букурещлиев');
//        $address->setStreetType('ул');
//        $address->setStreetNo('9');
//        $this->_sender->setAddress($address);
    }

    /**
     * This function must be executed as final step
     */
    public function setResolvedData()
    {
        $this->resolveSender();
        /* @var \ResultCourierService $listServices */
        $listServices = $this->eps->listServices(time())[0];
        // dump($listServices);
        $this->_picking->setClientSystemId(1310221100); //OpenCart
        $this->_picking->setRef1(010101);
        $this->_picking->setSender($this->_sender);
        $this->_picking->setReceiver($this->_receiver);
        $this->_picking->setServiceTypeId($listServices->getTypeId());

        //
        //
//        $dataTracking = $this->getAllowedDays($listServices->getTypeId());
        $this->_picking->setTakingDate($this->getTomorrowStamp());
    }


    /**
     * @return \date
     */
    protected function getTomorrowStamp()
    {
        return mktime(0, 0, 0, date('n'), date('j') + 1);
    }

    /** Detect is insurance required
     * @return bool
     */
    protected function isInsuranceRequired()
    {
        if (in_array(true, $this->_fragile)) {
            return true;
        }
        return false;
    }


    /** Sets Amount total of delivery pack with insurance
     * @param int $total Set item total cost
     * @param int $insurance_type Set insurance payer type (0=sender, 1=reciever or 2=third party)
     */
    protected function setShippingPackTotal($total, $insurance_type = 0)
    {

        if ($this->isInsuranceRequired()) {
            $this->_picking->setAmountInsuranceBase($total);
            $this->_picking->setPayerTypeInsurance(0);
        }

        if ($total) {
            $this->_picking->setAmountCodBase($total);
        } else {
            $this->_picking->setAmountCodBase(0);
        }
    }

    /** Gets calculation total of shipping
     * @param int $total
     * @return array
     */
    public function getCalculation($total = 0)
    {
        // dump($this->package_count < 1 || $this->_error);

        //
        // Todo Save result into session and pass it to billing
        if ($this->package_count < 1 || $this->_error) {
            return -1;
        }

        //
        // Prepare final data
        $this->setShippingPackTotal($total);
        $this->setResolvedData();

        //
        // Make calculation
        try {
            // Uncomment for shipping price only
            // return $result->getAmounts()->getTotal();
            return $this->getReformatedResult($this->eps->calculatePicking($this->_picking));
        } catch (\Exception $e) {
            //
            // Record message in error string
            $this->error = $e->getMessage();
            return -1;
        }
    }

    /** Remove unwanted prefix key
     * @param $prefix
     * @param ResultAmounts $container
     * @return array
     */
    protected function getReformatedResult(\ResultCalculation $container)
    {
        $amount = $container->getAmounts();
        return array(
            'net_cost' => $amount->getNet(),
            'vat_cost' => $amount->getVat(),
            'insurance' => $amount->getInsuranceBase(),
            'total' => $amount->getTotal(),
            'tracing_date' => $container->getTakingDate(), // 2016-01-16T00:00:00+02:00
            'shipping_end' => $container->getDeadlineDelivery() // 2016-01-18T19:00:00+02:00
        );
    }

    public function setBilling()
    {
        $this->setResolvedData();
        $result = $this->eps->createBillOfLading($this->_picking);
        return $result->getAmounts()->getTotal();
    }

    /**
     * Sets error in shipping
     */
    public function setErrorTrue()
    {
        $this->_error = true;
    }

    public function getErrorInfo()
    {
        return $this->error;
    }

}
