<?php
/**
 * Created by PhpStorm.
 * Author: Angel Zaprianov <me@fire1.eu>
 * Date: 12/14/15
 * Time: 2:47 PM
 */

namespace Speedy\Test;


use Speedy\ConnectionSpeedyApi;
use Speedy\ConnectionEpsStub;

class ConnectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConnectionSpeedyApi
     */
    public $connection;

    public function setUp()
    {
        $this->connection = new ConnectionSpeedyApi(new ConnectionEpsStub('999761', '9344789773'));
    }


    public function testOK()
    {
        $this->assertEquals(true, $this->connection->isConnected());
    }
}
