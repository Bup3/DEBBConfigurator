<?php

namespace Debb\ManagementBundle\Controller;

use Debb\ConfigBundle\Controller\NodeController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use \Debb\ConfigBundle\Entity\Node;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Patrick Bußmann <patrick.bussmann@christmann.info>
 * @Route("/{_locale}/management/chassis", requirements={"_locale" = "en|de"}, defaults={"_locale" = "en"})
 */
class ChassisController extends BaseController
{
	/**
	 * Creates a new entity
	 *
	 * @Route("/form/{id}-{duplicated}", defaults={"id"=0, "duplicated"=0}, requirements={"id"="\d+|", "duplicated"="0|1|"});
	 * @Template()
	 *
	 * @param Request                                   $request  Request object
	 * @param int                                       $id       item id
	 *
	 * @return array
	 */
	public function formAction(Request $request, $id = 0, $duplicated = 0)
	{
		/* @var $item \Debb\ManagementBundle\Entity\Chassis */
		$item = $this->getEntity($id);

		$form = $this->createForm($this->getFormType($item), $item, array('attr' => array('duplicated' => $duplicated)));
		if ($request->getMethod() == 'POST')
		{
			$form->submit($request);

			if ($form->isValid())
			{
				$this->persistEntity($item);
				$this->addSuccessMsg("localdev_admin.messages.saved");
			}
		}

		$nodeController = new NodeController();
		$nodeController->setContainer($this->getContainer());

		return $this->render($this->resolveTemplate(__METHOD__), array(
			'form' => $form->createView(),
			'item' => $item,
			'nodetypspecs' => $nodeController->getNodeTypesQuery()->execute(),
            'duplicated' => $duplicated
		));
	}

    /**
     * Duplicate entity
     *
     * @Route("/duplicate/{id}", requirements={"id"="\d+"});
     *
     * @param int $id item id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function duplicateAction($id)
    {
        $item = $this->getEntity($id);
        $itemNew = clone $item;
        $this->persistEntity($itemNew);
        return $this->redirect($this->generateUrl('debb_management_chassis_form', array('id' => $itemNew->getId(), 'duplicated' => 1)));
    }
}
