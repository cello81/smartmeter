<?php
// src/AppBundle/Controller/PVController.php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Entity\Rawdata;
use DateTime;
use DateInterval;

class PVController extends Controller
{
    public function GetValuesOfDay($date)
    {
//$logdate = new DateTime('now');
//echo $logdate->format('Y:m:d h:i:s'); 
//echo 'start';
       $em = $this->getDoctrine()->getManager();
       $meterdataRepo = $em->getRepository('AppBundle:Rawdata');
//$logdate = new DateTime('now');
//echo $logdate->format('Y:m:d h:i:s'); 
//echo 'getrepo';

//       $startTime = new DateTime($date);
	$startTime = $date;
	$endTime = clone $startTime;
	$endTime->add(new DateInterval('P1D')); 
//echo $date->format('Y:m:d h:i:s'); 

	$dailyData = array();
	$dailyData['date'] = $date->format('d m Y');

//echo $startTime->format('Y-m-d H:i:s');
//echo $endTime->format('Y-m-d H:i:s');
//$logdate = new DateTime('now');
//echo $logdate->format('Y:m:d h:i:s'); 
//echo 'startquery';
// query id von datum von und datum bis, mit maxentry = 1
       $query = $meterdataRepo->createQueryBuilder('p')
                ->orderBy('p.id', 'ASC')
                ->where('p.measuringtime > :datefrom')
                ->andWhere('p.measuringtime < :dateto')
                ->setParameter('datefrom', $startTime)
                ->setParameter('dateto', $endTime)
                ->getQuery();

        $meterdataAll = $query->getResult();
//$logdate = new DateTime('now');
//echo $logdate->format('Y:m:d h:i:s'); 
//echo 'gotquery';
//        $transmitEnergyLowTariff = 0;
//        $consumeEnergyLowTariff = 0;
        $transmitEnergyHighTariff = 0;
  	$receiveEnergyHighTariff = 0;

//        $transmitPriceHighTariff = 0;
//        $receivePriceHighTariff = 0;

//	$consumeEnergy = 0;
//	$siteEnergy = 0;
//$logdate = new DateTime('now');
//echo $logdate->format('Y:m:d h:i:s'); 
//echo 'startforeach';

        foreach ($meterdataAll as $mde) {
 //           $time = $mde->getMeasuringtime();
//            $netflow = $mde->getNetflow();

//            if ($netflow < 0 )
            {    // transmit
//                $transmitEnergyHighTariff += 10;
//                $transmitPriceHighTariff += $mde->getTariff() / 100;
	    }
//            else
            {   // receive
//                $receiveEnergyHighTariff += 10;
//                $receivePriceHighTariff += $mde->getTariff() / 100;
	    }
//            $mde->setTimediff($time->getTimestamp() - $startTime->getTimestamp());

 //           $startTime = $time;
        }
//$logdate = new DateTime('now');
//echo $logdate->format('Y:m:d h:i:s'); 
//echo 'endforeach';

	$dailyData['recEnHiTar'] = $receiveEnergyHighTariff;
//	$dailyData['recPrHiTar'] = $receivePriceHighTariff;
	$dailyData['traEnHiTar'] = $transmitEnergyHighTariff;
//	$dailyData['traPrHiTar'] = $transmitPriceHighTariff;
//$logdate = new DateTime('now');
//echo $logdate->format('Y:m:d h:i:s'); 
//echo 'end<br />';
	return $dailyData;
     }

     /**
     * @Route("/show/day/{date}/")
     */
     public function GetDailyInformation($date)
     {
	$dailyData = ShowController::GetValuesOfDay($date);
        return $this->render(
                'show/daily.html.twig',
                array('dailyData' => $dailyData));
     }

	/**
    	 * @Route("/show/monthdays/{date}/")
     	*/
	public function GetDailyDataByMonth($date)
	{
		$month = new DateTime($date);
		$interval = new DateInterval('P1D');
//		$monthlyData = array();
		$monthvalue = $month->format('m');
		$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $monthvalue, $month->format('Y'));
		for ($i = 1; $i <= $daysInMonth; $i++ )
		{
//			echo $month->format('Y:m:d h:i:s'); 
//			echo "<br />";
			$monthlyData[$i] = PVController::GetValuesOfDay($month);
			$month->add($interval);
		}
//$logdate = new DateTime('now');
//echo $logdate->format('Y:m:d h:i:s'); 
//echo 'mannooo';

