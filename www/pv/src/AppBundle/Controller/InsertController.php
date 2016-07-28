<?php
// src/AppBundle/Controller/InsertController.php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Entity\Rawdata;

class InsertController extends Controller
{
     /**
     * @Route("/insert/meterdata/{sitepower}/{netflow}/{tariff}")
     */
    public function meterdataAction($sitepower,$netflow,$tariff)
    {
        $rawdata = new Rawdata();
        $time = date("Y-m-d H:i:s");
        $rawdata->setMeasuringtime(new \DateTime("now"));
	$rawdata->setSitepower($sitepower);
	$rawdata->setNetflow($netflow);
	$rawdata->setTariff($tariff);

	$em = $this->getDoctrine()->getManager();
	$em->persist($rawdata);
	$em->flush();

        return new Response(
            '<html><body>Saved entry with the following values: Measuring Time: '.$time.', Site Power: '.$sitepower.', Netz: '.$netflow.', Tarif: '.$tariff.'</body></html>'
        );
/*
	// render(): a shortcut that does the same as above
	return $this->render(
	 'lucky/number.html.twig',
	 array('luckyNumberList' => $numbersList)
          );
*/    }

}
