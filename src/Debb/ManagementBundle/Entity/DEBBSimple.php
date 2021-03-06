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

use Debb\ConfigBundle\Entity\Dimensions;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;

/**
 * DEBBSimple
 *
 * SimpleType describes all distinct devices for CAD, so where Transform and id/name are necessary.
 * On the other side the memory or CPU are nor relevant for that.
 *
 * @ORM\Table(name="debb_simple")
 * @ORM\Entity(repositoryClass="Debb\ManagementBundle\Repository\BaseRepository")
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="typ", type="string")
 * @author Patrick Bußmann <patrick.bussmann@christmann.info>
 */
class DEBBSimple extends Dimensions
{
    /**
     * The Transform tag is necessary for all part which are located within a Component i.e. fans within a RECS or sensors or for the "root object of a DEBB it is not used.
     * For all parts at a fixed position within the DEBB (fans, sensors, etc.) this is the transform matrix relative to the DEBB origin.
     * For DEBBComponents this is the relative position of the connector to the DEBB's origin.
     * By "adding" the relative transforms the resulting transform can be directly used for PLMXML.
     *
     * @var string
     *
     * @ORM\Column(name="transform", type="string", length=255, nullable=true)
     */
    private $transform;

    /**
     * @var \Debb\ManagementBundle\Entity\File[]
     *
     * @ORM\ManyToMany(targetEntity="Debb\ManagementBundle\Entity\File", cascade={"all"}, orphanRemoval=true)
     */
    private $references;

	/**
	 * Returns a array for later converting
	 *
	 * @return array the array for later converting
	 */
	public function getDebbXmlArray()
	{
		$array = parent::getDebbXmlArray();
		if ($this->getTransform() !== null)
		{
			$array['Transform'] = $this->getTransform();
		}
		foreach($this->getReferences() as $reference)
		{
			$array[] = array(array('Reference' => array('Type' => $reference->getFileEnding(), 'Location' => './objects/X' . $reference->getId() . '_' . $reference->getName())));
		}
		return $array;
	}
    
    /**
     * Set transform
     *
     * @param string $transform
     * @return DEBBSimple
     */
    public function setTransform($transform)
    {
        $this->transform = $transform;
    
        return $this;
    }

    /**
     * Get transform
     *
     * @return string 
     */
    public function getTransform()
    {
        return $this->transform;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->references = new \Doctrine\Common\Collections\ArrayCollection();
    }

	/**
	 * Duplicate this entity
	 */
	public function __clone()
	{
		if ($this->getId() > 0)
		{
			parent::__clone();

			$references = new ArrayCollection();
			foreach($this->getReferences() as $reference)
			{
				$references->add(clone $reference);
			}
			$this->references = $references;
		}
	}
    
    /**
     * Add references
     *
     * @param \Debb\ManagementBundle\Entity\File $references
     * @return DEBBSimple
     */
    public function addReference(\Debb\ManagementBundle\Entity\File $references)
    {
        $this->references[] = $references;
    
        return $this;
    }

    /**
     * Remove references
     *
     * @param \Debb\ManagementBundle\Entity\File $references
     */
    public function removeReference($reference)
    {
        $this->references->removeElement($reference);
    }

    /**
     * Get references
     *
     * @return \Debb\ManagementBundle\Entity\File[]
     */
    public function getReferences()
    {
        return $this->references instanceof PersistentCollection ? $this->references->getValues() : array();
    }
}