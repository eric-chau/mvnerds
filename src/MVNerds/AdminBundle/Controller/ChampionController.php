<?php

namespace MVNerds\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

use MVNerds\CoreBundle\Form\Type\ChampionType;
use MVNerds\CoreBundle\Model\ChampionPeer;

/**
 * @Route("/champ")
 */
class ChampionController extends Controller
{
	/**
	 * Liste tous les champions de la base
	 *
	 * @Route("/", name="admin_champions_index")
	 */
	public function indexAction()
	{		
		return $this->render('MVNerdsAdminBundle:Champion:index.html.twig', array(
			'champions' => $this->get('mvnerds.champion_manager')->findAll()
		));
	}

	/**
	 * Formulaire d'ajout d'un nouveau champion
	 *
	 * @Route("/add", name="admin_champions_add")
	 */
	public function addChampionAction()
	{
		$form = $this->createForm(new ChampionType());

		$request = $this->getRequest();
		if ($request->isMethod('POST'))
		{
			$form->bind($request);
			if ($form->isValid())
			{
				$champion = $form->getData();
				// Persistance de l'objet en base de données
				$this->get('mvnerds.champion_manager')->createChampionIfValid($champion);

				// Ajout d'un message de flash pour notifier que le champion a bien été ajouté
				$this->get('mvnerds.flash_manager')->setSuccessMessage('Le champion ' . $champion->getName() . ' a bien été ajouté.');

				// On redirige l'utilisateur vers la liste des champions
				return $this->redirect($this->generateUrl('admin_champions_index'));
			}
		}

		return $this->render('MVNerdsAdminBundle:Champion:add_champion_form.html.twig', array(
			'form' => $form->createView()
		));
	}
	
	/**
	 * Formulaire d'édition de champion
	 *
	 * @Route("/{slug}/edit", name="admin_champions_edit")
	 */
	public function editChampionAction($slug)
	{
		try {
			$champion = $this->get('mvnerds.champion_manager')->findBySlugWithI18ns($slug);
		} catch (\Exception $e) {
			return $this->redirect($this->generateUrl('admin_champions_index'));
		}
		$form = $this->createForm(new ChampionType(), $champion);

		$request = $this->getRequest();
		if ($request->isMethod('POST'))
		{
			$form->bind($request);
			if ($form->isValid())
			{
				$champion = $form->getData();die(var_dump($champion->getChampionI18ns()));
				// TODO: effectuer au moins la valiation en XML avant de sauvegarder les modifications effectuées sur le champion
				$this->get('mvnerds.champion_manager')->save($champion);

				// Ajout d'un message de flash pour notifier que les informations de l'utilisateur ont bien été modifié
				$this->get('mvnerds.flash_manager')->setSuccessMessage('Les informations du champion ' . $champion->getName() . ' ont bien été mises à jour.');

				// On redirige l'utilisateur vers la liste des champions
				return $this->redirect($this->generateUrl('admin_champions_index'));
			}
		}

		return $this->render('MVNerdsAdminBundle:Champion:edit_champion_form.html.twig', array(
			'form'		=> $form->createView(),
			'champion'	=> $champion
		));
	}

	/**
	 * Supprimer le champion ayant pour slug $slug de la base de données;
	 *
	 * @Route("/{slug}/supprimer", name="admin_champions_delete")
	 */
	public function deleteChampionAction($slug)
	{
		$this->get('mvnerds.champion_manager')->deleteBySlug($slug);

		return new Response(json_encode(true));
	}
	
	/**
	 * Simple consultation de la fiche du champion
	 * 
	 * @param string $slug slug du champion dont on veut consulter la fiche
	 *
	 * @Route("/{slug}/voir", name="admin_champions_view")
	 */
	public function viewChampionAction($slug)
	{
		//Création du championManager
		/* @var $champion \MVNerds\CoreBundle\Model\Champion */
		$champion = $this->get('mvnerds.champion_manager')->findBySlug($slug);
		
		//On récupère les tags du champion
		$tags =$this->get('mvnerds.champion_tag_manager')->findTagsByChampion($champion);
		
		//On récupère les champs à afficher pour les stats
		$fieldNames = ChampionPeer::getFieldNames();

		return $this->render('MVNerdsAdminBundle:Champion:view_champion.html.twig', array(
			'champion'		=> $champion,
			'tags'			=> $tags,
			'field_names'	=> $fieldNames
		));
	}
}
