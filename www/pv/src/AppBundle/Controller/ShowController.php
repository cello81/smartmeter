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

     /**
     * @Route("/show/meterdata/{datetoshowfrom}/{datetoshowto}/")
     */

    public function ShowMeterdataAction($datetoshowfrom, $datetoshowto)
    {
       $em = $this->getDoctrine()->getManager();
       $meterdataRepo = $em->getRepository('AppBundle:Rawdata');

       $prevTime = new DateTime($datetoshowfrom);
//echo $prevTime->format('Y-m-d H:i:s');
       if ($datetoshowto == 1)
       {
           $dateTimeEnd = new DateTime($datetoshowfrom);
       }
       else
       {
           $dateTimeEnd = new DateTime($datetoshowto);
       }
//echo $dateTimeEnd->format('Y-m-d H:i:s');

       $dateTimeEnd->add(new DateInterval('P1D'));  // + 1 Tag 
//echo $dateTimeEnd->format('Y-m-d H:i:s');

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
echo "consumeEnergy:  $consumeEnergyHighTariff Wh \n";
echo "consumePrice:   $consumePriceHighTariff Rp. \n";
echo "transmitEnergy: $transmitEnergyHighTariff Wh \n";
echo "transmitPrice:  $transmitPriceHighTariff Rp. \n";

        return $this->render(
                'show/meterentries.html.twig',
                array('allmeterdata' => $meterdataAll));

    }

     /**
     * @Route("/show/meterdata/")
     */

    public function ShowMeterdataActionToday()
    {
	return ShowController::ShowMeterdataAction('today',1);
    }

     /**
     * @Route("/show/meterdata/all/")
     */
    public function ShowMeterdataActionAll()
    {
        return ShowController::ShowMeterdataAction('2016-08-04','NOW');
    }
}
