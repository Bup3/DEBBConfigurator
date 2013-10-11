<?php

namespace Debb\ManagementBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * CoolingDevice
 *
 * @ORM\Table(name="coolingdevice")
 * @ORM\Entity(repositoryClass="Debb\ManagementBundle\Repository\BaseRepository")
 */
class CoolingDevice extends DEBBComplex
{
	/**
	 * @var string
	 *
	 * @Assert\Choice(callback={"Debb\ManagementBundle\Form\CoolingDeviceType", "getClasses"}, message="Choose a valid class.")
	 * @ORM\Column(name="class", type="string", length=255)
	 */
	private $class;

	/**
	 * @var float
	 *
	 * @ORM\Column(name="MaxCoolingCapacity", type="decimal", nullable=true)
	 */
	private $maxCoolingCapacity;

	/**
	 * @var float
	 *
	 * @Assert\NotBlank()
	 * @ORM\Column(name="CoolingCapacityRated", type="decimal")
	 */
	private $coolingCapacityRated;

	/**
	 * @var float
	 *
	 * @ORM\Column(name="MaxAirThroughput", type="decimal", nullable=true)
	 */
	private $maxAirThroughput;

	/**
	 * @var float
	 *
	 * @ORM\Column(name="MaxWaterThroughput", type="decimal", nullable=true)
	 */
	private $maxWaterThroughput;

	/**
	 * @var \Debb\ManagementBundle\Entity\FlowProfile
	 *
	 * @ORM\ManyToOne(targetEntity="Debb\ManagementBundle\Entity\FlowProfile")
	 */
	private $airThroughputProfile;

	/**
	 * @var \Debb\ManagementBundle\Entity\FlowProfile
	 *
	 * @ORM\ManyToOne(targetEntity="Debb\ManagementBundle\Entity\FlowProfile")
	 */
	private $waterThroughputProfile;

	/**
	 * @var \Debb\ManagementBundle\Entity\CoolingEER
	 *
	 * @ORM\OneToMany(targetEntity="Debb\ManagementBundle\Entity\CoolingEER", mappedBy="coolingDevice", cascade={"all"}, orphanRemoval=true)
	 */
	private $energyEfficiencyRatio;

	/**
	 * Components
	 *
	 * @ORM\OneToMany(targetEntity="Debb\ManagementBundle\Entity\Component", cascade={"persist"}, mappedBy="coolingDevice", orphanRemoval=true)
	 *
	 * @var \Debb\ManagementBundle\Entity\Component[]
	 */
	private $components;

	/**
	 * Constructor
	 */
	function __construct()
	{
		$this->energyEfficiencyRatio = new \Doctrine\Common\Collections\ArrayCollection();
		$this->components = new \Doctrine\Common\Collections\ArrayCollection();
	}

	/**
	 * Returns a array for later converting
	 * 
	 * @return array the array for later converting
	 */
	public function getDebbXmlArray()
	{
		$array = parent::getDebbXmlArray();
		$array['Class'] = (string) $this->getClass();
		if ($this->getMaxCoolingCapacity() !== null)
		{
			$array['MaxCoolingCapacity'] = $this->getMaxCoolingCapacity();
		}
		$array['CoolingCapacityRated'] = (string) $this->getCoolingCapacityRated();
		if ($this->getMaxAirThroughput() !== null)
		{
			$array['MaxAirThroughput'] = $this->getMaxAirThroughput();
		}
		if ($this->getMaxWaterThroughput() !== null)
		{
			$array['MaxWaterThroughput'] = $this->getMaxWaterThroughput();
		}
		if ($this->getAirThroughputProfile() !== null)
		{
			$array['AirThroughputProfile'] = $this->getAirThroughputProfile()->getDebbXmlArray();
		}
		if ($this->getWaterThroughputProfile() !== null)
		{
			$array['WaterThroughputProfile'] = $this->getWaterThroughputProfile()->getDebbXmlArray();
		}
		if ($this->getEnergyEfficiencyRatio() !== null && count($this->getEnergyEfficiencyRatio()) > 0)
		{
			foreach($this->getEnergyEfficiencyRatio() as $eer)
			{
				$array[] = array('EnergyEfficiencyRatio' => $eer->getDebbXmlArray());
			}
		}
		return $array;
	}

    /**
     * Set class
     *
     * @param string $class
     * @return CoolingDevice
     */
    public function setClass($class)
    {
        $this->class = $class;
    
        return $this;
    }

    /**
     * Get class
     *
     * @return string 
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Set maxCoolingCapacity
     *
     * @param float $maxCoolingCapacity
     * @return CoolingDevice
     */
    public function setMaxCoolingCapacity($maxCoolingCapacity)
    {
        $this->maxCoolingCapacity = $maxCoolingCapacity;
    
        return $this;
    }

