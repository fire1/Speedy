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
* Project: Speedy
*
* Date: 12/13/2015
* Time: 18:37
*
* @author Angel Zaprianov <me@fire1.eu>
*/

namespace Fire1\Speedy;

use Monolog\Logger;

/**
 * Чернова с работещите глупости
 * @deprecated  This only wrap given functions
 *
 * Speedy Service
 * Class SpeedyServiceWrapper
 * @package Speedy
 */
class SpeedyServiceWrapper
{
    /**
     * @var Logger
     */
    protected $log;
    /**
     * @var string
     */
    private $_error;
    /**
     * Server destination
     */
    const SERVER = 'https://www.speedy.bg/eps/main01.wsdl';
    /**
     * @var \EPSFacade
     */
    protected $ePSFacade;
    /**
     * @var \ResultLogin
     */
    protected $resultLogin;
    /**
     * @var \ResultClientData
     */
    protected $resultClientData;

    /** Construct service
     * @param ConnectionInterface $connectionInterface
     * @param Logger $logger
     */
    public function __construct(ConnectionInterface $connectionInterface, Logger $logger)
    {
        $this->log = $logger;
        $this->includeBaseDependencyClasses();
        $this->initialConnectionInformation($connectionInterface);
    }

    /** Resolve connection information and initialize
     * @param ConnectionInterface $connectionInterface
     */
    protected function initialConnectionInformation(ConnectionInterface $connectionInterface)
    {
        $server_address = (is_null($connectionInterface->getServer())) ? self::SERVER : $connectionInterface->getServer();
        try {
            $ePSSOAPInterfaceImpl = new \EPSSOAPInterfaceImpl($server_address);
            $this->ePSFacade = new \EPSFacade($ePSSOAPInterfaceImpl, $connectionInterface->getUsername(), $connectionInterface->getPassword());
            $this->resultLogin = $this->ePSFacade->login();
            $this->resultClientData = $this->ePSFacade->getClientById($this->resultLogin->getClientId());
        } catch (\Exception $e) {
            $this->_error = $e->getMessage();
            $this->log->addError('Speedy :: getServices :: ' . $e->getMessage());
        }
    }

    /**
     * Adds library classes
     */
    public static function includeBaseDependencyClasses()
    {
        //
        // Adding main dependency classes
        //  Note: include only once!
        require_once((__DIR__) . '/lib/speedy-eps-lib/util/Util.class.php');
        require_once((__DIR__) . '/lib/speedy-eps-lib/ver01/EPSFacade.class.php');
        require_once((__DIR__) . '/lib/speedy-eps-lib/ver01/soap/EPSSOAPInterfaceImpl.class.php');

        //
        // Check for missing required classes
        if (!class_exists('ResultSite'))
            require_once((__DIR__) . '/lib/speedy-eps-lib/ver01/ResultSite.class.php');
        if (!class_exists('AddrNomen'))
            require_once((__DIR__) . '/lib/speedy-eps-lib/ver01/AddrNomen.class.php');
    }

    /** Returns service
     * @return array
     */
    public function getServices()
    {
        $this->_error = '';
        $services = array();
        $time = time();
        if ($this->resultLogin) {
            try {
                $listServices = $this->ePSFacade->listServices($time);

                if ($listServices) {
                    foreach ($listServices as $service) {
                        if ($service->getTypeId() == 26 || $service->getTypeId() == 36) {
                            continue;
                        }

                        $services[$service->getTypeId()] = $service->getName();
                    }
                }
            } catch (\Exception $e) {
                $this->_error = $e->getMessage();
                $this->log->addError('Speedy :: getServices :: ' . $e->getMessage());
            }
        }

        return $services;
    }

