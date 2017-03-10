<?php
// src/AppBundle/Controller/PVController.php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Entity\Rawdata;
use AppBundle\Entity\Dailydata;
use DateTime;
use DateInterval;
use ArrayObject;

class PVController extends Controller
{
     // returns all events 
     public function GetEvents($datetoshowfrom, $datetoshowto)
     {
       $em = $this->getDoctrine()->getManager();
       $meterdataRepo = $em->getRepository('AppBundle:Rawdata');
       $prevTime = new DateTime($datetoshowfrom);
       $dateTimeEnd = new DateTime($datetoshowto);

       $query = $meterdataRepo->createQueryBuilder('p')
                ->orderBy('p.id', 'ASC')
                ->where('p.measuringtime > :datefrom')
                ->andWhere('p.measuringtime < :dateto')
                ->setParameter('datefrom', $prevTime)
                ->setParameter('dateto', $dateTimeEnd)
                ->getQuery();

        $meterdataAll = $query->getResult();

//	$prevReceiveTime = new DateTime($datetoshowfrom);
	$prevTime = new DateTime($datetoshowfrom);
	$cumulatedCosts = 0;
	$counter = 0;

        foreach ($meterdataAll as $mde) {
            $netflow = $mde->getNetflow();

//            if ($mde->getTimediff() != 0)
//	    {
		$time = $mde->getMeasuringtime();
//echo $time->format('Y:m:d H:i:s');
		if( $netflow > 0 )
		{
// 			$receiveTime = $mde->getMeasuringtime();
			//$mde->setTimediff($receiveTime->getTimestamp() - $prevReceiveTime->getTimestamp());
//			$delta = $receiveTime->getTimestamp() - $prevReceiveTime->getTimestamp();
			$delta = $time->getTimestamp() - $prevTime->getTimestamp();
	                $receive = $netflow/($delta/3600);
	                $mde->setWattReceive($receive);
	                $mde->setWattDeliver(0);
			$mde->setWattConsume($mde->GetSitePower()+$receive);

			$cumulatedCosts += $mde->getTariff() / 100;

//			$prevReceiveTime = $receiveTime;
		}
		else
		{
// 			$deliverTime = $mde->getMeasuringtime();
 			//$mde->setTimediff($deliverTime->getTimestamp() - $prevDeliverTime->getTimestamp());
 			$deltaDeliver = $time->getTimestamp() - $prevTime->getTimestamp();
// 			$deltaDeliver = $deliverTime->getTimestamp() - $prevDeliverTime->getTimestamp();
	                $deliver = -1*$netflow/($deltaDeliver/3600);
	                $mde->setWattDeliver($deliver);
	                $mde->setWattReceive(0);
			$mde->setWattConsume($mde->GetSitePower()-$deliver);
//			$prevDeliverTime = $deliverTime;
		}
		$mde->setCosts($cumulatedCosts);

//	    }
//           else
//           {
//	        $mde->setwattReceive(-1);
//                $mde->setwattDeliver(-1);
//	    }
		$mde->SetJSTimestamp($time->getTimestamp()*1000 + (60*60*1000)); // convert from php to js, add 1 hour
            $prevTime = $time;
        }
        return $meterdataAll;
     }

     /**
     * @Route("/pvrange/{datetoshowfrom}/{datetoshowto}/")
     */

    public function PVDataAction($datetoshowfrom, $datetoshowto)
    {
        $events = PVController::GetEvents($datetoshowfrom, $datetoshowto);
	$pvdata = array();

	$pvdata['events']   = $events;
	$pvdata['consume']  = PVController::ShowConsume();
	$pvdata['deliver']  = PVController::ShowDeliver();
	$pvdata['site']     = PVController::ShowSite();
	$pvdata['receive']  = PVController::ShowReceive();
//	$pvdata['costs']    = PVController::ShowCosts($datetoshowfrom, $datetoshowto);

        return $this->render(
                'pv/dashboard.html.twig',
                array('pvdata' => $pvdata));
    }

     /**
     * @Route("/pv", name="_dashboard")
     */

    public function ShowPVDashboard()
    {
	$todayMorning = new DateTime('today');
	$todayMorning->add(new DateInterval('PT6H')); // + 6h
	$todayMorningString = $todayMorning->format("Y-m-d h:i:s");
	return PVController::PVDataAction($todayMorningString,'NOW');
    }


