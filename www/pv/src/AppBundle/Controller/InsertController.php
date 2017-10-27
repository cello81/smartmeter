<?php
// src/AppBundle/Controller/InsertController.php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Entity\Rawdata;
use AppBundle\Entity\Dailydata;
use AppBundle\Entity\Plugdata;
use DateTime;
use DateInterval;
use curl;

use Symfony\Component\HttpFoundation\Session\Session;


class InsertController extends Controller
{

     /**
     * @Route("/power/spark/{online}", name="_sparkonline")
     */
	public function powerActionSpark($online)
	{
		$em = $this->getDoctrine()->getManager();
		$plugRepo = $em->getRepository('AppBundle:Plugdata');

		$query = $plugRepo->createQueryBuilder('p')
                ->select('p')
                ->orderBy('p.id', 'DESC')
                ->setMaxResults(1)
                ->getQuery();

	        $plugDataAll = $query->getResult();

		$plugdata = $plugDataAll[0];
		$plugdata->setSparkOnline($online);

		if ($online == "1")
		{
			$plugdata->setState("off");
			$plugdata->setCounter(1);
		}

		$em->persist($plugdata);
		$em->flush();

		return InsertController::powerActionGet();
	}

     /**
     * @Route("/power/limit/{limit}", name="_limit")
     */
	public function powerActionLimit($limit)
	{
		$em = $this->getDoctrine()->getManager();
		$plugRepo = $em->getRepository('AppBundle:Plugdata');

		$query = $plugRepo->createQueryBuilder('p')
                ->select('p')
                ->orderBy('p.id', 'DESC')
                ->setMaxResults(1)
                ->getQuery();

	        $plugDataAll = $query->getResult();

		$plugdata = $plugDataAll[0];
		$plugdata->setPowerLimit($limit);

		// tells Doctrine you want to (eventually) save the Product (no queries yet)
		$em->persist($plugdata);

		// actually executes the queries (i.e. the INSERT query)
		$em->flush();

		return InsertController::powerActionGet();
	}

     /**
     * @Route("/power/init/session")
     */
	public function powerActionInit()
	{
		$em = $this->getDoctrine()->getManager();
		$plugRepo = $em->getRepository('AppBundle:Plugdata');

		$query = $plugRepo->createQueryBuilder('p')
                ->select('p')
                ->orderBy('p.id', 'DESC')
                ->setMaxResults(1)
                ->getQuery();

	        $plugDataAll = $query->getResult();

		$plugdata = $plugDataAll[0];
		$plugdata->setCounter(1);
		$plugdata->setState("off");
		$plugdata->setSparkOnline("0");

		// tells Doctrine you want to (eventually) save the Product (no queries yet)
		$em->persist($plugdata);

		// actually executes the queries (i.e. the INSERT query)
		$em->flush();

        	return new Response(
	            '<html><body>Init successful!</body></html>' );
	}

     /**
     * @Route("/power/get", name="_powerget")
     */
	public function powerActionGet()
	{
		$em = $this->getDoctrine()->getManager();
		$plugRepo = $em->getRepository('AppBundle:Plugdata');

		$query = $plugRepo->createQueryBuilder('p')
                ->select('p')
                ->orderBy('p.id', 'DESC')
                ->setMaxResults(1)
                ->getQuery();

	        $plugDataAll = $query->getResult();

	        $counter = $plugDataAll[0]->getCounter();
	        $state = $plugDataAll[0]->getState();
	        $limit = $plugDataAll[0]->getPowerLimit();
	        $online = $plugDataAll[0]->getSparkOnline();

		$deliver = PVController::ShowDeliver();


	        $plugdata = array();
	        $plugdata['counter'] = $counter;
	        $plugdata['state'] = $state;
	        $plugdata['powerLimit'] = $limit;
	        $plugdata['sparkOnline'] = $online;

	        return $this->render(
        	        'pv/plug.html.twig',
                	array('plugdata' => $plugdata));
	}

     /**
     * @Route("/power/test/{param}")
     */
	public function powerActionTest($param)
	{
		if ($param == "on" )
			return InsertController::powerAction("on");
		if ($param == "off" )
		{
			return InsertController::powerAction("off");
		}
	        return new Response(
        		'<html><body></body></html>' );

	}


     /**
     * @Route("/power/{onoff}")
     */
	public function powerAction($onoff)
	{
		$em = $this->getDoctrine()->getManager();
		$plugRepo = $em->getRepository('AppBundle:Plugdata');

		$query = $plugRepo->createQueryBuilder('p')
                ->select('p')
                ->orderBy('p.id', 'DESC')
                ->setMaxResults(1)
                ->getQuery();

	        $plugDataAll = $query->getResult();

	        $counter = $plugDataAll[0]->getCounter();
	        $state = $plugDataAll[0]->getState();
		$sparkOnline = $plugDataAll[0]->getSparkOnline();
		$limit = $plugDataAll[0]->getPowerLimit();

		$callSpark = 0;

		if ($onoff == "off")
		{
			$counter = 0;
			if ($state == "on")
			{
				$callSpark = 1;
				$state = "off";
			}
		} else
		{
			if ($state == "off")
			{
				$deliver = PVController::ShowDeliver();

				if ($deliver > $limit )
				{
					$counter = $counter + 1;

					if($counter >= 10 )
					{
						$state = "on";
						$counter = 0;
						$callSpark = 1;
					}
				}
			} else
			{
			}
		}

//		$em = $this->getDoctrine()->getManager();
		$plugdata = $plugDataAll[0];

		$plugdata->setCounter($counter);
		$plugdata->setState($state);

		// tells Doctrine you want to (eventually) save the Product (no queries yet)
		$em->persist($plugdata);

		// actually executes the queries (i.e. the INSERT query)
		$em->flush();

		$response = "No function called";
		if ($callSpark == 1 && $sparkOnline == "1")
		{
			InsertController::callSpark($onoff);
		}
	        return new Response(
        		'<html><body>counter: '.$counter.', state: '.$state.', antwort von sparkli: '.$response.'</body></html>' );
	}
 

