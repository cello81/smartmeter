<?php
// src/AppBundle/Controller/InsertController.php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Entity\Rawdata;
use AppBundle\Entity\Dailydata;
use DateTime;
use DateInterval;

class InsertController extends Controller
{
    /** 
    * @Route("/insert/dailydata/{date}/{dailysitepower}")
    */
    public function insertDailyDataAction($date, $dailysitepower)
    {
	$dailydata = new Dailydata();
        $startTime = new DateTime($date);

        $endTime = clone $startTime;
        $endTime->add(new DateInterval('P1D'));

	$dailydata->setDate($startTime);
//echo $startTime->format('Y-m-d H:i:s');
//echo $endTime->format('Y-m-d H:i:s');
	$em = $this->getDoctrine()->getManager();
	$meterdataRepo = $em->getRepository('AppBundle:Rawdata');

       $query = $meterdataRepo->createQueryBuilder('p')
                ->orderBy('p.id', 'ASC')
                ->where('p.measuringtime > :datefrom')
                ->andWhere('p.measuringtime < :dateto')
                ->setParameter('datefrom', $startTime)
                ->setParameter('dateto', $endTime)
                ->getQuery();

        $meterdataAll = $query->getResult();

        $transmitEnergy = 0;
        $receiveEnergy = 0;
        $transmitPrice = 0;
        $receivePrice = 0;

        foreach ($meterdataAll as $mde) {
            $netflow = $mde->getNetflow();

           if ($netflow < 0 )
            {    // transmit
		$transmitEnergy += 10;
                $transmitPrice += $mde->getTariff() / 100; // Preis ist pro kWh hinterlegt, Abrechnung pro 10Wh
            }
            else
            {   // receive
                $receiveEnergy += 10;
                $receivePrice += $mde->getTariff() / 100;
            }
        }

	$dailydata->setEinnahmen($transmitPrice);
	$dailydata->setAusgaben($receivePrice);
	$dailydata->setBezug($receiveEnergy);
	$dailydata->setLieferung($transmitEnergy);
	$dailydata->setProduktion($dailysitepower);

	$em->persist($dailydata);
	$em->flush();

        return new Response(
            '<html><body>Save successful!</body></html>' );
     }

     /**
     * @Route("/insert/meterdata/{sitepower}/{netflow}")
     */
    public function meterdataAction($sitepower,$netflow)
    {
        $rawdata = new Rawdata();
        $time = date("Y-m-d H:i:s");
	$actualTime = new DateTime("now");
	$rawdata->setMeasuringtime($actualTime);
	$rawdata->setSitepower($sitepower);
	$rawdata->setNetflow($netflow);

	if ($netflow < 0 ) // Rücklieferung
	{
	    $lowTariffDeliver = 5.9;
	    $highTariffDeliver = 5.9;
	    $weekday = $actualTime->format("N");
	    if ($weekday == 6 || $weekday == 7)
	       $tariff = $lowTariffDeliver;
	    else
	    {
	        $hour = $actualTime->format("G");
	        if( $hour >= 7 && $hour < 19 )
	            $tariff = $highTariffDeliver;
	        else
		    $tariff = $lowTariffDeliver;
	    }
	}
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
    }
}
