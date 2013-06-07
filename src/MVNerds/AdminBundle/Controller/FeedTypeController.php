<?php

namespace MVNerds\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\FormError;

use MVNerds\CoreBundle\Form\Type\FeedTypeType;
use MVNerds\CoreBundle\Model\FeedType;

/**
 * @Route("/feed_type")
 */
class FeedTypeController extends Controller
{

	/**
	 * Liste tous les feed type
	 *
	 * @Route("/", name="admin_feed_type_index")
	 */
	public function indexAction()
	{
		return $this->render('MVNerdsAdminBundle:FeedType:index.html.twig', array(
			'feed_types' => $this->get('mvnerds.feed_type_manager')->findAll(false)
		));
	}
	
	/**
	 * Permet de créer un nouveau feed type
	 * 
	 * @Route("/create", name="admin_feed_type_create")
	 */
	public function createAction()
	{
		//Création du formulaire de l'objet
		$form = $this->createForm(new FeedTypeType());
		
		$request = $this->getRequest();
		if ($request->isMethod('POST'))
		{
			$form->bind($request);
			//Si le formulaire est valide
			if ($form->isValid())
			{
				//On récupère les données du formulaire en créant un nouvel objet FeedType
				$feedType = $form->getData();
				
				try {
					//On essaie d'enregistrer le FeedType
					$feedType->save();
				} catch (\Exception $e) {
					//Si une Exception survient, on ajoute l'erreur au formulaire et on l'affiche
					$form->addError(new FormError($e->getMessage()));
					
					return $this->render('MVNerdsAdminBundle:FeedType:add_feed_type_form.html.twig', array(
						'form' => $form->createView()
					));
				}

				// On redirige l'utilisateur vers la liste des superTags
				return $this->redirect($this->generateUrl('admin_feed_type_index'));
			}
		}

		return $this->render('MVNerdsAdminBundle:FeedType:add_feed_type_form.html.twig', array(
			'form' => $form->createView()
		));
	}
	
	/**
	 * Permet d'éditer un super_tag
	 * 
	 * @Route("/{uniqueName}/edit", name="admin_feed_type_edit")
	 */
	public function editAction($uniqueName)
	{
		try {
			//On essaie de récupérer le FeedType associé au uniqueName fournit en paramètre
			$feedType = $this->get('mvnerds.feed_type_manager')->findByUniqueName($uniqueName);
		} catch (\Exception $e) {
			//On renvoie vers la page de listing si on ne le trouve pas.
			return $this->redirect($this->generateUrl('admin_feed_type_index'));
		}
		
		//On conserve l'ancienne version pour pouvoir editer la primary key si besoin
		$oldFeedType = clone $feedType;
		//Création du formulaire
		$form = $this->createForm(new FeedTypeType(), $feedType);

		$request = $this->getRequest();
		if ($request->isMethod('POST'))
		{
			$form->bindRequest($request);
			//Si le formulaire est valide
			if ($form->isValid())
			{
				
				try {
					//On essaie d'enregistrer le FeedType
					$this->get('mvnerds.feed_type_manager')->customSave($feedType, $oldFeedType);
				} catch (\Exception $e) {
					//Si une Exception survient, on ajoute l'erreur au formulaire et on l'affiche
					$form->addError(new FormError($e->getMessage()));
					
					return $this->render('MVNerdsAdminBundle:FeedType:edit_feed_type_form.html.twig', array(
						'form' => $form->createView(),
						'feed_type' => $feedType
					));
				}

				// On redirige l'utilisateur vers la liste des superTags
				return $this->redirect($this->generateUrl('admin_feed_type_index'));
			}
		}

		return $this->render('MVNerdsAdminBundle:FeedType:edit_feed_type_form.html.twig', array(
			'form'		=> $form->createView(),
			'feed_type' => $feedType
		));
	}
	
	/**
	 * Supprime un tag via son label
	 * 
	 * @Route("/{uniqueName}/supprimer", name="admin_feed_type_delete")
	 */
	public function deleteAction($uniqueName)
	{
		try {
			//On essaie de supprimer le FeedType associé au uniqueName fournit en paramètre
			$this->get('mvnerds.feed_type_manager')->deleteByUniqueName($uniqueName);
		} catch (\Exception $e) {}
		
		return $this->redirect($this->generateUrl('admin_feed_type_index'));
	}

}
