<?php
// src/AppBundle/Controller/InsertController.php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Entity\Rawdata;
use DateTime;

class InsertController extends Controller
{
     /**
     * @Route("/insert/meterdata/{sitepower}/{netflow}")
     */
    public function meterdataAction($sitepower,$netflow)
    {
        $rawdata = new Rawdata();
        $time = date("Y-m-d H:i:s");
	$actualTime = new DateTime("now");
//        $rawdata->setMeasuringtime(new \DateTime("now"));
	$rawdata->setMeasuringtime($actualTime);
	$rawdata->setSitepower($sitepower);
	$rawdata->setNetflow($netflow);

	if ($netflow < 0 ) // Rücklieferung
	    $tariff = 5.9;
	else
	{
	    $lowTariff = 13;
	    $highTariff = 21;
	    $weekday = $actualTime->format("N");
	    if ($weekday == 6 || $weekday == 7)
	       $tariff = $lowTariff;
	    else
	    {
	        $hour = $actualTime->format("G");
	        if( $hour >= 7 && $hour < 19 )
	            $tariff = $highTariff;
	        else
		    $tariff = $lowTariff;
	    }
	}
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
