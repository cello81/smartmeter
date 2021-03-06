<?php
// src/AppBundle/Entity/Dailydata.php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="dailydata")
 */
class Dailydata
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
    private $date;

    /**
     * @ORM\Column(type="decimal", scale=2)
     */
    private $einnahmen;

    /** 
     * @ORM\Column(type="decimal", scale=2)
     */
    private $ausgaben;

    /**
     * @ORM\Column(type="decimal", scale=2)
     */
    private $bezug;

    /**
     * @ORM\Column(type="decimal", scale=2)
     */
    private $lieferung;

    /**
     * @ORM\Column(type="decimal", scale=2)
     */
    private $produktion;

    /**
     * @ORM\Column(type="decimal", scale=2)
     */
    private $verbrauch;

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
     * Set date
     *
     * @param \DateTime $date
     *
     * @return Dailydata
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set einnahmen
     *
     * @param string $einnahmen
     *
     * @return Dailydata
     */
    public function setEinnahmen($einnahmen)
    {
        $this->einnahmen = $einnahmen;

        return $this;
    }

    /**
     * Get einnahmen
     *
     * @return string
     */
    public function getEinnahmen()
    {
        return $this->einnahmen;
    }

    /**
     * Set ausgaben
     *
     * @param string $ausgaben
     *
     * @return Dailydata
     */
    public function setAusgaben($ausgaben)
    {
        $this->ausgaben = $ausgaben;

        return $this;
    }

    /**
     * Get ausgaben
     *
     * @return string
     */
    public function getAusgaben()
    {
        return $this->ausgaben;
    }

    /**
     * Set bezug
     *
     * @param string $bezug
     *
     * @return Dailydata
     */
    public function setBezug($bezug)
    {
        $this->bezug = $bezug;

        return $this;
    }

    /**
     * Get bezug
     *
     * @return string
     */
    public function getBezug()
    {
        return $this->bezug;
    }

    /**
     * Set lieferung
     *
     * @param string $lieferung
     *
     * @return Dailydata
     */
    public function setLieferung($lieferung)
    {
        $this->lieferung = $lieferung;

        return $this;
    }

    /**
     * Get lieferung
     *
     * @return string
     */
    public function getLieferung()
    {
        return $this->lieferung;
    }

    /**
     * Set produktion
     *
     * @param string $produktion
     *
     * @return Dailydata
     */
    public function setProduktion($produktion)
    {
        $this->produktion = $produktion;

        return $this;
    }

    /**
     * Get produktion
     *
     * @return string
     */
    public function getProduktion()
    {
        return $this->produktion;
    }

    /**
     * Set verbrauch
     *
     * @param string $verbrauch
     *
     * @return Dailydata
     */
    public function setVerbrauch($verbrauch)
    {
        $this->verbrauch = $verbrauch;

        return $this;
    }

    /**
     * Get verbrauch
     *
     * @return string
     */
    public function getVerbrauch()
    {
        return $this->verbrauch;
    }
}
