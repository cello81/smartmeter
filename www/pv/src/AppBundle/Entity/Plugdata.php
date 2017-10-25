<?php
// src/AppBundle/Entity/Plugdata.php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="plugdata")
 */
class Plugdata
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

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
     * @ORM\Column(type="integer")
     */
    private $counter;
    /**
     * @ORM\Column(type="string", length=5)
     */
    private $state;

     /**
     * Set counter
     *
     * @param integer $counter
     *
     * @return Plugdata
     */
    public function setCounter($counter)
    {
        $this->counter = $counter;

        return $this;
    }

     /**
     * Increment counter
     *
     * @return Plugdata
     */
    public function incCounter()
    {
        $this->counter++;

        return $this;
    }

    /**
     * Get counter
     *
     * @return integer
     */
    public function getCounter()
    {
        return $this->counter;
    }

     /**
     * Set state
     *
     * @param string $state
     *
     * @return Plugdata
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state
     *
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @ORM\Column(type="integer")
     */
    private $powerLimit;
    /**
     * @ORM\Column(type="string", length=5)
     */
    private $sparkOnline;

     /**
     * Set power limit
     *
     * @param integer $powerLimit
     *
     * @return Plugdata
     */
    public function setPowerLimit($powerLimit)
    {
        $this->powerLimit = $powerLimit;

        return $this;
    }

    /**
     * Get powerLimit
     *
     * @return integer
     */
    public function getPowerLimit()
    {
        return $this->powerLimit;
    }

     /**
     * Set sparkOnline
     *
     * @param string $sparkOnline
     *
     * @return Plugdata
     */
    public function setSparkOnline($state)
    {
        $this->sparkOnline = $state;

        return $this;
    }

    /**
     * Get sparkOnline
     *
     * @return string
     */
    public function getSparkOnline()
    {
        return $this->sparkOnline;
    }



}
