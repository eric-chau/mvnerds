<?php

namespace MVNerds\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

use MVNerds\CoreBundle\Form\Type\ChampionType;
use MVNerds\CoreBundle\Model\ChampionPeer;
/**
 * @Route("/champions")
 */
class ChampionController extends Controller
{

	/**
	 * @var int permet d'indiquer le nombre max de comparaisons de champions simultanées
	 */
	protected $_maxChampionComparison = 2;
	
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
	 * @Route("/ajouter", name="admin_champions_add")
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
	 * @Route("/{slug}/editer", name="admin_champions_edit")
	 */
	public function editChampionAction($slug)
	{
		$champion = $this->get('mvnerds.champion_manager')->findBySlug($slug);
		$form = $this->createForm(new ChampionType(), $champion);

		$request = $this->getRequest();
		if ($request->isMethod('POST'))
		{
			$form->bind($request);
			if ($form->isValid())
			{
				$champion = $form->getData();
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
		// FIXME: action obselète pour le moment ?
		$this->get('mvnerds.champion_manager')->deleteBySlug($slug);
	}
	
	/**
	 * Permet d'ajouter un champion à la liste des champions à comparer grâce à son slug
	 * 
	 * @param string $slug le slug du champion à ajouter à la comparaison
	 * 
	 * @Route("/{slug}/ajouter-comparaison", name="admin_champions_add_to_compare")
	 */
	public function addToCompareAction($slug)
	{		
		$champion = $this->get('mvnerds.champion_manager')->findBySlug($slug);
		
		//récupération du champion_comparison_manager
		/* @var $comparisonManager \MVNerds\CoreBundle\ChampionComparison\ChampionComparisonManager */
		$comparisonManager = $this->get('mvnerds.champion_comparison_manager');
		
		//On ajoute le champion à la liste
		$comparisonManager->addChampion($champion);
		
		// On redirige l'utilisateur vers la liste des utilisateurs
		return $this->redirect($this->generateUrl('admin_champions_index'));
	}
	
	/**
	 * Permet de retirer un champion de la liste des champions à comparer grâce à son slug
	 * 
	 * @param string $slug le slug du champion à retirer de la comparaison
	 * 
	 * @Route("/{slug}/retirer-comparaison", name="admin_champions_remove_from_compare")
	 */
	public function removeFromCompareAction($slug)
	{		
		//récupération du champion grâce à son slug
		$champion = $this->get('mvnerds.champion_manager')->findBySlug($slug);
		
		//récupération du champion_comparison_manager
		/* @var $comparisonManager \MVNerds\CoreBundle\ChampionComparison\ChampionComparisonManager */
		$comparisonManager = $this->get('mvnerds.champion_comparison_manager');
		
		//On retire le champion de la liste
		$comparisonManager->removeChampion($champion);
		
		// On redirige l'utilisateur vers la liste des utilisateurs
		return $this->redirect($this->generateUrl('admin_champions_index'));
	}
	
	/**
	 * Envoie vers la page de comparaison des champions
	 * 
	 * @Route("/comparer", name="admin_champions_compare")
	 */
	public function compareAction()
	{		
		//Création du FlashManager
		/* @var $flashManager \MVNerds\CoreBundle\Flash\FlashManager */
		$flashManager = $this->get('mvnerds.flash_manager');
		
		//récupération du champion_comparison_manager
		/* @var $comparisonManager \MVNerds\CoreBundle\ChampionComparison\ChampionComparisonManager */
		$comparisonManager = $this->get('mvnerds.champion_comparison_manager');
		
		//Si la liste peut être comparée
		if ($comparisonManager->isComparable())
		{
			//on récupère les champs à comparer
			$fieldNames = ChampionPeer::getFieldNames();

			//On affiche la page de comparaison
			return $this->render('MVNerdsAdminBundle:Champion:compare_champions.html.twig', array(
				'comparison_list'	=> $comparisonManager->getList(),
				'field_names'	=> $fieldNames
			));
		}
		else
		{
			$flashManager->setErrorMessage('Flash.error.not_enough.compare.champions');
		}
		
		return $this->redirect($this->generateUrl('admin_champions_index'));
	}
	
	/**
	 * Permet de vider la liste de comparaison de champions
	 * 
	 * @Route("/vider-comparaison", name="admin_champions_clean_comparison")
	 */
	public function cleanComparisonAction()
	{		
		//récupération du champion_comparison_manager
		/* @var $comparisonManager \MVNerds\CoreBundle\ChampionComparison\ChampionComparisonManager */
		$comparisonManager = $this->get('mvnerds.champion_comparison_manager');
			
		//On vide la liste
		$comparisonManager->cleanList();
		
		// On redirige l'utilisateur vers la liste des utilisateurs
		return $this->redirect($this->generateUrl('admin_champions_index'));
	}
}