	/**
	* @Route("powerSwitch/{powerSwitch}", name="_powerSwitch")
	*/
	public function powerSwitch($powerSwitch)
	{

		$em = $this->getDoctrine()->getManager();
		$plugRepo = $em->getRepository('AppBundle:Plugdata');

		$query = $plugRepo->createQueryBuilder('p')
                ->select('p')
                ->orderBy('p.id', 'DESC')
                ->setMaxResults(1)
                ->getQuery();

	        $plugDataAll = $query->getResult();

		$plugdata = $plugDataAll[0];
		$plugdata->setState($powerSwitch);

		// tells Doctrine you want to (eventually) save the Product (no queries yet)
		$em->persist($plugdata);

		// actually executes the queries (i.e. the INSERT query)
		$em->flush();

		InsertController::callSpark($powerSwitch);

		return InsertController::powerActionGet();
	}

	public function callSpark($powerSwitch)
	{
		$url = "https://api.particle.io/v1/devices/53ff6d066667574846572567/power";

		$ch = curl_init($url);
		curl_setopt( $ch, CURLOPT_POST, 1);
		curl_setopt( $ch, CURLOPT_TIMEOUT, 5);
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt( $ch, CURLOPT_HEADER, 0);
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query(array('arg' => $powerSwitch)));
		curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer b44cc030df607284a533a339d9d59f7e209c7fda'));
		$response = curl_exec( $ch );
	}

    /** 
    * @Route("/update/dailydata/{date}")
    */
    public function updateDailyDataAction($date)
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

        $receiveEnergy = 0;
        $receivePrice = 0;

        foreach ($meterdataAll as $mde) {
            $netflow = $mde->getNetflow();

           if ($netflow > 0 )
           {   // receive
                $receiveEnergy += 10;
                $receivePrice += $mde->getTariff() / 100;
           }
        }

	$dailydata->setAusgaben($receivePrice);
	$dailydata->setBezug($receiveEnergy);

	$dailyRepo = $em->getRepository('AppBundle:Dailydata');
	$dailyQuery = $dailyRepo->createQueryBuilder('u')
			->update()
			->set('u.bezug', '?1')
				->setParameter(1,$receiveEnergy)
			->set('u.ausgaben', '?2')
				->setParameter(2,$receivePrice)
			->where('u.date = :mydate')
				->setParameter('mydate', $startTime)
			->getQuery();

	$dailyQuery->execute();

        return new Response(
            '<html><body>Save successful!</body></html>' );
     }

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
        $transmitPrice = 0;

        foreach ($meterdataAll as $mde) {
            $netflow = $mde->getNetflow();

           if ($netflow < 0 )
            {    // transmit
		$transmitEnergy += 10;
                $transmitPrice += $mde->getTariff() / 100; // Preis ist pro kWh hinterlegt, Abrechnung pro 10Wh
            }
        }

	$dailydata->setEinnahmen($transmitPrice);
	$dailydata->setAusgaben(0);
	$dailydata->setBezug(0);
	$dailydata->setVerbrauch(0);
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
//            $session->set('name', 'Drak');
	    $lowTariffDeliver = 5.45;  // 2017
	    $highTariffDeliver = 5.45; // 2017
//	    $lowTariffDeliver = 4.23;  // 2018
//	    $highTariffDeliver = 4.23; // 2018
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
            InsertController::powerAction("off");
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

    /**
     * @Route("/insert/meterdata/{sitepower}/{sitepowerOst}/{sitepowerWest}/{netflow}")
     */
    public function meterdataActionExt($sitepower,$sitepowerOst,$sitepowerWest,$netflow)
    {
        $rawdata = new Rawdata();
        $time = date("Y-m-d H:i:s");
	$actualTime = new DateTime("now");
	$rawdata->setMeasuringtime($actualTime);
	$rawdata->setSitepower($sitepower);
	$rawdata->setSitepowerOst($sitepowerOst);
	$rawdata->setSitepowerWest($sitepowerWest);
	$rawdata->setNetflow($netflow);

	if ($netflow < 0 ) // Rücklieferung
	{
//            InsertController::powerAction("on");
	    $lowTariffDeliver = 5.45;  // 2017
	    $highTariffDeliver = 5.45; // 2017
//	    $lowTariffDeliver = 4.23;  // 2018
//	    $highTariffDeliver = 4.23; // 2018

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

	if ( $netflow < 0 ) // Rücklieferung
	{
		InsertController::powerAction("on");
	} else
	{
		InsertController::powerAction("off");
	}

        return new Response(
            '<html><body>Saved entry with the following values: Measuring Time: '.$time.', Site Power: '.$sitepower.', Netz: '.$netflow.', Tarif: '.$tariff.'</body></html>'
        );
    }

}