    /**
     * Get maxCoolingCapacity
     *
     * @return float 
     */
    public function getMaxCoolingCapacity()
    {
        return $this->maxCoolingCapacity;
    }

    /**
     * Set maxAirThroughput
     *
     * @param float $maxAirThroughput
     * @return CoolingDevice
     */
    public function setMaxAirThroughput($maxAirThroughput)
    {
        $this->maxAirThroughput = $maxAirThroughput;
    
        return $this;
    }

    /**
     * Get maxAirThroughput
     *
     * @return float 
     */
    public function getMaxAirThroughput()
    {
        return $this->maxAirThroughput;
    }

    /**
     * Set maxWaterThroughput
     *
     * @param float $maxWaterThroughput
     * @return CoolingDevice
     */
    public function setMaxWaterThroughput($maxWaterThroughput)
    {
        $this->maxWaterThroughput = $maxWaterThroughput;
    
        return $this;
    }

    /**
     * Get maxWaterThroughput
     *
     * @return float 
     */
    public function getMaxWaterThroughput()
    {
        return $this->maxWaterThroughput;
    }

    /**
     * Set airThroughputProfile
     *
     * @param \Debb\ManagementBundle\Entity\FlowProfile $airThroughputProfile
     * @return CoolingDevice
     */
    public function setAirThroughputProfile(\Debb\ManagementBundle\Entity\FlowProfile $airThroughputProfile = null)
    {
        $this->airThroughputProfile = $airThroughputProfile;
    
        return $this;
    }

    /**
     * Get airThroughputProfile
     *
     * @return \Debb\ManagementBundle\Entity\FlowProfile 
     */
    public function getAirThroughputProfile()
    {
        return $this->airThroughputProfile;
    }

    /**
     * Set waterThroughputProfile
     *
     * @param \Debb\ManagementBundle\Entity\FlowProfile $waterThroughputProfile
     * @return CoolingDevice
     */
    public function setWaterThroughputProfile(\Debb\ManagementBundle\Entity\FlowProfile $waterThroughputProfile = null)
    {
        $this->waterThroughputProfile = $waterThroughputProfile;
    
        return $this;
    }

    /**
     * Get waterThroughputProfile
     *
     * @return \Debb\ManagementBundle\Entity\FlowProfile 
     */
    public function getWaterThroughputProfile()
    {
        return $this->waterThroughputProfile;
    }

    /**
     * Add energyEfficiencyRatio
     *
     * @param \Debb\ManagementBundle\Entity\CoolingEER $energyEfficiencyRatio
     * @return CoolingDevice
     */
    public function addEnergyEfficiencyRatio($energyEfficiencyRatio)
    {
		if($energyEfficiencyRatio instanceof \Debb\ManagementBundle\Entity\CoolingEER)
		{
			$this->energyEfficiencyRatio[] = $energyEfficiencyRatio;
			$energyEfficiencyRatio->setCoolingDevice($this);
		}
    
        return $this;
    }

    /**
     * Remove energyEfficiencyRatio
     *
     * @param \Debb\ManagementBundle\Entity\CoolingEER $energyEfficiencyRatio
     */
    public function removeEnergyEfficiencyRatio(\Debb\ManagementBundle\Entity\CoolingEER $energyEfficiencyRatio)
    {
		$energyEfficiencyRatio->setCoolingDevice();
        $this->energyEfficiencyRatio->removeElement($energyEfficiencyRatio);
    }

    /**
     * Get energyEfficiencyRatio
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getEnergyEfficiencyRatio()
    {
        return $this->energyEfficiencyRatio;
    }

    /**
     * Set coolingCapacityRated
     *
     * @param float $coolingCapacityRated
     * @return CoolingDevice
     */
    public function setCoolingCapacityRated($coolingCapacityRated)
    {
        $this->coolingCapacityRated = $coolingCapacityRated;
    
        return $this;
    }

    /**
     * Get coolingCapacityRated
     *
     * @return float 
     */
    public function getCoolingCapacityRated()
    {
        return $this->coolingCapacityRated;
    }

	/**
	 * Add components
	 *
	 * @param \Debb\ManagementBundle\Entity\Component $components
	 * @return Heatsink
	 */
	public function addComponent(\Debb\ManagementBundle\Entity\Component $components)
	{
		$this->components[] = $components;

		return $this;
	}

	/**
	 * Remove components
	 *
	 * @param \Debb\ManagementBundle\Entity\Component $components
	 */
	public function removeComponent(\Debb\ManagementBundle\Entity\Component $components)
	{
		$this->components->removeElement($components);
	}

	/**
	 * Get components
	 *
	 * @return Component[]
	 */
	public function getComponents()
	{
		return $this->components;
	}

	/**
	 * Get the parents
	 *
	 * @return Component[]
	 */
	public function getParents()
	{
		return $this->getComponents();
	}
}