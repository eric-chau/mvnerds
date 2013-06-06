<?php

namespace MVNerds\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\FormError;

use MVNerds\CoreBundle\Form\Type\SuperTagType;

/**
 * @Route("/super_tags")
 */
class SuperTagController extends Controller
{

	/**
	 * Liste tous les tags de la plateforme
	 *
	 * @Route("/", name="admin_super_tags_index")
	 */
	public function indexAction()
	{
		return $this->render('MVNerdsAdminBundle:SuperTag:index.html.twig', array(
			'super_tags' => $this->get('mvnerds.super_tag_manager')->findAll(false)
		));
	}
	
	/**
	 * Permet de créer un nouveau super_tag
	 * 
	 * @Route("/create", name="admin_super_tags_create")
	 */
	public function createAction()
	{
		//Création du formulaire de l'objet
		$form = $this->createForm(new SuperTagType());
		
		$request = $this->getRequest();
		if ($request->isMethod('POST'))
		{
			$form->bind($request);
			//Si le formulaire est valide
			if ($form->isValid())
			{
				//On récupère les données du formulaire en créant un nouvel objet SuperTag
				$superTag = $form->getData();
				
				try {
					//On essaie d'enregistrer le SuperTag
					$superTag->save();
				} catch (\Exception $e) {
					//Si une Exception survient, on ajoute l'erreur au formulaire et on l'affiche
					$form->addError(new FormError($e->getMessage()));
					
					return $this->render('MVNerdsAdminBundle:SuperTag:add_super_tag_form.html.twig', array(
						'form' => $form->createView()
					));
				}

				// On redirige l'utilisateur vers la liste des superTags
				return $this->redirect($this->generateUrl('admin_super_tags_index'));
			}
		}

		return $this->render('MVNerdsAdminBundle:SuperTag:add_super_tag_form.html.twig', array(
			'form' => $form->createView()
		));
	}
	
	/**
	 * Permet d'éditer un super_tag
	 * 
	 * @Route("/{uniqueName}/edit", name="admin_super_tags_edit")
	 */
	public function editAction($uniqueName)
	{
		try {
			//On essaie de récupérer le SuperTag associé au uniqueName fournit en paramètre
			$superTag = $this->get('mvnerds.super_tag_manager')->findByUniqueName($uniqueName);
		} catch (\Exception $e) {
			//On renvoie vers la page de listing si on ne le trouve pas.
			return $this->redirect($this->generateUrl('admin_super_tags_index'));
		}
		
		//Création du formulaire
		$form = $this->createForm(new SuperTagType(), $superTag);

		$request = $this->getRequest();
		if ($request->isMethod('POST'))
		{
			$form->bind($request);
			//Si le formulaire est valide
			if ($form->isValid())
			{
				//On récupère les données du formulaire en créant un nouvel objet SuperTag
				$superTag = $form->getData();
				
				try {
					//On essaie d'enregistrer le SuperTag
					$superTag->save();
				} catch (\Exception $e) {
					//Si une Exception survient, on ajoute l'erreur au formulaire et on l'affiche
					$form->addError(new FormError($e->getMessage()));
					
					return $this->render('MVNerdsAdminBundle:SuperTag:add_super_tag_form.html.twig', array(
						'form' => $form->createView()
					));
				}

				// On redirige l'utilisateur vers la liste des superTags
				return $this->redirect($this->generateUrl('admin_super_tags_index'));
			}
		}

		return $this->render('MVNerdsAdminBundle:SuperTag:add_super_tag_form.html.twig', array(
			'form' => $form->createView()
		));
	}
	
	/**
	 * Supprime un tag via son label
	 * 
	 * @Route("/{uniqueName}/supprimer", name="admin_super_tags_delete")
	 */
	public function deleteAction($uniqueName)
	{
		try {
			//On essaie de récupérer le SuperTag associé au uniqueName fournit en paramètre
			$superTag = $this->get('mvnerds.super_tag_manager')->findByUniqueName($uniqueName);
			//On le supprime
			$superTag->delete();
		} catch (\Exception $e) {}
		
		return $this->redirect($this->generateUrl('admin_super_tags_index'));
	}

}
