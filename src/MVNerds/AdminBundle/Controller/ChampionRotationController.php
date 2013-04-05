<?php

namespace MVNerds\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

use MVNerds\CoreBundle\Form\Type\RotationType;
/**
 * @Route("/champ-rotation")
 */
class ChampionRotationController extends Controller
{
	/**
	 * Liste toutes les rotations de champion
	 *
	 * @Route("/", name="admin_champ_rotation_index")
	 */
	public function indexAction()
	{		
		return $this->render('MVNerdsAdminBundle:ChampionRotation:index.html.twig', array(
			'rotations' => $this->get('mvnerds.champion_rotation_manager')->findAll()
		));
	}

	/**
	 *
	 * @Route("/add", name="admin_champ_rotation_add")
	 */
	public function addChampionRotationAction()
	{
		$form = $this->createForm(new RotationType());

		$request = $this->getRequest();
		if ($request->isMethod('POST'))
		{
			$form->bind($request);
			if ($form->isValid())
			{
				$rotation = $form->getData();
				
				$rotation->save();

				// Ajout d'un message de flash pour notifier que le champion a bien été ajouté
				$this->get('mvnerds.flash_manager')->setSuccessMessage('La rotation ' . $rotation->getTitle() . ' a bien été ajouté.');

				// On redirige l'utilisateur vers la liste des champions
				return $this->redirect($this->generateUrl('admin_champ_rotation_index'));
			}
		}

		return $this->render('MVNerdsAdminBundle:ChampionRotation:add_champ_rotation_form.html.twig', array(
			'form' => $form->createView()
		));
	}
	
	/**
	 * Formulaire d'édition de champion
	 *
	 * @Route("/{id}/edit", name="admin_champ_rotation_edit")
	 */
	public function editChampionRotationAction($id)
	{
		try {
			$rotation = $this->get('mvnerds.champion_rotation_manager')->findById($id);
		} catch (\Exception $e) {
			return $this->redirect($this->generateUrl('admin_champ_rotation_index'));
		}
		$form = $this->createForm(new RotationType(), $rotation);

		$request = $this->getRequest();
		if ($request->isMethod('POST'))
		{
			$form->bind($request);
			if ($form->isValid())
			{
				$rotation = $form->getData();
				
				$rotation->save();
				
				$this->get('mvnerds.flash_manager')->setSuccessMessage('Les informations de la rotation ont bien été mises à jour.');

				return $this->redirect($this->generateUrl('admin_champ_rotation_index'));
			}
		}

		return $this->render('MVNerdsAdminBundle:ChampionRotation:edit_champ_rotation_form.html.twig', array(
			'form'		=> $form->createView(),
			'rotation'	=> $rotation
		));
	}

	/**
	 * Supprimer le champion ayant pour slug $slug de la base de données;
	 *
	 * @Route("/{id}/delete", name="admin_champ_rotation_delete")
	 */
	public function deleteChampionRotationAction($id)
	{
		$this->get('mvnerds.champion_rotation_manager')->deleteById($id);

		return new Response(json_encode(true));
	}
}
