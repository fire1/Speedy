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

/** Stub ConnectionSpeedyApi information
 * Class ConnectionEpsStub
 * @package Speedy
 */
class ConnectionImplements implements ConnectionInterface
{
    /**
     * @var string
     */
    protected $username;
    /**
     * @var string
     */
    protected $password;
    /**
     * @var null|string
     */
    protected $server;

    /** Sets connection parameters
     * @param $username
     * @param $password
     * @param null $server
     */
    public function __construct($username, $password, $server = null)
    {
        $this->username = $username;
        $this->password = $password;
        $this->server = $server;
    }

    /**
     * @inheritdoc
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @inheritdoc
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @inheritdoc
     */
    public function getServer()
    {
        return $this->server;
    }
}