<?php

namespace AppBundle\Controller;

use AppBundle\Network\NetworkInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class NetworkInterfacesController extends Controller
{
    /**
     * @Route("/network-interfaces", name="network_interfaces")
     * @Method("GET")
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
                    'bytes_received' => $interface->getRxBytesCount(),
                    'bytes_sent' => $interface->getTxBytesCount(),
                    'wireless_connection' => $interface->getWirelessConnectionDetails(),
                ];
            }
        }

        return $this->render('default/network-interfaces.html.twig', [
            'interfaces' => $data
        ]);
    }

       /**
     * @Route("/network-interfaces/enable", name="network_interfaces_enable")
     * @Method("POST")
     */
    public function enableAction(Request $request)
    {
        $name = $request->request->get('name', null);

        if (!is_null($name) && ctype_alnum($name)) {
            $interface = NetworkInterface::get($name, $this->get('app.command.executor'));

            if ($interface->up()) {
                $this->addFlash('success', sprintf('Interface "%s" succesfully enabled.', $name));
            } else {
                $this->addFlash('danger', sprintf('Interface "%s" could not be enabled.', $name));
            }
        }

        return $this->redirectToRoute('network_interfaces');
    }

    /**
     * @Route("/network-interfaces/disable", name="network_interfaces_disable")
     * @Method("POST")
     */
    public function disableAction(Request $request)
    {
        $name = $request->request->get('name', null);

        if (!is_null($name) && ctype_alnum($name)) {
            $interface = NetworkInterface::get($name, $this->get('app.command.executor'));

            if ($interface->down()) {
                $this->addFlash('success', sprintf('Interface "%s" succesfully disabled.', $name));
            } else {
                $this->addFlash('danger', sprintf('Interface "%s" could not be disabled.', $name));
            }
        }

        return $this->redirectToRoute('network_interfaces');
    }
}
