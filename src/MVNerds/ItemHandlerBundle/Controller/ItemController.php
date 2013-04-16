<?php

namespace MVNerds\ItemHandlerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * @Route("/items")
 */
class ItemController extends Controller
{
	/**
	 * @Route("/", name="items_list")
	 */
	public function listAction()
	{		
		/* @var $tagManager \MVNerds\CoreBundle\Tag\TagManager */
		$tagManager = $this->get('mvnerds.tag_manager');
		$tags = array();
		$tags['attack'] = $tagManager->findByParentName('BASE_ITEM_ATTACK');
		$tags['magic'] = $tagManager->findByParentName('BASE_ITEM_MAGIC');
		$tags['defense'] = $tagManager->findByParentName('BASE_ITEM_DEFENSE');
		$tags['other'] = $tagManager->findByParentName('BASE_ITEM_OTHER');
		
		return $this->render('MVNerdsItemHandlerBundle:Item:item_list_index.html.twig', array(
			'items'	=> $this->get('mvnerds.item_manager')->findAllActive(),
			'tags'		=> $tags
		));
	}
	
	/**
	 * @Route("/{slug}", name="items_detail", options={"expose"=true})
	 */
	public function detailAction($slug)
	{
		try {
			/* @var $item \MVNerds\CoreBundle\Model\Item */
			$item = $this->get('mvnerds.item_manager')->findCompleteBySlug($slug);
		} catch (\Exception $e) {
			return $this->redirect($this->generateUrl('items_list'));
		}
		return $this->render('MVNerdsItemHandlerBundle:Item:item_detail.html.twig', array(
			'item'	=> $item
		));
	}
}
