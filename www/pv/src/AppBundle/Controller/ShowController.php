<?php
// src/AppBundle/Controller/ShowController.php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Entity\Rawdata;
use DateTime;
use DateInterval;

class ShowController extends Controller
{
    public function GetValuesOfDay($date)
    {
       $em = $this->getDoctrine()->getManager();
       $meterdataRepo = $em->getRepository('AppBundle:Rawdata');

//       $startTime = new DateTime($date);
	$startTime = $date;
	$endTime = clone $startTime;
	$endTime->add(new DateInterval('P1D')); 
//echo $date->format('Y:m:d h:i:s'); 

	$dailyData = array();
	$dailyData['date'] = $date->format('d m Y');

//echo $startTime->format('Y-m-d H:i:s');
//echo $endTime->format('Y-m-d H:i:s');
       $query = $meterdataRepo->createQueryBuilder('p')
                ->orderBy('p.id', 'ASC')
                ->where('p.measuringtime > :datefrom')
                ->andWhere('p.measuringtime < :dateto')
                ->setParameter('datefrom', $startTime)
                ->setParameter('dateto', $endTime)
                ->getQuery();

        $meterdataAll = $query->getResult();
//        $transmitEnergyLowTariff = 0;
//        $consumeEnergyLowTariff = 0;
        $transmitEnergyHighTariff = 0;
  	$receiveEnergyHighTariff = 0;

        $transmitPriceHighTariff = 0;
        $receivePriceHighTariff = 0;

	$consumeEnergy = 0;
	$siteEnergy = 0;

        foreach ($meterdataAll as $mde) {
            $time = $mde->getMeasuringtime();
            $netflow = $mde->getNetflow();

            if ($netflow < 0)
            {    // transmit
                $transmitEnergyHighTariff += 10;
                $transmitPriceHighTariff += $mde->getTariff() / 100;
	    }
            else
            {   // receive
                $receiveEnergyHighTariff += 10;
                $receivePriceHighTariff += $mde->getTariff() / 100;
	    }
            $mde->setTimediff($time->getTimestamp() - $startTime->getTimestamp());

            $startTime = $time;
        }
	$dailyData['recEnHiTar'] = $receiveEnergyHighTariff;
	$dailyData['recPrHiTar'] = $receivePriceHighTariff;
	$dailyData['traEnHiTar'] = $transmitEnergyHighTariff;
	$dailyData['traPrHiTar'] = $transmitPriceHighTariff;
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
			$monthlyData[$i] = ShowController::GetValuesOfDay($month);
			$month->add($interval);
		}

	        return $this->render(
        	        'show/monthdiagram.html.twig',
                	array('monthlyData' => $monthlyData));
	}



     public function GetDataFromQuery($datetoshowfrom, $datetoshowto)
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
//        $transmitEnergyLowTariff = 0;
        $transmitEnergyHighTariff = 0;
//        $consumeEnergyLowTariff = 0;
        $consumeEnergyHighTariff = 0;
        
        $consumePriceHighTariff = 0;
        $transmitPriceHighTariff = 0;

        foreach ($meterdataAll as $mde) {
            $time = $mde->getMeasuringtime();
            $netflow = $mde->getNetflow();

            if ($netflow < 0 )
            {    // transmit
                $transmitEnergyHighTariff += 10;
                $transmitPriceHighTariff += $mde->getTariff() / 100;
	    }
            else
            {   // consume
                $consumeEnergyHighTariff += 10;
                $consumePriceHighTariff += $mde->getTariff() / 100;
	    }
            $mde->setTimediff($time->getTimestamp() - $prevTime->getTimestamp());

            if ($mde->getTimediff() != 0)
                $mde->setwattNet($netflow/($mde->GetTimediff()/3600));
            else
                $mde->setwattNet(-1);
            $prevTime = $time;
        }
	if (0)
	{
		echo "consumeEnergy:  $consumeEnergyHighTariff Wh \n";
		echo "consumePrice:   $consumePriceHighTariff Rp. \n";
		echo "transmitEnergy: $transmitEnergyHighTariff Wh \n";
		echo "transmitPrice:  $transmitPriceHighTariff Rp. \n";
	}
        return $meterdataAll;
     }

     /**
     * @Route("/show/meterdata/{datetoshowfrom}/{datetoshowto}/")
     */

    public function ShowMeterdataAction($datetoshowfrom, $datetoshowto)
    {
        $meterdataAll = ShowController::GetDataFromQuery($datetoshowfrom, $datetoshowto);

        return $this->render(
                'show/meterentries.html.twig',
                array('allmeterdata' => $meterdataAll));
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
     * @Route("/show/diagram/{datetoshowfrom}/{datetoshowto}/")
     */

    public function ShowDiagramAction($datetoshowfrom, $datetoshowto)
    {
        $meterdataAll = ShowController::GetDataFromQuery($datetoshowfrom, $datetoshowto);

        return $this->render(
                'show/diagram.html.twig',
                array('allmeterdata' => $meterdataAll));
    }


     /**
     * @Route("/show/meterdata/")
     */

    public function ShowMeterdataActionToday()
    {
	return ShowController::ShowMeterdataAction('today','NOW');
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

     /**
     * @Route("/show/value/receive/")
     */
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

        return $this->render(
                'show/value.html.twig',
                array('value' => $value));
    }

     /**
     * @Route("/show/value/deliver/")
     */
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

        return $this->render(
                'show/value.html.twig',
                array('value' => $value));
    }


     /**
     * @Route("/show/value/site/")
     */
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

        return $this->render(
                'show/value.html.twig',
                array('value' => $value));
    }


     /**
     * @Route("/show/value/consume/")
     */
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
        return $this->render(
                'show/value.html.twig',
                array('value' => $value));
    }


}
