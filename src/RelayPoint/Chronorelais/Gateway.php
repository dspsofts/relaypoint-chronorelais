<?php

/**
 * Chrono Relais Gateway
 *
 * @author Pierre Feyssaguet <pfeyssaguet@gmail.com>
 * @since 2015-01-13
 */

namespace RelayPoint\Chronorelais;

use RelayPoint\Core\AbstractGateway;
use RelayPoint\Core\Address;
use RelayPoint\Core\RelayPointException;

class Gateway extends AbstractGateway
{
    const URL = 'https://www.chronopost.fr/recherchebt-ws-cxf/PointRelaisServiceWS?wsdl';

    const SERVICE_SEARCH = 'rechercheBtParCodeproduitEtCodepostalEtDate';
    const SERVICE_DETAIL = 'rechercheBtParIdChronopostA2Pas';

    protected $soapClient;

    public function __construct()
    {
        parent::__construct();
        $this->soapClient = new \SoapClient(self::URL);
    }

    /**
     * Defines the SoapClient class which must be used.
     *
     * @param \SoapClient $soapClient SoapClient instance
     */
    public function setSoapClient(\SoapClient $soapClient)
    {
        $this->soapClient = $soapClient;
    }

    public function getName()
    {
        return 'Chronorelais';
    }

    public function getDefaultParameters()
    {
        return array(
            'country' => 'FR',
        );
    }

    /**
     * Finds the list of relay points.
     *
     * @param array $parameters Search fields
     * @param boolean $active Turn to false if you only want active relay points
     * @return Address[]
     * @throws RelayPointException
     */
    public function search(array $parameters, $active = true)
    {
        $fields = array_replace($this->getParameters(), $parameters);

        if (ini_get('date.timezone') == '') {
            date_default_timezone_set('Europe/Paris');
        }

        $args = array(
            'codePostal' => $fields['zip'],
            'date' => date('d/m/Y'),
        );

        $result = $this->soapClient->rechercheBtParCodeproduitEtCodepostalEtDate($args);

        $list = array();

        foreach ($result->return as $relayPoint) {
            $address = $this->parseRelayPoint($relayPoint);

            $list[] = $address;
        }

        return $list;
    }

    /**
     * Returns the details of one Chronorelais relay point.
     *
     * @param array $parameters Search fields
     * @return Address|null
     * @throws RelayPointException
     */
    public function detail(array $parameters)
    {
        $fields = array_replace($this->getParameters(), $parameters);

        $args = array(
            'id' => $fields['code'],
        );

        $result = $this->soapClient->rechercheBtParIdChronopostA2Pas($args);

        return $this->parseRelayPoint($result->return);
    }

    /**
     * Parse the SOAP result of a relay point and returns the result in an Address object.
     *
     * @param \stdClass $relayPoint Relay point
     * @return Address
     */
    private function parseRelayPoint(\stdClass $relayPoint)
    {
        $days = array('Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche');
        $openingHours = array();

        foreach ($days as $day) {
            if ($relayPoint->{'horairesOuverture'.$day} != '00:00-00:00 00:00-00:00') {
                $openingHours[$day] = str_replace('-', ' - ', $relayPoint->{'horairesOuverture'.$day});
            } else {
                $openingHours[$day] = 'Fermé';
            }
        }

        // Horrible fix for the "D quote" which could create big problems...
        $adresse1 = strval($relayPoint->adresse1);
        if (strpos($adresse1, ' D ')) {
            $adresse1 = str_replace(' D ', " D'", $adresse1);
        }
        $locationHint = '';
        if (isset($relayPoint->adresse2)) {
            $locationHint = strval($relayPoint->adresse2);
        }

        $fields = array(
            'code' => strval($relayPoint->identifiantChronopostPointA2PAS),
            'name' => strval($relayPoint->nomEnseigne),
            'street' => $adresse1,
            'zip' => strval($relayPoint->codePostal),
            'city' => strval($relayPoint->localite),
            'locationHint' => $locationHint,
            'latitude' => strval($relayPoint->coordGeoLatitude),
            'longitude' => strval($relayPoint->coordGeoLongitude),
            'active' => true,
        );
        $address = new Address($fields);

        foreach ($openingHours as $day => $hours) {
            $address->addOpeningHour($day, $hours);
        }

        return $address;
    }
}
