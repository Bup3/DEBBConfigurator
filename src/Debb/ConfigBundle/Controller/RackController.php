<?php

namespace Debb\ConfigBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Localdev\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Request;
use Debb\ManagementBundle\Entity\NodeToNodegroup;
use Debb\ManagementBundle\Entity\NodegroupToRack;

/**
 * @Route("/{_locale}/rack", requirements={"_locale" = "en|de"}, defaults={"_locale" = "en"})
 */
class RackController extends CRUDController
{

	/**
	 * Creates a new entity
	 *
	 * @Route("/form/{id}", defaults={"id"=0}, requirements={"id"="\d+|"});
	 * @Template()
	 *
	 * @param Request                                   $request  Request object
	 * @param int                                       $id       item id
	 *
	 * @return array
	 */
	public function formAction(Request $request, $id = 0)
	{
		$item = $this->getEntity($id);
		$nodegroups = $this->getEntities('DebbConfigBundle:NodeGroup');

		if ($request->getMethod() != 'POST' && count($item->getNodeGroups()) < 1)
		{
			while (count($item->getNodeGroups()) < 42)
			{
				/* create required node groups */
				$nodeGroup = new NodegroupToRack();
				$nodeGroup->setField($item->getFreeNodeGroup());
				$item->addNodeGroup($nodeGroup);
			}

			$this->getManager()->persist($item);
		}

		$form = $this->createForm($this->getFormType($item), $item);
		if ($request->getMethod() == 'POST')
		{
			$form->bind($request);

			if ($form->isValid())
			{
				$this->persistEntity($item);
				$this->addSuccessMsg("localdev_admin.messages.saved");
			}
		}

		return $this->render($this->resolveTemplate(__METHOD__), array(
				'form' => $form->createView(),
				'item' => $item,
				'nodegroups' => $nodegroups
			));
	}

