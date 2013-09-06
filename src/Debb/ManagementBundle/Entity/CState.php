<?php

namespace Debb\ManagementBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * CState
 *
 * @ORM\Table(name="cstate")
 * @ORM\Entity
 */
class CState
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
     * @Assert\NotBlank()
     * @ORM\Column(name="power_usage", type="decimal")
     */
    private $powerUsage;

	/**
	 * @var \Debb\ManagementBundle\Entity\Processor
	 *
	 * @ORM\ManyToOne(targetEntity="Debb\ManagementBundle\Entity\Processor", inversedBy="cStates")
	 */
	private $processor;

	/**
	 * Returns a array for later converting
	 *
	 * @return array the array for later converting
	 */
	public function getDebbXmlArray($state = 0)
	{
		$array = array();
		$array['State'] = $state;
		if($this->getPowerUsage() !== null)
		{
			$array['PowerUsage'] = $this->getPowerUsage();
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
     * Set powerUsage
     *
     * @param float $powerUsage
     * @return CState
     */
    public function setPowerUsage($powerUsage)
    {
        $this->powerUsage = $powerUsage;
    
        return $this;
    }

    /**
     * Get powerUsage
     *
     * @return float 
     */
    public function getPowerUsage()
    {
        return $this->powerUsage;
    }

    /**
     * Set processor
     *
     * @param \Debb\ManagementBundle\Entity\Processor $processor
     * @return CState
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
}