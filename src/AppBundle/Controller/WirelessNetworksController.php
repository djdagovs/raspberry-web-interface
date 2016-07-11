<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class WirelessNetworksController extends Controller
{
	/**
     * @Route("/wireless-networks", name="wireless_networks")
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
}
