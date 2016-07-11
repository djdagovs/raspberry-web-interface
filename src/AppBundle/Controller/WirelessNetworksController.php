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

    /**
     * @Route("/wireless-networks/enable", name="wireless_networks_enable")
     * @Method("POST")
     */
    public function enableAction(Request $request)
    {
        $networkManager = $this->get('app.wireless.network_manager');

        $id = $request->request->get('id', null);

        if (!is_null($id) && is_numeric($id)) {
            if ($networkManager->enableNetwork($id)) {
                $this->addFlash('success', 'Network succesfully enabled.');
            } else {
                $this->addFlash('danger', 'Network could not be enabled.');
            }
        }

        return $this->redirectToRoute('wireless_networks');
    }

    /**
     * @Route("/wireless-networks/disable", name="wireless_networks_disable")
     * @Method("POST")
     */
    public function disableAction(Request $request)
    {
        $networkManager = $this->get('app.wireless.network_manager');

        $id = $request->request->get('id', null);

        if (!is_null($id) && is_numeric($id)) {
            if ($networkManager->disableNetwork($id)) {
                $this->addFlash('success', 'Network succesfully disabled.');
            } else {
                $this->addFlash('danger', 'Network could not be disabled.');
            }
        }

        return $this->redirectToRoute('wireless_networks');
    }

    /**
     * @Route("/wireless-networks/remove", name="wireless_networks_remove")
     * @Method("POST")
     */
    public function removeAction(Request $request)
    {
        $networkManager = $this->get('app.wireless.network_manager');

        $id = $request->request->get('id', null);

        if (!is_null($id) && is_numeric($id)) {
            if ($networkManager->removeNetwork($id)) {
                $this->addFlash('success', 'Network succesfully removed.');
            } else {
                $this->addFlash('danger', 'Network could not be removed.');
            }
        }

        return $this->redirectToRoute('wireless_networks');
    }
}
