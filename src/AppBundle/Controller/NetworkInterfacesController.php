<?php

namespace AppBundle\Controller;

use AppBundle\Network\NetworkInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class NetworkInterfacesController extends Controller
{
    /**
     * @Route("/network-interfaces", name="network_interfaces")
     */
    public function indexAction(Request $request)
    {
        $interfaces = NetworkInterface::getAll($this->get('app.command.executor'));
        $data = array();

        if ($interfaces) {
            foreach ($interfaces as $interface) {
                $data[$interface->getName()] = [
                    'name' => $interface->getName(),
                    'operation_state' => $interface->getOperationState(),
                    'ip' => $interface->getIpAddress(),
                    'mac_address' => $interface->getMacAddress(),
                    'netmask' => $interface->getNetmask(),
                    'wireless_connection' => $interface->getWirelessConnectionDetails(),
                ];
            }
        }

        // replace this example code with whatever you need
        return $this->render('default/network-interfaces.html.twig', [
            'interfaces' => $data
        ]);
    }
}
