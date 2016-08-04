<?php
// src/AppBundle/Entity/Rawdata.php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="rawdata")
 */
class Rawdata
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $measuringtime;

    /**
     * @ORM\Column(type="integer")
     */
    private $sitepower;

    /** 
     * @ORM\Column(type="integer")
     */
    private $netflow;

    /**
     * @ORM\Column(type="decimal", scale=2)
     */
    private $tariff;


    private $timediff;
    private $wattnet;
    public function getTimediff()
    {
        return $this->timediff;
    }

    public function getWattnet()
    {
        return $this->wattnet;
    }

    public function setTimediff($timediff)
    {
        $this->timediff = $timediff;
	return $this;
    }

    public function setWattnet($wattnet)
    {
         $this->wattnet = $wattnet;
 	 return $this;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set measuringtime
     *
     * @param \DateTime $measuringtime
     *
     * @return Rawdata
     */
    public function setMeasuringtime($measuringtime)
    {
        $this->measuringtime = $measuringtime;

        return $this;
    }

    /**
     * Get measuringtime
     *
     * @return \DateTime
     */
    public function getMeasuringtime()
    {
        return $this->measuringtime;
    }

    /**
     * Set sitepower
     *
     * @param integer $sitepower
     *
     * @return Rawdata
     */
    public function setSitepower($sitepower)
    {
        $this->sitepower = $sitepower;

        return $this;
    }

    /**
     *
     * Get sitepower
     *
     * @return integer
     */
    public function getSitepower()
    {
 	return $this->sitepower;
    }

    /**
     * Set Netflow
     *
     * @param integer $netflow
     *
     * @return Rawdata
     */
    public function setNetflow($netflow)
    {
        $this->netflow = $netflow;

	return $this->netflow;
    }

    /**
     * Get netflow
     *
     * @return integer
     */
    public function getNetflow()
    {
        return $this->netflow;
    }

    /**
     * Set tariff
     *
     * @param string $tariff
     *
     * @return Rawdata
     */
    public function setTariff($tariff)
    {
        $this->tariff = $tariff;

        return $this;
    }

    /**
     * Get tariff
     *
     * @return string
     */
    public function getTariff()
    {
        return $this->tariff;
    }
}
