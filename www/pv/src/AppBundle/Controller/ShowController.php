<?php
// src/AppBundle/Controller/ShowController.php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Entity\Rawdata;

class ShowController extends Controller
{
     /**
     * @Route("/show/meterdata/")
     */
    public function ShowMeterdataAction()
    {
        $em = $this->getDoctrine()->getManager();
	$meterdataRepo = $em->getRepository('AppBundle:Rawdata');
        $meterdataAll = $meterdataRepo->findAll();

	$response = new Response();

/*	$response->setContent('<html><body><h1>Hello world!</h1></body></html>');*/
	$response->setStatusCode(Response::HTTP_OK);

	// set a HTTP response header
	$response->headers->set('Content-Type', 'text/html');

	// print the HTTP headers followed by the content
//	$response->send();

	$htmltext = "<html><body><h1>Alle Einträge</h1><table>";
        foreach ($meterdataAll as $mde) {
           $id = $mde->getId();
           $time = $mde->getMeasuringtime();
           $sitepower = $mde->getSitepower();
           $netflow = $mde->getNetflow();
           $tariff = $mde->getTariff();
           $htmltext .= "<tr><td>ID: '.$id.'</td><td>Power: '.$sitepower.'</td><td>Netz: '.$netflow.'</td><td>Tarif: '.$tariff</td></tr>";
	}
        $htmltext .= "</table></body></html>";

 	$response->setContent($htmltext);

	return $response;
    }
}
