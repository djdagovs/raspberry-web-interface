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

        return $this->render('default/wireless-networks.html.twig', [
            'saved_networks' => $networkManager->listNetworks(),
        ]);
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
