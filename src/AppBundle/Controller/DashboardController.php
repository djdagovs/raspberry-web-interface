<?php

namespace AppBundle\Controller;

use AppBundle\Network\NetworkInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class DashboardController extends Controller
{
    /**
     * @Route("/", name="dashboard")
     */
    public function indexAction(Request $request)
    {
        $interface = NetworkInterface::get('wlan0', $this->get('app.command.executor'));
        $wlan0 = [
            'name' => $interface->getName(),
            'operation_state' => $interface->getOperationState(),
            'ip' => $interface->getIpAddress(),
            'mac_address' => $interface->getMacAddress(),
            'netmask' => $interface->getNetmask(),
            'wireless_connection' => $interface->getWirelessConnectionDetails(),
        ];

        return $this->render('default/dashboard.html.twig', [
            'interface' => $wlan0
        ]);
    }
}