     // returns all data from one month (format of month 2016-10)
     public function GetMonthData($month)
     {
       $em = $this->getDoctrine()->getManager();
       $dailyDataRepo = $em->getRepository('AppBundle:Dailydata');

       $startTime = new DateTime($month);
       $endTime   = clone $startTime;
       $endTime->add(new DateInterval('P1M'));  // + 1 Monat

       $query = $dailyDataRepo->createQueryBuilder('p')
                ->orderBy('p.id', 'ASC')
                ->where('p.date >= :datefrom')
                ->andWhere('p.date < :dateto')
                ->setParameter('datefrom', $startTime)
                ->setParameter('dateto', $endTime)
                ->getQuery();

        $dailyDataAll = $query->getResult();

	foreach($dailyDataAll as $dda)
	{
//		$time = $dda->getDate();
//echo $time->format('Y:m:d H:i:s');
		$dda->SetVerbrauch($dda->GetBezug() + $dda->GetProduktion() - $dda->GetLieferung());
	}

        return $dailyDataAll;
     }

     // returns all data from one year (format of year 2016)
     public function GetYearData($year)
     {
	$monthlyDataAll = array();
	for ($i = 1; $i <= 12; $i++ )
       	{
		$monthString = sprintf('%d-%02d', $year, $i); // should create 2016-11
		$dayDataOfMonth = PVController::GetMonthData($monthString);
		$tempData = new DailyData();
		$tempVerbrauch = 0;
		$tempBezug = 0;
		$tempLieferung = 0;
		$tempProduktion = 0;
		$tempEinnahmen = 0;
		$tempAusgaben = 0;
		foreach ($dayDataOfMonth as $ddom)
		{
			$tempVerbrauch += $ddom->GetVerbrauch();
			$tempBezug += $ddom->GetBezug();
			$tempLieferung += $ddom->GetLieferung();
			$tempProduktion += $ddom->GetProduktion();
			$tempEinnahmen += $ddom->GetEinnahmen();
			$tempAusgaben += $ddom->GetAusgaben();
		}
		$tempData->SetVerbrauch($tempVerbrauch);
		$tempData->SetBezug($tempBezug);
		$tempData->SetLieferung($tempLieferung);
		$tempData->SetProduktion($tempProduktion);
		$tempData->SetEinnahmen($tempEinnahmen);
		$tempData->SetAusgaben($tempAusgaben);

		$tempData->SetDate($monthString);

		$monthlyDataAll[]=$tempData;
	}

        return $monthlyDataAll;
     }

     /**
     * @Route("/pv/month/{month}", name="_monthselect")
     */
    public function PVMonthAction($month)
    {
	$monthdata = PVController::GetMonthData($month);

        return $this->render(
                'pv/month.html.twig',
                array('monthdata' => $monthdata));
    }

     /**
     * @Route("/pv/select/{year}", name="_yearselect")
     */
    public function PVYearAction($year)
    {
	$yeardata = PVController::GetYearData($year);
        return $this->render(
                'pv/year.html.twig',
                array('yeardata' => $yeardata));
    }

     /**
     * @Route("/pv/month", name="_month")
     */
    public function ShowPVMonth()
    {
	$today = new DateTime('today');
	$actualMonth = $today->format("Y-m"); // 2016-11
	return PVController::PVMonthAction($actualMonth);
    }

     /**
     * @Route("/pv/year", name="_year")
     */
    public function ShowPVYear()
    {
	$today = new DateTime('today');
	$actualYear = $today->format("Y"); // 2016
	return PVController::PVYearAction($actualYear);
    }

