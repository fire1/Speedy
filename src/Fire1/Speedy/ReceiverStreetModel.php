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
* Date: 1/3/2016
* Time: 18:53
*
* @author Angel Zaprianov <me@fire1.eu>
*/

namespace Fire1\Speedy;

/**
 * Class ReceiverStreetModel
 * @package Fire1\Speedy
 */
class ReceiverStreetModel implements \ArrayAccess
{

    /** Array model required from "setReceiverAddress" method
     * @var array
     */
    protected $arrModel = array(
        'city',
        'str_nm',
        'str_no',
        'zip',
        'flr_no',
        'blk_no',
        'ent_no',
        'note',
    );

    /**
     * @var array
     */
    protected $container = array();

    /**
     * @param array $arrInputStreet
     * @param array $arrCustomKeys
     */
    public function __construct(array $arrInputStreet = array(), $arrCustomKeys = array())
    {
        if (empty($arrCustomKeys)) {
            $this->container = $arrInputStreet;
        } else {
            $this->setFromCustomKeys($arrInputStreet, $arrCustomKeys);
        }
    }

    /**
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
     * @param array $inputStreet
     *
     * array['city']        => city_name
     * array['zip']         => city_code
     * array['qtr_tp']      => quarter_type
     * array['qtr_nm']      => quarter_name
     * array['str_tp']      => street_type
     * array['str_nm']      => street_name
     * array['str_no']      => street_number
     * array['flr_no']      => floor_number
     * array['blk_no']      => block_number
     * array['ent_no']      => entrance_number
     * array['apr_no']      => apartment_number
     * array['note']        => address_note
     * @param array $arrCustomKeys
     */
    public function setFromCustomKeys(array $inputStreet, array $arrCustomKeys)
    {
        foreach ($arrCustomKeys as $k => $v):
            $this->container[$k] = $inputStreet[$v];
        endforeach;
    }

    /** Gets input data array
     * @return array
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->container[$offset]);
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->container[$offset]);
    }

    /**
     * @param mixed $offset
     * @return array|null
     */
    public function offsetGet($offset)
    {
        return isset($this->container[$offset]) ? $this->container[$offset] : null;
    }

    /**
     * @param $offset
     * @return array
     */
    public function __get($offset)
    {
        return $this->container[$offset];
    }


}