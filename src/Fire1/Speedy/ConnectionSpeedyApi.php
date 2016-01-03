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

/**
 * Class ConnectionSpeedyApi
 * @package Speedy
 */
class ConnectionSpeedyApi
{
    /**
     * @var string  ../../../
     */
    protected static $lib_path = __DIR__ . '/../../../';
    /**
     * Speedy remote source
     */
    const SERVER = 'https://www.speedy.bg/eps/main01.wsdl';

    /**
     * @var \ResultLogin
     */
    protected $connection;

    /**
     * @var \ResultClientData
     */
    protected $client;

    /**
     * @var ConnectionInterface
     */
    protected $connInfo;

    /**
     * @var \EPSFacade
     */
    protected $eps_facade;

    /**
     * @param ConnectionInterface $conn
     * @throws ConnectionException
     */
    public function __construct(ConnectionInterface $conn)
    {
        self::includeApis();
        $this->connInfo = $conn;
        try {
            $this->setEpsFacade();
            $this->setLogin()->setClient();
        } catch (\Exception $e) {
            throw new ConnectionException($e->getMessage());
        }
    }

    /** Sets new path to Speedy EPS lib
     * @param $path
     */
    public static function setLibPath($path)
    {
        static::$lib_path = $path;
    }

    /** Gets Speedy EPS lib
     * @return string
     */
    public static function getLibIncludePath()
    {
        return realpath(static::$lib_path);
    }

    private static function includeApis()
    {
        $dir = self::getLibIncludePath();
        //
        // Adding main dependency classes
        //  Note: include only once!
        require_once($dir . '/lib/speedy-eps-lib/util/Util.class.php');
        require_once($dir . '/lib/speedy-eps-lib/ver01/EPSFacade.class.php');
        require_once($dir . '/lib/speedy-eps-lib/ver01/soap/EPSSOAPInterfaceImpl.class.php');

        require_once($dir . '/lib/speedy-eps-lib/ver01/ParamCalculation.class.php');
        require_once($dir . '/lib/speedy-eps-lib/ver01/ParamFilterSite.class.php');

        //
        // Check for missing required classes
        if (!class_exists('ResultSite'))
            require_once($dir . '/lib/speedy-eps-lib/ver01/ResultSite.class.php');
        if (!class_exists('AddrNomen'))
            require_once($dir . '/lib/speedy-eps-lib/ver01/AddrNomen.class.php');
    }


    /**
     * ConnectionSpeedyApi
     * Trigger EPS Facade
     */
    private function setEpsFacade()
    {
        $this->eps_facade = new \EPSFacade(new \EPSSOAPInterfaceImpl($this->getSource()), $this->getUser(), $this->getPass());
    }

    /**
     * Sets EPS Facade Login
     */
    protected function setLogin()
    {
        $this->connection = $this->eps_facade->login();
        return $this;
    }

    /**
     * Sets EPS Facade Client
     */
    protected function setClient()
    {
        $this->client = $this->eps_facade->getClientById($this->connection->getClientId());
        return $this;
    }

    /**
     * @return null|string
     */
    public function getSource()
    {
        return (is_null($this->connInfo->getServer())) ? static::SERVER : $this->connInfo->getServer();
    }

    /**
     * @return \ResultLogin
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /** Checks connection
     * @return bool
     */
    public function isConnected()
    {
        return (bool)$this->connection->getClientId() ? true : false;
    }

    /**
     * @return \ResultClientData
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @return \EPSFacade
     */
    public function getEpsFacade()
    {
        return $this->eps_facade;
    }

    /**
     * @return string
     */
    public function getUser()
    {
        return $this->connInfo->getUsername();
    }

    /**
     * @return string
     */
    public function getPass()
    {
        return $this->connInfo->getPassword();
    }
}