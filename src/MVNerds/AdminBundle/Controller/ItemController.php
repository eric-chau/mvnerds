<?php

namespace MVNerds\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use MVNerds\CoreBundle\Form\Type\ItemType;
/**
 * @Route("/items")
 */
class ItemController extends Controller
{
	/**
	 * Liste tous les items de la base
	 *
	 * @Route("/", name="admin_items_index")
	 */
	public function indexAction()
	{
		return $this->render('MVNerdsAdminBundle:Item:index.html.twig', array(
			'items' => $this->get('mvnerds.item_manager')->findAll()
		));
	}
	
	/**
	 * Formulaire d'ajout d'un nouvel item
	 *
	 * @Route("/add", name="admin_items_add")
	 */
	public function addItemAction()
	{
		$form = $this->createForm(new ItemType(), null, array('attr' => array('lang' =>$this->get('session')->get('locale'))));

		$request = $this->getRequest();
		if ($request->isMethod('POST'))
		{
			$form->bind($request);
			if ($form->isValid())
			{
				$item = $form->getData();
				// Persistance de l'objet en base de données
				$this->get('mvnerds.item_manager')->save($item);

				// Ajout d'un message de flash pour notifier que le champion a bien été ajouté
				$this->get('mvnerds.flash_manager')->setSuccessMessage('Le champion ' . $item->getSlug() . ' a bien été ajouté.');

				// On redirige l'utilisateur vers la liste des champions
				return $this->redirect($this->generateUrl('admin_items_index'));
			}
		}

		return $this->render('MVNerdsAdminBundle:Item:add_item_form.html.twig', array(
			'form' => $form->createView()
		));
	}
	
	/**
	 * Formulaire d'édition d item
	 *
	 * @Route("/{slug}/edit", name="admin_items_edit")
	 */
	public function editItemAction($slug)
	{
		try {
			$item = $this->get('mvnerds.item_manager')->findBySlugWithI18n($slug);
		} catch (\Exception $e) {
			return $this->redirect($this->generateUrl('admin_items_index'));
		}
		$form = $this->createForm(new ItemType(), $item, array('attr' => array('lang' =>$this->get('session')->get('locale'))));
		
		$wasObsolete = $item->getIsObsolete();
		
		$request = $this->getRequest();
		if ($request->isMethod('POST'))
		{
			$form->bind($request);
			if ($form->isValid())
			{
				$item = $form->getData();
				
				if(!$wasObsolete && $item->getIsObsolete())
				{
					$relatedItemBuildItems = $this->get('mvnerds.item_build_manager')->findItemBuildItemsByItemId($item->getId());
					foreach($relatedItemBuildItems as $itemBuildItems)
					{
						/* @var $itemBuildItems \MVNerds\CoreBundle\Model\ItemBuildItems */
						$itemBuildItems->delete();
					}
				} 
//				elseif ($wasObsolete && !$item->getIsObsolete())
//				{
//					$relatedItemBuilds = $this->get('mvnerds.item_build_manager')->findByItemId($item->getId());
//					foreach($relatedItemBuilds as $itemBuild)
//					{
//						/* @var $itemBuild \MVNerds\CoreBundle\Model\ItemBuild */
//						if (
//							!$itemBuild->getItemRelatedByItem1Id()->getIsObsolete() &&
//							!$itemBuild->getItemRelatedByItem2Id()->getIsObsolete() &&
//							!$itemBuild->getItemRelatedByItem3Id()->getIsObsolete() &&
//							!$itemBuild->getItemRelatedByItem4Id()->getIsObsolete() &&
//							!$itemBuild->getItemRelatedByItem5Id()->getIsObsolete() &&
//							!$itemBuild->getItemRelatedByItem6Id()->getIsObsolete()
//						) {
//							$itemBuild->setStatus(\MVNerds\CoreBundle\Model\ItemBuildPeer::STATUS_PUBLIC);
//							$itemBuild->save();
//						}
//					}
//				}
				
				// TODO: effectuer au moins la valiation en XML avant de sauvegarder les modifications effectuées sur le champion
				$this->get('mvnerds.item_manager')->save($item);
				
				// Ajout d'un message de flash pour notifier que les informations de l'utilisateur ont bien été modifié
				$this->get('mvnerds.flash_manager')->setSuccessMessage('Les informations de l item ' . $item->getSlug() . ' ont bien été mises à jour.');

				// On redirige l'utilisateur vers la liste des champions
				return $this->redirect($this->generateUrl('admin_items_index'));
			}
		}

		return $this->render('MVNerdsAdminBundle:Item:edit_item_form.html.twig', array(
			'form'		=> $form->createView(),
			'item'		=> $item
		));
	}
	
	/**
	 * Supprimer l item ayant pour slug $slug de la base de données;
	 *
	 * @Route("/{slug}/supprimer", name="admin_items_delete")
	 */
	public function deleteItemAction($slug)
	{
		$this->get('mvnerds.item_manager')->deleteBySlug($slug);

		return new Response(json_encode(true));
	}
	
	/**
	 * @Route("/convert-item-build/{offset}/{limit}")
	 */
	public function convertItemBuild($offset, $limit)
	{
		/* @var $itemBuildManager \MVNerds\CoreBundle\ItemBuild\ItemBuildManager */
		$itemBuildManager = $this->get('mvnerds.item_build_manager');
		$allItemBuilds = $itemBuildManager->findAll($offset, $limit);
		foreach ($allItemBuilds as $itemBuild)
		{
			/* @var $itemBuild \MVNerds\CoreBundle\Model\ItemBuild */
			$itemBuild->setGameMode($itemBuild->getChampionItemBuilds()->getFirst()->getGameMode());
			$itemBuild->save();
			$itemBuildItems = $itemBuild->getItemBuildItemss();
			
			$itemBlocksArray = array();
			
			foreach ($itemBuildItems as $itemBuildItem)
			{
				/* @var $itemBuildItem \MVNerds\CoreBundle\Model\ItemBuildItems */
				if (!isset($itemBlocksArray[$itemBuildItem->getType()]))
				{
					$itemBuildBlock = new \MVNerds\CoreBundle\Model\ItemBuildBlock();
					$itemBuildBlock->setItemBuild($itemBuildItem->getItemBuild());
					$itemBuildBlock->setType($itemBuildItem->getType());
					$itemBuildBlock->setPosition($itemBuildItem->getPosition());
					$itemBlocksArray[$itemBuildItem->getType()] = $itemBuildBlock;
					$itemBuildBlock->save();
				}
				$itemBuildBlockItem = new \MVNerds\CoreBundle\Model\ItemBuildBlockItem();
				$itemBuildBlockItem->setItem($itemBuildItem->getItem());
				$itemBuildBlockItem->setPosition($itemBuildItem->getItemOrder());
				$itemBuildBlockItem->setCount($itemBuildItem->getCount());
				$itemBuildBlockItem->setItemBuildBlock($itemBlocksArray[$itemBuildItem->getType()]);
				$itemBuildBlockItem->save();
			}
		}
		die(var_dump($allItemBuilds));
	}
	
}

?>