    /** Returns offices
     * @param null $name
     * @param null $city_id
     * @return array
     */
    public function getOffices($name = null, $city_id = null)
    {
        $this->_error = '';
        $offices = array();

        if (isset($this->resultLogin)) {
            try {
                $listOffices = $this->ePSFacade->listOfficesEx($name, $city_id);
                if ($listOffices) {
                    foreach ($listOffices as $office) {
                        $offices[] = array(
                            'id' => $office->getId(),
                            'label' => $office->getId() . ' ' . $office->getName() . ', ' . $office->getAddress()->getFullAddressString(),
                            'value' => $office->getName()
                        );
                    }
                }
            } catch (\Exception $e) {
                $this->_error = $e->getMessage();
                $this->log->addError('Speedy :: getOffices :: ' . $e->getMessage());
            }
        }

        return $offices;
    }

    /** Returns cities
     * @param null $name
     * @param null $postcode
     * @return array
     */
    public function getCities($name = null, $postcode = null)
    {
        $this->_error = '';
        $cities = array();

        if (isset($this->resultLogin)) {
            try {
                if ($postcode) {
                    require_once((__DIR__) . '/lib/speedy-eps-lib/ver01/ParamFilterSite.class.php');

                    $paramFilterSite = new \ParamFilterSite();
                    $paramFilterSite->setPostCode($postcode);
                    $paramFilterSite->setName($name);
                    $listSitesEx = $this->ePSFacade->listSitesEx($paramFilterSite);
                    $listSites = array();

                    foreach ($listSitesEx as $result) {
                        if ($result->isExactMatch()) {
                            $listSites[] = $result->getSite();
                        }
                    }
                } else {
                    $listSites = $this->ePSFacade->listSites(null, $name);
                }

                if ($listSites) {
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
            } catch (\Exception $e) {
                $this->_error = $e->getMessage();
                $this->log->addError('Speedy :: getCities :: ' . $e->getMessage());
            }
        }

        return $cities;
    }

    /** Gets streets
     * @param null $name
     * @param null $city_id
     * @return array
     */
    public function getStreets($name = null, $city_id = null)
    {
        $this->_error = '';
        $streets = array();

        if (isset($this->resultLogin)) {
            try {
                $listStreets = $this->ePSFacade->listStreets($name, $city_id);

                if ($listStreets) {
                    foreach ($listStreets as $street) {
                        $streets[] = array(
                            'id' => $street->getId(),
                            'label' => ($street->getType() ? $street->getType() . ' ' : '') . $street->getName(),
                            'value' => ($street->getType() ? $street->getType() . ' ' : '') . $street->getName()
                        );
                    }
                }
            } catch (\Exception $e) {
                $this->_error = $e->getMessage();
                $this->log->addError('Speedy :: getStreets :: ' . $e->getMessage());
            }
        }

        return $streets;
    }

    /** Get blocks
     * @param null $name
     * @param null $city_id
     * @return array
     */
    public function getBlocks($name = null, $city_id = null)
    {
        $this->_error = '';
        $blocks = array();

        if (isset($this->resultLogin)) {
            try {
                $listBlocks = $this->ePSFacade->listBlocks($name, $city_id);

                if ($listBlocks) {
                    foreach ($listBlocks as $block) {
                        $blocks[] = array(
                            'label' => $block,
                            'value' => $block
                        );
                    }
                }
            } catch (\Exception $e) {
                $this->_error = $e->getMessage();
                $this->log->addError('Speedy :: getBlocks :: ' . $e->getMessage());
            }
        }

        return $blocks;
    }

    /** Calculate base data
     * @param $data
     * @return array
     */
    public function calculate($data)
    {
        $this->_error = '';
        $resultCalculation = array();

        if (isset($this->resultLogin)) {
            try {
                $paramCalculation = new \ParamCalculation();
                $paramCalculation->setSenderId($this->resultLogin->getClientId());
                $paramCalculation->setBroughtToOffice($this->config->get('speedy_from_office') && $this->config->get('speedy_office_id'));
                $paramCalculation->setToBeCalled($data['to_office'] && $data['office_id']);
                $paramCalculation->setParcelsCount($data['count']);
                $paramCalculation->setWeightDeclared($data['weight']);
                $paramCalculation->setDocuments($this->config->get('speedy_documents'));
                $paramCalculation->setPalletized(false);

                if (!empty($data['fixed_time'])) {
                    $paramCalculation->setFixedTimeDelivery($data['fixed_time']);
                } else {
                    $paramCalculation->setFixedTimeDelivery(null);
                }

                if (isset($data['loading'])) {
                    if ($data['insurance']) {
                        if ($data['fragile']) {
                            $paramCalculation->setFragile(true);
                        } else {
                            $paramCalculation->setFragile(false);
                        }

                        $paramCalculation->setAmountInsuranceBase($data['totalNoShipping']);
                        $paramCalculation->setPayerTypeInsurance(\ParamCalculation::PAYER_TYPE_RECEIVER);
                    } else {
                        $paramCalculation->setFragile(false);
                    }
                } elseif ($this->config->get('speedy_insurance')) {
                    if ($this->config->get('speedy_fragile')) {
                        $paramCalculation->setFragile(true);
                    } else {
                        $paramCalculation->setFragile(false);
                    }

                    $paramCalculation->setAmountInsuranceBase($data['totalNoShipping']);
                    $paramCalculation->setPayerTypeInsurance(\ParamCalculation::PAYER_TYPE_RECEIVER);
                } else {
                    $paramCalculation->setFragile(false);
                }

                if (!($data['to_office'] && $data['office_id'])) {
                    $paramCalculation->setReceiverSiteId($data['city_id']);
                }

                $paramCalculation->setPayerType(\ParamCalculation::PAYER_TYPE_RECEIVER);

                if ($data['cod']) {
                    $paramCalculation->setAmountCodBase($data['total']);
                } else {
                    $paramCalculation->setAmountCodBase(0);
                }

                $paramCalculation->setTakingDate($data['taking_date']);
                $paramCalculation->setAutoAdjustTakingDate(true);

                if ($this->config->get('speedy_from_office') && $this->config->get('speedy_office_id')) {
                    $paramCalculation->setWillBringToOfficeId($this->config->get('speedy_office_id'));
                }

                if ($data['to_office'] && $data['office_id']) {
                    $paramCalculation->setOfficeToBeCalledId($data['office_id']);
                } else {
                    $paramCalculation->setOfficeToBeCalledId(null);
                }

                $resultCalculation = $this->ePSFacade->calculateMultipleServices($paramCalculation, $this->config->get('speedy_allowed_methods'));

                foreach ($resultCalculation as $key => $service) {
                    if ($service->getErrorDescription()) {
                        unset($resultCalculation[$key]);
                    }
                }

                $resultCalculation = array_values($resultCalculation);
            } catch (\Exception $e) {
                $this->_error = $e->getMessage();
                $this->log->addError('Speedy :: calculate :: ' . $e->getMessage());
            }
        }

        return $resultCalculation;
    }


    public function getAllowedDaysForTaking($data)
    {
        $this->_error = '';
        $firstAvailableDate = '';

        if (isset($this->resultLogin)) {
            try {
                if ($this->config->get('speedy_from_office') && $this->config->get('speedy_office_id')) {
                    $senderSiteId = null;
                    $senderOfficeId = $this->config->get('speedy_office_id');
                } else {
                    $senderSiteId = $this->resultClientData->getAddress()->getSiteId();
                    $senderOfficeId = null;
                }

                $takingTime = $this->ePSFacade->getAllowedDaysForTaking($data['shipping_method_id'], $senderSiteId, $senderOfficeId, $data['taking_date']);

                if ($takingTime) {
                    $firstAvailableDate = $takingTime[0];
                }
            } catch (\Exception $e) {
                $this->_error = $e->getMessage();
                $this->log->addError('Speedy :: getAllowedDaysForTaking :: ' . $e->getMessage());
            }
        }

        return $firstAvailableDate;
    }

    /** Creates bill of loading
     * @param $data
     * @param $order
     * @return array
     */
    public function createBillOfLading($data, $order)
    {
        require_once((__DIR__) . '/lib/speedy-eps-lib/ver01/ParamCalculation.class.php');

        $this->_error = '';
        $bol = array();

        if (isset($this->resultLogin)) {
            try {
                $sender = new \ParamClientData();
                $sender->setClientId($this->resultLogin->getClientId());

                if ($this->config->get('speedy_telephone')) {
                    $senderPhone = new \ParamPhoneNumber();
                    $senderPhone->setNumber($this->config->get('speedy_telephone'));
                    $sender->setPhones(array(0 => $senderPhone));
                }

                $receiverAddress = new \ParamAddress();
                $receiverAddress->setSiteId($data['city_id']);

                if ($data['quarter']) {
                    $receiverAddress->setQuarterName($data['quarter']);
                }

                if ($data['quarter_id']) {
                    $receiverAddress->setQuarterId($data['quarter_id']);
                }

                if ($data['street']) {
                    $receiverAddress->setStreetName($data['street']);
                }

                if ($data['street_id']) {
                    $receiverAddress->setStreetId($data['street_id']);
                }

                if ($data['street_no']) {
                    $receiverAddress->setStreetNo($data['street_no']);
                }

                if ($data['block_no']) {
                    $receiverAddress->setBlockNo($data['block_no']);
                }

                if ($data['entrance_no']) {
                    $receiverAddress->setEntranceNo($data['entrance_no']);
                }

                if ($data['floor_no']) {
                    $receiverAddress->setFloorNo($data['floor_no']);
                }

                if ($data['apartment_no']) {
                    $receiverAddress->setApartmentNo($data['apartment_no']);
                }

                if ($data['note']) {
                    $receiverAddress->setAddressNote($data['note']);
                }

                $receiver = new \ParamClientData();
                $receiver->setPartnerName($order['firstname'] . ' ' . $order['lastname']);
                $receiverPhone = new \ParamPhoneNumber();
                $receiverPhone->setNumber($order['telephone']);
                $receiver->setPhones(array(0 => $receiverPhone));
                $receiver->setEmail($order['email']);

                $picking = new \ParamPicking();
                $picking->setClientSystemId(1310221100); //OpenCart
                $picking->setRef1($order['order_id']);

                if ($data['depth'] || $data['height'] || $data['width']) {
                    $size = new Size();

                    if ($data['depth']) {
                        $size->setDepth($data['depth']);
                    }

                    if ($data['height']) {
                        $size->setHeight($data['height']);
                    }

                    if ($data['width']) {
                        $size->setWidth($data['width']);
                    }

                    $picking->setSize($size);
                }

                if (!empty($data['fixed_time'])) {
                    $picking->setFixedTimeDelivery($data['fixed_time']);
                }

                $picking->setServiceTypeId($data['shipping_method_id']);

                if ($data['to_office'] && $data['office_id']) {
                    $picking->setOfficeToBeCalledId($data['office_id']);
                } else {
                    $receiver->setAddress($receiverAddress);
                    $picking->setOfficeToBeCalledId(null);
                }

                $picking->setBackDocumentsRequest($this->config->get('speedy_back_documents'));
                $picking->setBackReceiptRequest($this->config->get('speedy_back_receipt'));

                if ($this->config->get('speedy_from_office') && $this->config->get('speedy_office_id')) {
                    $picking->setWillBringToOffice(true);
                    $picking->setWillBringToOfficeId($this->config->get('speedy_office_id'));
                } else {
                    $picking->setWillBringToOffice(false);
                }

                $picking->setParcelsCount($data['count']);
                $picking->setWeightDeclared($data['weight']);
                $picking->setContents($data['contents']);
                $picking->setPacking($data['packing']);
                $picking->setPackId(null);
                $picking->setDocuments($this->config->get('speedy_documents'));
                $picking->setPalletized(false);

                $payerType = $this->getPayerType($order['order_id'], $data['shipping_method_cost']);

                if ($data['insurance']) {
                    if ($data['fragile']) {
                        $picking->setFragile(true);
                    } else {
                        $picking->setFragile(false);
                    }

                    $picking->setAmountInsuranceBase($data['totalNoShipping']);
                    /*
                    if ($this->config->get('speedy_pricing') == 'free' || $this->config->get('speedy_pricing') == 'fixed') {
                        $picking->setPayerTypeInsurance($payerType);
                    } else {
                        $picking->setPayerTypeInsurance($payerType);
                    } */
                    $picking->setPayerTypeInsurance($payerType);
                } else {
                    $picking->setFragile(false);
                }

                $picking->setSender($sender);
                $picking->setReceiver($receiver);

                /*
                if ($this->config->get('speedy_pricing') == 'free' || $this->config->get('speedy_pricing') == 'fixed') {
                    $picking->setPayerType($payerType);
                } else {
                      $picking->setPayerType($payerType);
                }

                */

                $picking->setPayerType($payerType);

                $picking->setTakingDate($data['taking_date']);

                if ($data['deffered_days']) {
                    $picking->setDeferredDeliveryWorkDays($data['deffered_days']);
                }

                if ($data['client_note']) {
                    $picking->setNoteClient($data['client_note']);
                }

                if ($data['cod']) {
                    $picking->setAmountCodBase($data['total']);
                } else {
                    $picking->setAmountCodBase(0);
                }

                $result = $this->ePSFacade->createBillOfLading($picking);
                $parcels = $result->getGeneratedParcels();
                $bol['bol_id'] = $parcels[0]->getParcelId();
                $bol['total'] = $result->getAmounts()->getTotal();
            } catch (\Exception $e) {
                $this->_error = $e->getMessage();
                $this->log->addError('Speedy :: createBillOfLading :: ' . $e->getMessage());
            }
        }

        return $bol;
    }

    /** Create PDF from bol ID
     * @param $bol_id
     * @return array|string
     */
    public function createPDF($bol_id)
    {
        require_once((__DIR__) . '/lib/speedy-eps-lib/ver01/ParamPDF.class.php');

        $this->_error = '';
        $pdf = '';

        if (isset($this->resultLogin)) {
            try {
                $paramPDF = new \ParamPDF();

                if ($this->config->get('speedy_label_printer')) {
                    $pickingParcels = $this->ePSFacade->getPickingParcels($bol_id);

                    $ids = array();

                    foreach ($pickingParcels as $parcel) {
                        $ids[] = $parcel->getParcelId();
                    }

                    $paramPDF->setIds($ids);
                    $paramPDF->setType(\ParamPDF::PARAM_PDF_TYPE_LBL);
                } else {
                    $paramPDF->setIds($bol_id);
                    $paramPDF->setType(\ParamPDF::PARAM_PDF_TYPE_BOL);
                }

                $paramPDF->setIncludeAutoPrintJS(true);

                $pdf = $this->ePSFacade->createPDF($paramPDF);
            } catch (\Exception $e) {
                $this->_error = $e->getMessage();
                $this->log->addError('Speedy :: createPDF :: ' . $e->getMessage());
            }
        }

        return $pdf;
    }

    /** Cancel
     * @param $bol_id
     * @return bool
     */
    public function cancelBol($bol_id)
    {
        $this->_error = '';
        $cancelled = false;

        if (isset($this->resultLogin)) {
            try {
                $this->ePSFacade->invalidatePicking($bol_id);
                $cancelled = true;
            } catch (\Exception $e) {
                $this->_error = $e->getMessage();
                $this->log->addError('Speedy :: cancelBol :: ' . $e->getMessage());
            }
        }

        return $cancelled;
    }

    /** Gets error
     * @param null $type
     * @return bool
     */
    public function getError($type = null)
    {
        if ($type) {
            if (isset($this->_error[$type])) {
                return $this->_error[$type];
            } else {
                return false;
            }
        } else {
            return $this->_error;
        }
    }

    /** Sets error
     * @param $error
     * @param null $type
     */
    public function setError($error, $type = null)
    {
        if ($type) {
            $this->_error[$type] = $error;
        } else {
            $this->_error = $error;
        }
    }

    /** Checks Credentials of user
     * @param $username
     * @param $password
     * @return bool|\ResultLogin
     */
    public function checkCredentials($username, $password)
    {
        $this->ePSFacade->setUsername($username);
        $this->ePSFacade->setPassword($password);

        try {

            return $this->ePSFacade->login();
        } catch (\ClientException $ce) {
            return FALSE;
        } catch (\ServerException $se) {
            return FALSE;
        }
    }

    /** Translator
     * @param $value
     * @param string $language_from
     * @param string $language_to
     * @return mixed
     */

    protected function transliteration($value, $language_from = 'en', $language_to = 'bg')
    {
        $en = array('a', 'b', 'v', 'g', 'd', 'e', 'zh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'tc', 'ch', 'sh', 'sht', 'a', 'y', 'yu', 'ya', 'y', 'e', 'yo', 'A', 'B', 'V', 'G', 'D', 'E', 'Zh', 'Z', 'I', 'Y', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F', 'H', 'Tc', 'Ch', 'Sh', 'Sht', 'A', 'Y', 'Yu', 'Ya', 'Y', 'E', 'Yo', 'q', 'Q');
        $bg = array('а', 'б', 'в', 'г', 'д', 'е', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ь', 'ю', 'я', 'ы', 'э', 'ё', 'А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ь', 'Ю', 'Я', 'Ы', 'Э', 'Ё', 'я', 'Я');

        if ($language_from != $language_to) {
            $value = str_replace(${$language_from}, ${$language_to}, $value);
        }

        return $value;
    }

    /** Gets payer type
     * @param $order_id
     * @param $shippingCost
     * @return null
     */
    private function getPayerType($order_id, $shippingCost)
    {
        $payerType = null;
        $db = $this->registry->get('db');
        $session = $this->registry->get('session');
        $query = $db->query("SELECT data FROM " . DB_PREFIX . "speedy_order WHERE order_id = '" . (int)$order_id . "'");

        $data = unserialize($query->row['data']);

        if ($data['price_gen_method'] && !$session->data['is_speedy_bol_recalculated']) {
            if ($data['price_gen_method'] == 'fixed' || $data['price_gen_method'] == 'free') {
                if ($data['price_gen_method'] == 'free') {
                    $delta = 0.0001;

                    if (abs($data['shipping_method_cost'] - 0.0000) < $delta) {
                        $payerType = \ParamCalculation::PAYER_TYPE_SENDER;
                    } else {
                        $payerType = \ParamCalculation::PAYER_TYPE_RECEIVER;
                    }
                } else {
                    $payerType = \ParamCalculation::PAYER_TYPE_SENDER;
                }
            } else {
                $payerType = \ParamCalculation::PAYER_TYPE_RECEIVER;
            }
        } elseif ($data['price_gen_method'] && $session->data['is_speedy_bol_recalculated']) {
            if ($this->config->get('speedy_pricing') == 'free' || $this->config->get('speedy_pricing') == 'fixed') {
                if ($this->config->get('speedy_pricing') == 'free') {
                    $delta = 0.0001;

                    if (($shippingCost - 0.0000) < $delta) {
                        $payerType = \ParamCalculation::PAYER_TYPE_SENDER;
                    } else {
                        $payerType = \ParamCalculation::PAYER_TYPE_RECEIVER;
                    }
                } else {
                    $payerType = \ParamCalculation::PAYER_TYPE_SENDER;
                }
            } else {
                $payerType = \ParamCalculation::PAYER_TYPE_RECEIVER;
            }
        } elseif (!$data['price_gen_method']) {
            if ($this->config->get('speedy_pricing') == 'free' || $this->config->get('speedy_pricing') == 'fixed') {
                $payerType = \ParamCalculation::PAYER_TYPE_SENDER;
            } else {
                $payerType = \ParamCalculation::PAYER_TYPE_RECEIVER;
            }
        }

        return $payerType;
    }
}