<?php
/**
 * Created by PhpStorm.
 * Author: Angel Zaprianov <me@fire1.eu>
 * Date: 12/14/15
 * Time: 10:04 AM
 */

namespace Speedy\Test;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Speedy\ConnectionEpsStub;
use Speedy\SpeedyServiceWrapper;

class SpeedyServiceWrapperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SpeedyServiceWrapper
     */
    protected $service;

    private $random_office;

    protected function setUp()
    {
        $log = new Logger('name');
        $log->pushHandler(new StreamHandler('./errors.log', Logger::WARNING));
        $this->service = new SpeedyServiceWrapper(new ConnectionEpsStub('999761', '9344789773'), $log);
    }


    public function testService()
    {
        $result = $this->service->getServices();
//        var_dump($result);
        $this->assertArrayHasKey(2, $result);
    }

    public function testCities()
    {
        $result = $this->service->getCities();
        $this->random_office = array_rand($result);
//        var_dump($result);
        $this->assertArrayHasKey(1, $result);
    }


    public function testOffices(){

        $result = $this->service->getOffices($this->random_office['label'],$this->random_office['id']);
//                var_dump($result);
        $this->assertArrayHasKey(1, $result);

    }
}
