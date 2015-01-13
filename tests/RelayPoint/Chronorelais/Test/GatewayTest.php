<?php

namespace RelayPoint\Chronorelais\Test;

use RelayPoint\Chronorelais\Gateway;

class GatewayTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Gateway
     */
    protected $gateway;

    public function setUp()
    {
        $this->gateway = new Gateway();
    }

    public function testSearch()
    {
        $list = $this->gateway->search(array('zip' => '75004'));
        $this->assertNotEmpty($list);
    }

    public function testDetail()
    {
        $list = $this->gateway->search(array('zip' => '75004'));
        $addr = $list[0];

        $detail = $this->gateway->detail(array('code' => $addr->getField('code')));

        $this->assertNotEmpty($detail);
        $this->assertEquals($addr->getField('code'), $detail->getField('code'));
    }
}