	        return $this->render(
        	        'pv/month.html.twig',
                	array('monthData' => $monthlyData));
	}


     // returns all receive events
     public function GetReceiveEvents($datetoshowfrom, $datetoshowto)
     {
       $em = $this->getDoctrine()->getManager();
       $meterdataRepo = $em->getRepository('AppBundle:Rawdata');

       $prevTime = new DateTime($datetoshowfrom);
       $dateTimeEnd = new DateTime($datetoshowto);

       $query = $meterdataRepo->createQueryBuilder('p')
                ->orderBy('p.id', 'ASC')
                ->where('p.measuringtime > :datefrom')
                ->andWhere('p.measuringtime < :dateto')
                ->andWhere('p.netflow > 0')
                ->setParameter('datefrom', $prevTime)
                ->setParameter('dateto', $dateTimeEnd)
                ->getQuery();

        $meterdataAll = $query->getResult();

        foreach ($meterdataAll as $mde) {
            $time = $mde->getMeasuringtime();
            $netflow = $mde->getNetflow();

            $mde->setTimediff($time->getTimestamp() - $prevTime->getTimestamp());

            if ($mde->getTimediff() != 0)
                $mde->setwattNet($netflow/($mde->GetTimediff()/3600));
            else
                $mde->setwattNet(-1);

            $prevTime = $time;
        }
        return $meterdataAll;
     }


     // returns all deliver events
     public function GetDeliverEvents($datetoshowfrom, $datetoshowto)
     {
       $em = $this->getDoctrine()->getManager();
       $meterdataRepo = $em->getRepository('AppBundle:Rawdata');

       $prevTime = new DateTime($datetoshowfrom);
       $dateTimeEnd = new DateTime($datetoshowto);

       $query = $meterdataRepo->createQueryBuilder('p')
                ->orderBy('p.id', 'ASC')
                ->where('p.measuringtime > :datefrom')
                ->andWhere('p.measuringtime < :dateto')
                ->andWhere('p.netflow < 0')
                ->setParameter('datefrom', $prevTime)
                ->setParameter('dateto', $dateTimeEnd)
                ->getQuery();

        $meterdataAll = $query->getResult();

        foreach ($meterdataAll as $mde) {
            $time = $mde->getMeasuringtime();
            $netflow = $mde->getNetflow();

            $mde->setTimediff($time->getTimestamp() - $prevTime->getTimestamp());

            if ($mde->getTimediff() != 0)
                $mde->setwattNet((-1)*$netflow/($mde->GetTimediff()/3600));
            else
                $mde->setwattNet(-1);

            $prevTime = $time;
        }
        return $meterdataAll;
     }


     /**
     * @Route("/pv/{datetoshowfrom}/{datetoshowto}/")
     */

    public function PVDataAction($datetoshowfrom, $datetoshowto)
    {
        $receiveEvents = PVController::GetReceiveEvents($datetoshowfrom, $datetoshowto);
	$deliverEvents = PVController::GetDeliverEvents($datetoshowfrom, $datetoshowto);
	$pvdata = array();

	$pvdata['receiveEvents'] = $receiveEvents;
	$pvdata['deliverEvents'] = $deliverEvents;
	$pvdata['consume'] = PVController::ShowConsume();
	$pvdata['deliver'] = PVController::ShowDeliver();
	$pvdata['site'] = PVController::ShowSite();
	$pvdata['receive'] = PVController::ShowReceive();

        return $this->render(
                'pv/dashboard.html.twig',
                array('pvdata' => $pvdata));
    }

     /**
     * @Route("/show/diagram/")
     */

    public function ShowDiagramActionToday()
    {
	$meterdataAll = ShowController::GetDataFromQuery('today','NOW');
	
        return $this->render(
                'show/diagram.html.twig',
                array('allmeterdata' => $meterdataAll));
    }

     /**
     * @Route("/pv", name="_dashboard")
     */

    public function ShowPVDashboard()
    {
	return PVController::PVDataAction('today','NOW');
    }

     /**
     * @Route("/pv/month", name="_month")
     */

    public function ShowPVMonth()
    {
	return PVController::GetDailyDataByMonth("2016-10");
    }

     /**
     * @Route("/show/meterdata/all/")
     */
    public function ShowMeterdataActionAll()
    {
        return ShowController::ShowMeterdataAction('2016-08-04','NOW');
    }

     /**
     * @Route("/show/cost/")
     */
    public function ShowTodayCost()
    {
	$value = 500;
        return $this->render(
                'show/value.html.twig',
                array('value' => $value));
    }

    /**
     * @Route("/show/cost/{costfrom}/{costto}")
     */
    public function ShowCostFromTo($costfrom, $costto)
    {
	$value = 3300;
        return $this->render(
                'show/value.html.twig',
                array('value' => $value));
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
