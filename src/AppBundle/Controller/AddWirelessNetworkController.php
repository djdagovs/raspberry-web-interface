<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class AddWirelessNetworkController extends Controller
{
    /**
     * @Route("/wireless-networks/add", name="add_wireless_network")
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {
        $scanner = $this->get('app.wireless.scanner');

        return $this->render('default/add-wireless-network.html.twig', [
            'networks' => $scanner->getResults(),
        ]);
    }

    /**
     * @Route("/wireless-networks/add", name="add_wireless_network_add")
     * @Method("POST")
     */
    public function addAction(Request $request)
    {
        $networkManager = $this->get('app.wireless.network_manager');
        $interface = $this->get('app.network.preferred_interface');

        $ssid = $request->request->get('ssid');
        $password = $request->request->get('password', null);
        $enablePreferredBssid = $request->request->get('enable_preferred_bssid', false);
        $bssid = null;
        $keyManagement = $request->request->get('key_management', null);

        if ($enablePreferredBssid) {
            $bssid = $request->request->get('bssid', null);
        }

        if ($networkManager->addNetwork($ssid, $password, $bssid, $keyManagement)) {
            $this->addFlash('success', sprintf('Network "%s" is now added to your saved networks.', $ssid));

            if ($interface->restart()) {
                $this->addFlash('success', sprintf('Interface "%s" succesfully restarted.', $interface->getName()));
            } else {
                $this->addFlash('warning', sprintf('Interface "%s" could not be restarted.', $interface->getName()));
            }
        } else {
            $this->addFlash('danger', sprintf('Network "%s" could not be added to your saved networks.', $ssid));
        }

        return $this->redirectToRoute('wireless_networks');
    }
}
