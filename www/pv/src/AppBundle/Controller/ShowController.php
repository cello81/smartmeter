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
     public function GetDataFromQuery($datetoshowfrom, $datetoshowto)
     {
       $em = $this->getDoctrine()->getManager();
       $meterdataRepo = $em->getRepository('AppBundle:Rawdata');

       $prevTime = new DateTime($datetoshowfrom);
       if ($datetoshowto == 1)
       {
           $dateTimeEnd = new DateTime($datetoshowfrom);
echo "SHOULD never get here! war 1:" .  $dateTimeEnd->format('Y-m-d H:i:s');
       }
       else
       {

           $dateTimeEnd = new DateTime($datetoshowto);

       }

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

	$value = $netflow2 / ( ($time1 - $time2) / 3600 );

        return $this->render(
                'show/value.html.twig',
                array('value' => $value));
    }

     /**
     * @Route("/show/value/deliver/")
     */
    public function ShowDeliver()
    {
	$value = 600;
        return $this->render(
                'show/value.html.twig',
                array('value' => $value));
    }


     /**
     * @Route("/show/value/site/")
     */
    public function ShowSite()
    {
	$value = 700;
        return $this->render(
                'show/value.html.twig',
                array('value' => $value));
    }


     /**
     * @Route("/show/value/consume/")
     */
    public function ShowConsume()
    {
	$value = 800;
        return $this->render(
                'show/value.html.twig',
                array('value' => $value));
    }


}
