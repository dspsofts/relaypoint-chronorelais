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
        parent::setUp();
        $this->gateway = new Gateway();
    }

    protected function getMockAddress()
    {
        static $mockAddress = null;
        if ($mockAddress === null) {
            $mockAddress = new \stdClass();
            $mockAddress->nomEnseigne = 'name';
            $mockAddress->adresse1 = 'street';
            $mockAddress->adresse2 = 'locationHint';
            $mockAddress->codePostal = 'zip';
            $mockAddress->localite = 'city';
            $mockAddress->identifiantChronopostPointA2PAS = 'code';
            $mockAddress->coordGeoLatitude = 'latitude';
            $mockAddress->coordGeoLongitude = 'longitude';
            $mockAddress->horairesOuvertureDimanche = "00:00-00:00 00:00-00:00";
            $mockAddress->horairesOuvertureLundi = "00:00-00:00 00:00-00:00";
            $mockAddress->horairesOuvertureMardi = "00:00-00:00 00:00-00:00";
            $mockAddress->horairesOuvertureMercredi = "00:00-00:00 00:00-00:00";
            $mockAddress->horairesOuvertureJeudi = "00:00-00:00 00:00-00:00";
            $mockAddress->horairesOuvertureVendredi = "00:00-00:00 00:00-00:00";
            $mockAddress->horairesOuvertureSamedi = "00:00-00:00 00:00-00:00";

        }

        return $mockAddress;
    }

    public function testSearch()
    {
        $soapClient = $this->getMockFromWsdl(__DIR__ . '/wsdl.xml', 'SoapClientMockChronopostForSearch');
        $this->gateway->setSoapClient($soapClient);

        $resultSearch = new \stdClass();
        $resultSearch->return = array($this->getMockAddress());

        $soapClient->expects($this->any())
            ->method(Gateway::SERVICE_SEARCH)
            ->will($this->returnValue($resultSearch));

        $list = $this->gateway->search(array('zip' => '75004'));
        $this->assertNotEmpty($list);
    }

    public function testDetail()
    {
        $soapClient = $this->getMockFromWsdl(__DIR__ . '/wsdl.xml', 'SoapClientMockChronopostForDetail');
        $this->gateway->setSoapClient($soapClient);

        $resultDetail = new \stdClass();
        $resultDetail->return = $this->getMockAddress();

        $soapClient->expects($this->any())
            ->method(Gateway::SERVICE_DETAIL)
            ->will($this->returnValue($resultDetail));

        $detail = $this->gateway->detail(array('code' => 'code'));

        $expected = array(
            'active' => true,
            'code' => 'code',
            'name' => 'name',
            'street' => 'street',
            'locationHint' => 'locationHint',
            'zip' => 'zip',
            'city' => 'city',
            'latitude' => 'latitude',
            'longitude' => 'longitude',

        );
        $this->assertEquals($expected, $detail->getFields());
    }
}
