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
			'items' => $this->get('mvnerds.item_manager')->findAllWithTags('en')
		));
	}
	
	/**
	 * Formulaire d'ajout d'un nouvel item
	 *
	 * @Route("/add", name="admin_items_add")
	 */
	public function addItemAction()
	{
		$form = $this->createForm(new ItemType());

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
		$form = $this->createForm(new ItemType(), $item);
		
		$request = $this->getRequest();
		if ($request->isMethod('POST'))
		{
			$form->bind($request);
			if ($form->isValid())
			{
				$item = $form->getData();
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
	
}

?>
