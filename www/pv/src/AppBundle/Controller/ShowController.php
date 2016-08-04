<?php
// src/AppBundle/Controller/ShowController.php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Entity\Rawdata;
use DateTime;

class ShowController extends Controller
{
     /**
     * @Route("/show/meterdata/")
     */
    public function ShowMeterdataAction()
    {
        $em = $this->getDoctrine()->getManager();
	$meterdataRepo = $em->getRepository('AppBundle:Rawdata');

	$prevTime = new DateTime('2016-08-03');

	$query = $meterdataRepo->createQueryBuilder('p')
   		->orderBy('p.id', 'ASC')
		->where('p.measuringtime > :date')
		->setParameter('date', $prevTime)
		->getQuery();

	$meterdataAll = $query->getResult();

        $prevTime = new DateTime('NOW');

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

	return $this->render(
		'show/meterentries.html.twig',
		array('allmeterdata' => $meterdataAll));
    }
}
