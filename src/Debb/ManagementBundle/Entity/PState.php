<?php
/**
 * DEBBConfigurator - A configurator for DEBB component and PLMXML files
 * Copyright (C) 2013-2014 christmann informationstechnik + medien GmbH & Co. KG
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Library General Public
 * License as published by the Free Software Foundation; either
 * version 2 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Library General Public License for more details.
 *
 * You should have received a copy of the GNU Library General Public
 * License along with this library; if not, write to the
 * Free Software Foundation, Inc., 51 Franklin St, Fifth Floor,
 * Boston, MA  02110-1301, USA.
 */

namespace Debb\ManagementBundle\Entity;

use Debb\ManagementBundle\DataTransformer\DecimalTransformer;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * PState
 *
 * @ORM\Table(name="pstate")
 * @ORM\Entity()
 * @author Patrick Bußmann <patrick.bussmann@christmann.info>
 */
class PState
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var float
     *
     * @Assert\NotNull()
     * @ORM\Column(name="frequency", type="decimal", precision=18, scale=9)
     */
    private $frequency;

    /**
     * @var float
     *
     * @Assert\NotNull()
     * @ORM\Column(name="voltage", type="decimal", precision=18, scale=9)
     */
    private $voltage;

    /**
     * @var \Debb\ManagementBundle\Entity\PStateLoadPowerUsage[]
     *
	 * @ORM\OneToMany(targetEntity="Debb\ManagementBundle\Entity\PStateLoadPowerUsage", mappedBy="pstate", cascade={"all"}, orphanRemoval=true)
     */
    private $loadPowerUsages;

	/**
	 * @var \Debb\ManagementBundle\Entity\Processor
	 *
	 * @ORM\ManyToOne(targetEntity="Debb\ManagementBundle\Entity\Processor", inversedBy="pStates")
	 */
	private $processor;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->loadPowerUsages = new \Doctrine\Common\Collections\ArrayCollection();
	}

	/**
	 * Duplicate this entity
	 */
	public function __clone()
	{
		if ($this->getId() > 0)
		{
			$this->id = null;

			$this->processor = null;
		}
	}

	/**
	 * Returns a array for later converting
	 *
	 * @return array the array for later converting
	 */
	public function getDebbXmlArray($state = 0)
	{
		$array = array();
		$array['State'] = $state;
		if($this->getFrequency() !== null)
		{
			$array['Frequency'] = DecimalTransformer::convert($this->getFrequency());
		}
		if($this->getVoltage() !== null)
		{
			$array['Voltage'] = DecimalTransformer::convert($this->getVoltage());
		}
		foreach($this->getLoadPowerUsages() as $loadPowerUsage)
		{
			$array[] = array(array('LoadPowerUsage' => $loadPowerUsage->getDebbXmlArray()));
		}
		return $array;
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
     * Set frequency
     *
     * @param float $frequency
     * @return PState
     */
    public function setFrequency($frequency)
    {
        $this->frequency = $frequency;
    
        return $this;
    }

    /**
     * Get frequency
     *
     * @return float 
     */
    public function getFrequency()
    {
        return $this->frequency;
    }

    /**
     * Set voltage
     *
     * @param float $voltage
     * @return PState
     */
    public function setVoltage($voltage)
    {
        $this->voltage = $voltage;
    
        return $this;
    }

    /**
     * Get voltage
     *
     * @return float 
     */
    public function getVoltage()
    {
        return $this->voltage;
    }

    /**
     * Set processor
     *
     * @param \Debb\ManagementBundle\Entity\Processor $processor
     * @return PState
     */
    public function setProcessor(\Debb\ManagementBundle\Entity\Processor $processor = null)
    {
        $this->processor = $processor;
    
        return $this;
    }

    /**
     * Get processor
     *
     * @return \Debb\ManagementBundle\Entity\Processor 
     */
    public function getProcessor()
    {
        return $this->processor;
    }
    
    /**
     * Add loadPowerUsages
     *
     * @param \Debb\ManagementBundle\Entity\PStateLoadPowerUsage $loadPowerUsages
     * @return PState
     */
    public function addLoadPowerUsage($loadPowerUsages)
    {
		if($loadPowerUsages instanceof \Debb\ManagementBundle\Entity\PStateLoadPowerUsage)
		{
			$this->loadPowerUsages[] = $loadPowerUsages;
			$loadPowerUsages->setPstate($this);
		}
    
        return $this;
    }

    /**
     * Remove loadPowerUsages
     *
     * @param \Debb\ManagementBundle\Entity\PStateLoadPowerUsage $loadPowerUsages
     */
    public function removeLoadPowerUsage(\Debb\ManagementBundle\Entity\PStateLoadPowerUsage $loadPowerUsages)
    {
		$loadPowerUsages->setPstate();
        $this->loadPowerUsages->removeElement($loadPowerUsages);
    }

    /**
     * Get loadPowerUsages
     *
     * @return \Debb\ManagementBundle\Entity\PStateLoadPowerUsage[]
     */
    public function getLoadPowerUsages()
    {
        return $this->loadPowerUsages;
    }
}