	/**
	 * Return entity as DEBBComponents.xml string
	 *
	 * @param int                                       $id       item id
	 *
	 * @return string the DEBBComponents.xml string
	 */
	public function asXmlAction($id, $pretty=false)
	{
		$item = $this->getEntity($id);

		$xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><DEBBComponents />');
		$xmlComputeBoxTwo = $xml->addChild('ComputeBox2');
		$xmlComputeBoxOne = $xmlComputeBoxTwo->addChild('ComputeBox1');
		$rack = $item->getDebbXmlArray();
		$rack = $rack['Rack'];
		\Debb\ManagementBundle\Entity\Base::array_to_xml($rack, $xmlComputeBoxOne);

		if($pretty)
		{
			$dom = dom_import_simplexml($xml)->ownerDocument;
			$dom->formatOutput = true;
			$dom->preserveWhiteSpace = true;
			$xmlStr = $dom->saveXML();
		}
		else
		{
			$xmlStr = $xml->asXML();
		}

		$str = str_replace('<DEBBComponents>', '<xsd_1:DEBBComponents xmlns:xsd_1="http://www.coolemall.eu/DEBBComponent"
	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
	xsi:schemaLocation="http://www.coolemall.eu/DEBBComponent DEBBComponents.xsd "><Name>CoolEmAll</Name><Description>Generated DEBBComponent File</Description>', str_replace('</DEBBComponents>', '</xsd_1:DEBBComponents>', $xmlStr));
	
		return $str;
	}

	/**
	 * Return entity as plm xml string
	 *
	 * @param int $id item id
	 *
	 * @return string the plm xml string
	 */
	public function asPlmXmlAction($id, $pretty=false)
	{
		$item = $this->getEntity($id);

		$xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><PLMXML xmlns:PLMXML="http://www.plmxml.org/Schemas/PLMXMLSchema"
	xmlns:vis="PLMXMLTcVisSchema" schemaVersion="1" date="' . date('Y-m-d') . '" time="' . date('H:i:s') . '"
	author="Generator" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
	xsi:schemaLocation="http://www.plmxml.org/Schemas/PLMXMLSchema PLMXMLSchema.xsd" />');
		$productDef = $xml->addChild('ProductDef');
		$productDef->addAttribute('id', 'id1');
		$instanceGraph = $productDef->addChild('InstanceGraph');
		$instanceGraph->addAttribute('id', 'id2');
		$instanceGraph->addAttribute('rootRefs', 'inst' . sprintf('%02d', $item->getId()) . '_1');

		$rackInstance = $this->addPlmXmlProductInstance(
			$instanceGraph, 'inst' . sprintf('%02d', $item->getId()) . '_1', 'DefRack' . sprintf('%02d', $item->getId()), null, $item->getHostname()
		);
		$rackInstance = $rackInstance[0];

		$nodeGroupsForThatRack = array();

		foreach ($item->getNodeGroups() as $nodeGroup)
		{
			if ($nodeGroup->getNodeGroup() != null)
			{
				$id = $item->getId() . $nodeGroup->getId();
				$nodeGroupsForThatRack[] = 'id' . sprintf('%04d', $id);

				$productRevisionView = $this->addPlmXmlProductRevisionView(
					$instanceGraph, 'id' . sprintf('%04d', $id), 'DefNodeGroup' . sprintf('%04d', $id), array(), 'assembly', 'VRML', '.\objects\file.wrl', $nodeGroup->getNodeGroup()->getComponentId(), 'NodeGroup'
				);

				$nodesForThatNodeGroup = array();
				foreach ($nodeGroup->getNodeGroup()->getNodes() as $node)
				{
					if ($node->getNode() != null)
					{
						$partReference = $this->addPlmXmlProductInstance(
							$instanceGraph, 'id' . sprintf('%04d', $id) . '_2', 'DefNode' . sprintf('%04d', $id), 'id' . sprintf('%04d', $id), $node->getNode()->getHostname(), null // position
						);
						$nodesForThatNodeGroup[] = $partReference[1];
					}
				}

				$productRevisionView->addAttribute('partRef', implode(' ', $nodesForThatNodeGroup));
			}
		}

		$rackInstance->addAttribute('partRef', implode(' ', $nodeGroupsForThatRack));

		if($pretty)
		{
			$dom = dom_import_simplexml($xml)->ownerDocument;
			$dom->formatOutput = true;
			$dom->preserveWhiteSpace = true;
			return $dom->saveXML();
		}
		else
		{
			return $xml->asXML();
		}
	}

	/**
	 * Adds a ProductInstance entry to the SimpleXMLElement $xml [PLMXML]
	 * 
	 * @param \SimpleXMLElement $xml the SimpleXMLElement
	 * @param string $id the id of the ProductRevisionView
	 * @param null|string optional $name the name of this product instance
	 * @param null|string optional $partRef the part reference of this product instance
	 * @param null|string optional $hostname the hostname of this product instance
	 * @param null|string optional $transform the position of this product instance
	 * @return array the SimpleXMLElement product instance (0) and the generated id (1)
	 */
	public function addPlmXmlProductInstance(\SimpleXMLElement &$xml, $id, $name = null, $partRef = null, $hostname = null, $transform = null)
	{
		$productInstance = $xml->addChild('ProductInstance');

		$isId = explode('_', $id);
		$iId = (int) $isId[count($isId) - 1];
		unset($isId[count($isId) - 1]);
		$exId = implode('_', $isId);
		$id = $exId . '_' . $iId;

		while (count($xml->xpath('//ProductInstance[@id="' . $id . '"]/@id')) > 0)
		{
			$iId++;
			$id = $exId . '_' . $iId;
		}

		$productInstance->addAttribute('id', $id); // example: inst71_01_7
		if ($name != null)
		{
			$productInstance->addAttribute('name', $name . '_' . $iId); // example: Node7
		}
		if ($partRef != null)
		{
			$productInstance->addAttribute('partRef', $partRef); // example: #id71_01_1
		}

		if ($hostname != null || $transform != null)
		{
			$userData = $productInstance->addChild('UserData');
			$userData->addAttribute('id', str_replace('inst', 'id', $id) . '_1'); // example: id71_01_7_1

			if ($hostname != null)
			{
				$userValue = $userData->addChild('UserValue');
				$userValue->addAttribute('value', $hostname); // example: n007
				$userValue->addAttribute('title', 'hostname');
			}

			if ($transform != null)
			{
				$transform = $productInstance->addChild('Transform', ''); // example: 0 1 0 0 -1 0 0 0 0 0 1 0 0.175 0.744 0.005 1
				$transform->addAttribute('id', $this->convertIdToTransId($id)); // example: id71_01_07
			}
		}

		return array(0 => $productInstance, 1 => $id);
	}

	/**
	 * Adds a ProductRevisionView entry to the SimpleXMLElement $xml [PLMXML]
	 * 
	 * @param \SimpleXMLElement $xml the SimpleXMLElement
	 * @param string $id the id of the ProductRevisionView
	 * @param null|string optional $name the name of the ProductRevisionView
	 * @param null|array optional $instanceRefs array of instance refs
	 * @param null|string optional $type the type of ProductRevisionView
	 * @param null|string optional $format the file format of $location file
	 * @param null|string optional $location the path to file for representation
	 * @param null|string optional $DEBBComponentId the ComponentID from DEBBComponents.xml file
	 * @param null|string optional $DEBBLevel the type from DEBBComponents.xml file (Node, NodeGroup, Computebox1, ComputeBox2, Sensor, CoolingDevice, Powersupply, ...)
	 * @return \SimpleXMLElement the SimpleXMLElement product revision view
	 */
	public function addPlmXmlProductRevisionView(\SimpleXMLElement &$xml, $id, $name = null, $instanceRefs = array(), $type = null, $format = 'VRML', $location = '.\objects\\', $DEBBComponentId = null, $DEBBLevel = null)
	{
		$productRevisionView = $xml->addChild('ProductRevisionView');
		$productRevisionView->addAttribute('id', $id); // example: id84_04_1
		if ($name != null)
		{
			$productRevisionView->addAttribute('name', $name); // example: NodeGeometry
		}
		if (is_array($instanceRefs) && count($instanceRefs) > 1)
		{
			$productRevisionView->addAttribute('instanceRefs', implode(' ', $instanceRefs)); // example: inst83_01_1 inst83_01_2 inst83_01_3 inst83_01_4 inst83_01_5 inst83_01_6
		}
		if ($type != null)
		{
			$productRevisionView->addAttribute('type', $type); // example: assembly
		}

		if ($DEBBComponentId != null && $DEBBLevel != null)
		{
			$userData = $productRevisionView->addChild('UserData');
			$userData->addAttribute('id', $id . '_1'); // example: id84_04_1_1

			if ($DEBBLevel != null)
			{
				$userValue = $userData->addChild('UserValue');
				$userValue->addAttribute('value', $DEBBLevel); // example: Node (Node, NodeGroup, Computebox1, ComputeBox2, Sensor, CoolingDevice, Powersupply, ...)
				$userValue->addAttribute('title', 'DEBBLevel'); // example: DEBBLevel
			}
			if ($DEBBComponentId != null)
			{
				$userValue = $userData->addChild('UserValue');
				$userValue->addAttribute('value', $DEBBComponentId); // example: node_psnc_i7-16GB-sandy
				$userValue->addAttribute('title', 'DEBBComponentId'); // example: DEBBComponentId
			}
		}

		if ($format != null || $location != null)
		{
			$representation = $productRevisionView->addChild('Representation');
			$representation->addAttribute('id', $this->convertIdToRevId($id)); // example: id1084_04_1
			if ($format != null)
			{
				$representation->addAttribute('format', $format); // example: VRML
			}
			if ($location != null)
			{
				$representation->addAttribute('location', $location); // example: .\objects\NodeBoard.wrl
			}
		}

		return $productRevisionView;
	}

	/**
	 * Convert a id to another id
	 * 
	 * @param string $id the id to convert (example: inst83_01_3)
	 * @return string the converted id (example: id83_01_03)
	 */
	public function convertIdToTransId($id)
	{
		$id = str_replace('inst', 'id', $id);
		$cache = explode('_', $id);
		if (count($cache) > 1)
		{
			$last = & $cache[count($cache) - 1];
			$last = sprintf('%02d', $last);
		}
		return implode('_', $cache);
	}

	/**
	 * Convert a id to another id
	 * 
	 * @param string $id the id to convert (example: id84_04_1)
	 * @return string the converted id (example: id1084_04_1)
	 */
	public function convertIdToRevId($id)
	{
		return str_replace('id', 'id10', $id);
	}

	/**
	 * Download a zip archive with all xml files
	 * 
	 * @Route("/download/{id}.zip", defaults={"id"=0}, requirements={"id"="\d+|"});
	 * @param type $id the id of rack to generate plm xml and DEBBComponents.xml
	 * @throws error 404
	 */
	public function exportAsArchiveAction($id)
	{
		$fileName = tempnam(sys_get_temp_dir(), 'zip');

		$zip = new \ZipArchive;
		$res = $zip->open($fileName, \ZipArchive::CREATE);
		if ($res == true)
		{
			$zip->addFromString('DEBBComponents.xml', $this->asXmlAction($id, true));
			$zip->addFromString('PLMXML.xml', $this->asPlmXmlAction($id, true));
			$zip->addEmptyDir('img');
			foreach($this->getEntities('DebbConfigBundle:Node') as $node)
			{
				if($node->getImage() != null)
				{
					$zip->addFile($node->getImage()->getFullPath(), 'img/' . $node->getComponentId() . '.' . $node->getImage()->getExtension());
				}
			}
			$zip->close();
			header('Content-Disposition: attachment; filename=' . date('Y-m-d-H-i-s') . '.zip');
			header('Content-type: application/zip');
			if(readfile($fileName))
			{
				unlink($fileName);
			}
		}
		else
		{
			throw $this->createNotFoundException($this->get('translator')->trans('could not create zip archive'));
		}
		exit(0);
	}

}
