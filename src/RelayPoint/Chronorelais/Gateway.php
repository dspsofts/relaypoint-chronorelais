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

        $soapClient = new \SoapClient(self::URL, array('trace' => true));

        if (ini_get('date.timezone') == '') {
            date_default_timezone_set('Europe/Paris');
        }

        $args = array(
            'codePostal' => $fields['zip'],
            'date' => date('d/m/Y'),
        );

        $result = $soapClient->__soapCall(self::SERVICE_SEARCH, array($args));

        //var_dump($result);
        $list = array();

        foreach ($result->return as $relayPoint)
        {
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

        $soapClient = new \SoapClient(self::URL);

        $args = array(
            'id' => $fields['code'],
        );

        $result = $soapClient->__soapCall(self::SERVICE_DETAIL, array($args));

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

        foreach ($days as $day)
        {
            if ($relayPoint->{'horairesOuverture'.$day} != '00:00-00:00 00:00-00:00')
            {
                $openingHours[$day] = str_replace('-', ' - ', $relayPoint->{'horairesOuverture'.$day});
            }
            else
            {
                $openingHours[$day] = 'Fermé';
            }
        }

        // Horrible fix for the "D quote" which could create big problems...
        $adresse1 = $relayPoint->adresse1;
        if (strpos($adresse1, ' D ')) {
            $adresse1 = str_replace(' D ', " D'", $adresse1);
        }

        $fields = array(
            'code' => strval($relayPoint->identifiantChronopostPointA2PAS),
            'name' => strval($relayPoint->nomEnseigne),
            'street' => strval($adresse1),
            'zip' => strval($relayPoint->codePostal),
            'city' => strval($relayPoint->localite),
            'locationHint' => strval($relayPoint->addresse2),
            'latitude' => strval($relayPoint->coordGeoLatitude),
            'longitude' => strval($relayPoint->coordGeoLongitude),
            'active' => true,
        );
        $address = new Address($fields);

        foreach ($openingHours as $day => $hours)
        {
            $address->addOpeningHour($day, $hours);
        }

        return $address;
    }
}
