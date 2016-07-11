<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class WirelessNetworksController extends Controller
{
    /**
     * @Route("/wireless-networks", name="wireless_networks")
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {
        $networkManager = $this->get('app.wireless.network_manager');
        $scanner = $this->get('app.wireless.scanner');

        return $this->render('default/wireless-networks.html.twig', [
            'saved_networks' => $networkManager->listNetworks(),
            'networks' => $scanner->getResults(),
        ]);
    }

    /**
     * @Route("/wireless-networks", name="wireless_networks_add")
     * @Method("POST")
     */
    public function addAction(Request $request)
    {
        $ssid = $request->request->get('ssid');
        $password = $request->request->get('password', null);
        $enablePreferredBssid = $request->request->get('enable_preferred_bssid', false);
        $bssid = null;
        $keyManagement = $request->request->get('key_management', null);

        if ($enablePreferredBssid) {
            $bssid = $request->request->get('bssid', null);
        }

        $networkManager = $this->get('app.wireless.network_manager');

        if (!$networkManager->addNetwork($ssid, $password, $bssid, $keyManagement)) {
            $this->addFlash('danger', sprintf('Network "%s" could not be added to your saved networks.', $ssid));
        } else {
            $this->addFlash('success', sprintf('Network "%s" is now added to your saved networks.', $ssid));
        }

        return $this->redirectToRoute('wireless_networks');
    }
}
