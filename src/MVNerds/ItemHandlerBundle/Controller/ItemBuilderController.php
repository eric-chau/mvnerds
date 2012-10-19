<?php

namespace MVNerds\ItemHandlerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use MVNerds\CoreBundle\Model\ItemBuild;

/**
 * @Route("/item-builder")
 */
class ItemBuilderController extends Controller
{

	/**
	 * @Route("/create", name="item_builder_create")
	 */
	public function createAction()
	{
		return $this->render('MVNerdsItemHandlerBundle:ItemBuilder:create_index.html.twig', array(
		));
	}
	
	/**
	 * @Route("/batch", name="item_builder_batch")
	 */
	public function batchAction()
	{
		/* @var $itemBuildManager \MVNerds\CoreBundle\ItemBuild\ItemBuildManager */
		$itemBuildManager = $this->get('mvnerds.item_build_manager');
		
		$itemBuild = new ItemBuild();
		$itemBuild = $itemBuildManager->findOneById(1);
		
		/* @var $batchManager \MVNerds\CoreBundle\Batch\BatchManager */
		$batchManager = $this->get('mvnerds.batch_manager');
		
		die($batchManager->createRecItemBuilder($itemBuild));
		
	}

	
}
