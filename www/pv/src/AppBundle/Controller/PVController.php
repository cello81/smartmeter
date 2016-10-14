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

        foreach ($meterdataAll as $mde) {
            $time = $mde->getMeasuringtime();
            $netflow = $mde->getNetflow();

            $mde->setTimediff($time->getTimestamp() - $prevTime->getTimestamp());

            if ($mde->getTimediff() != 0)
	    {
		if( $netflow > 0 )
		{
	                $receive = $netflow/($mde->GetTimediff()/3600);
	                $mde->setWattReceive($receive);
	                $mde->setWattDeliver(0);
			$mde->setWattConsume($mde->GetSitePower()+$receive);
		}
		else
		{
	                $deliver = -1*$netflow/($mde->GetTimediff()/3600);
	                $mde->setWattDeliver($deliver);
	                $mde->setWattReceive(0);
			$mde->setWattConsume($mde->GetSitePower()-$deliver);
		}
	    }
            else
            {
	        $mde->setwattReceive(-1);
                $mde->setwattDeliver(-1);
	    }
            $prevTime = $time;
        }
        return $meterdataAll;
     }

     /**
     * @Route("/pv/{datetoshowfrom}/{datetoshowto}/")
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

        return $this->render(
                'pv/dashboard.html.twig',
                array('pvdata' => $pvdata));
    }

     /**
     * @Route("/pv", name="_dashboard")
     */

    public function ShowPVDashboard()
    {
	return PVController::PVDataAction('today','NOW');
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
		$dda->SetVerbrauch($dda->GetBezug() + $dda->GetProduktion() - $dda->GetLieferung());
	}

        return $dailyDataAll;
     }

     /**
     * @Route("/pv/month/{month}")
     */
    public function PVMonthAction($month)
    {
	$monthdata = PVController::GetMonthData($month);

        return $this->render(
                'pv/month.html.twig',
                array('monthdata' => $monthdata));
    }

     /**
     * @Route("/pv/month", name="_month")
     */
    public function ShowPVMonth()
    {
	return PVController::PVMonthAction("2016-10");
    }

     /**
     * @Route("/show/cost/")
     */
/*    public function ShowaTodayCost()
    {
	$value = 500;
        return $this->render(
                'show/value.html.twig',
                array('value' => $value));
    }
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