     /**
     * @Route("/show/cost/")
     */
    public function ShowCosts($datetoshowfrom, $datetoshowto)
    {

       $em = $this->getDoctrine()->getManager();
       $meterdataRepo = $em->getRepository('AppBundle:Rawdata');


//       $dailyDataRepo = $em->getRepository('AppBundle:Dailydata');

//       $startTime = new DateTime($month);
//       $endTime   = clone $startTime;
//       $endTime->add(new DateInterval('P1M'));  // + 1 Monat

	$today = new DateTime('today');
	$todayString = $today->format("Y-m-d h:i:s");
//echo $today->format('Y:m:d H:i:s');


       $query = $meterdataRepo->createQueryBuilder('p')
                ->where('p.measuringtime >= :datefrom')
                ->andWhere('p.measuringtime < :dateto')
                ->setParameter('datefrom', $todayString)
                ->setParameter('dateto', $datetoshowto)
                ->getQuery();

        $meterdataAll = $query->getResult();


	$dailyCosts = 0;
        foreach ($meterdataAll as $mde) {
	        $netflow = $mde->getNetflow();

        	    if ($netflow < -1)
	            {    // transmit
//        	        $transmitEnergyHighTariff += 10;
//                	$transmitPriceHighTariff += $mde->getTariff() / 100;
;
		    }
	            else
	            {   // receive
//	                $receiveEnergyHighTariff += 10;
	                $dailyCosts += $mde->getTariff() / 100; //TODO: wird hier abgerundet?
		    }
	}

        return $dailyCosts;
    }

    public function ShowReceive()
    {
       $em = $this->getDoctrine()->getManager();
       $meterdataRepo = $em->getRepository('AppBundle:Rawdata');

	$query = $meterdataRepo->createQueryBuilder('p')
    		->select('p')
		->orderBy('p.id', 'DESC')
		->setMaxResults(2)
		->getQuery();

        $meterdataAll = $query->getResult();

	$time1 = $meterdataAll[0]->getMeasuringtime()->getTimestamp();
	$time2 = $meterdataAll[1]->getMeasuringtime()->getTimestamp();

	$netflow2 = $meterdataAll[1]->getNetflow();
	if ($netflow2 > 0)
		$value = $netflow2 / ( ($time1 - $time2) / 3600 );
	else
		$value = 0;

        return $value;
    }

    public function ShowDeliver()
    {
       $em = $this->getDoctrine()->getManager();
       $meterdataRepo = $em->getRepository('AppBundle:Rawdata');

	$query = $meterdataRepo->createQueryBuilder('p')
    		->select('p')
		->orderBy('p.id', 'DESC')
		->setMaxResults(2)
		->getQuery();

        $meterdataAll = $query->getResult();

	$time1 = $meterdataAll[0]->getMeasuringtime()->getTimestamp();
	$time2 = $meterdataAll[1]->getMeasuringtime()->getTimestamp();

	$netflow2 = $meterdataAll[1]->getNetflow();
	if ($netflow2 < 0)
		$value = -1 * ( $netflow2 / ( ($time1 - $time2) / 3600 ) ); 
	else
		$value = 0;

        return $value;
    }


    public function ShowSite()
    {
       $em = $this->getDoctrine()->getManager();
       $meterdataRepo = $em->getRepository('AppBundle:Rawdata');

	$query = $meterdataRepo->createQueryBuilder('p')
    		->select('p')
		->orderBy('p.id', 'DESC')
		->setMaxResults(1)
		->getQuery();

        $meterdataAll = $query->getResult();

	$value = $meterdataAll[0]->GetSitePower();

        return $value;
    }

    public function ShowConsume()
    {

       $em = $this->getDoctrine()->getManager();
       $meterdataRepo = $em->getRepository('AppBundle:Rawdata');

	$query = $meterdataRepo->createQueryBuilder('p')
    		->select('p')
		->orderBy('p.id', 'DESC')
		->setMaxResults(2)
		->getQuery();

        $meterdataAll = $query->getResult();

	$time1 = $meterdataAll[0]->getMeasuringtime()->getTimestamp();
	$time2 = $meterdataAll[1]->getMeasuringtime()->getTimestamp();

	$netflow2 = $meterdataAll[1]->getNetflow();
	if ($netflow2 < 0)
	{
		$wattDeliver = -1 * ( $netflow2 / ( ($time1 - $time2) / 3600 ) ); 
		$value = $meterdataAll[1]->getSitePower() - $wattDeliver;
	}
	else
	{
		$wattReceive = $netflow2 / ( ($time1 - $time2) / 3600 ); 
		if ($meterdataAll[1]->getSitePower() == -1 )
			$value = $wattReceive;
		else
			$value = $meterdataAll[1]->getSitePower() + $wattReceive;
	}
        return $value;
    }
}
