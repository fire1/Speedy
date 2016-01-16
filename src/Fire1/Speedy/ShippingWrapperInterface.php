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


interface ShippingWrapperInterface
{

    /** Gets services from speedy
     * @return array
     */
    public function getServices();

    /**
     * Gets Cities from speedy
     * @param null $name
     * @param int $postcode
     * @return mixed
     */
    public function getCities($name = null, $postcode = null);

    /** Get blocks from given city
     * @param null $name
     * @param integer $city_id
     * @return array
     */
    public function getBlocks($name = null, $city_id = null);

    /** Gets Streets from speedy
     * @param null $name
     * @param null $city_id
     * @return array
     */
    public function getStreets($name = null, $city_id = null);

    /** Sets Receiver address
     *
     * array['city']        City name
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
    public function setReceiverAddress(array $address = array());

    /** Sets goods package for shipping
     * @param integer $width Width Size of package in cm.
     * @param integer $height Height Size of package in cm.
     * @param integer $depth Depth Size of package in cm.
     * @param float $weight Weight in kg. of package
     * @param string $infoText
     * @param boolean $fragile
     */
    public function setPackage($width, $height, $depth, $weight, $infoText = null, $fragile = false);

    /** Sets Receiver date
     * @param string $realName
     * @param integer $phone
     * @param string $email
     * @return \ParamClientData
     */
    public function setReceiverData($realName, $phone, $email);


    /** Gets calculation total of shipping
     * @param int $total
     * @return mixed
     */
    public function getCalculation($total = 0);

    /** Sets payer type (0=sender, 1=receiver or 2=third party)
     * @param $intType
     */
    public function setPayerType($intType);

}