<?php

namespace Debb\ManagementBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Memory
 *
 * @ORM\Table(name="memory")
 * @ORM\Entity(repositoryClass="Debb\ManagementBundle\Repository\BaseRepository")
 */
class Memory extends Base
{
	/**
	 * @var integer
	 *
	 * @ORM\Column(name="Capacity", type="integer", nullable=true)
	 */
	private $capacity;

	/**
	 * Set capacity
	 *
	 * @param integer $capacity
	 * @return Memory
	 */
	public function setCapacity($capacity)
	{
		$this->capacity = $capacity;

		return $this;
	}

	/**
	 * Get capacity
	 *
	 * @return integer 
	 */
	public function getCapacity()
	{
		return $this->capacity;
	}

	/**
	 * Returns a array for later converting
	 * 
	 * @return array the array for later converting
	 */
	public function getDebbXmlArray()
	{
		$array = parent::getDebbXmlArray();
		if ($this->getCapacity() != null)
		{
			$array['Capacity'] = $this->getCapacity();
		}
		return $array;
	}

}