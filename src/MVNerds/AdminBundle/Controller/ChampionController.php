<?php

namespace MVNerds\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

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
		$champion = $this->get('mvnerds.champion_manager')->deleteBySlug($slug);
	}
	
	/**
	 * Permet d'ajouter un champion à la liste des champions à comparer grâce à son slug
	 * 
	 * @param string $slug le slug du champion à ajouter à la comparaison
	 * 
	 * @Route("/ajouter-comparaison/{slug}", name="admin_champions_add_to_compare")
	 */
	public function addToCompareAction($slug)
	{		
		$champion = $this->get('mvnerds.champion_manager')->findBySlug($slug);
		
		$session = $this->getRequest()->getSession();
		
		//Si la liste des champions à comparer n'existe pas encore
		if ( (! $session->has('comparison_list')) || (!is_array($session->get('comparison_list'))))
		{
			//on crée la liste
			$session->set('comparison_list', array());
		}
		
		//Création du FlashManager
		/* @var $flashManager \MVNerds\CoreBundle\Flash\FlashManager */
		$flashManager = $this->get('mvnerds.flash_manager');
		
		$comparisonList = $session->get('comparison_list');
		
		//On vérifie que la taille maximum du tableau ne soit pas dépassée
		if (count($comparisonList) < $this->_maxChampionComparison)
		{
			if( ! array_key_exists($champion->getSlug(), $comparisonList) )
			{
				$comparisonList[$champion->getSlug()] = $champion;
				$session->set('comparison_list', $comparisonList);
				$flashManager->setSuccessMessage('Flash.success.add_to_compare.champions');
			}
			else
			{
				$flashManager->setErrorMessage('Flash.error.already_in_list.add_to_compare.champions');
			}
		}
		else
		{
			$flashManager->setErrorMessage('Flash.error.max_reached.add_to_compare.champions');
		}
		
		// On redirige l'utilisateur vers la liste des utilisateurs
		return $this->redirect($this->generateUrl('admin_champions_index'));
	}
	
	/**
	 * Permet de retirer un champion de la liste des champions à comparer grâce à son slug
	 * 
	 * @param string $slug le slug du champion à retirer de la comparaison
	 * 
	 * @Route("/retirer-comparaison/{slug}", name="admin_champions_remove_from_compare")
	 */
	public function removeFromCompareAction($slug)
	{		
		//récupération du champion grâce à son slug
		$champion = $this->get('mvnerds.champion_manager')->findBySlug($slug);
		
		//récupération de la session
		$session = $this->getRequest()->getSession();
		
		//Si la liste des champions à comparer n'existe pas encore
		if ( $session->has('comparison_list') )
		{
			//Récupération de la liste
			$comparisonList = $session->get('comparison_list');
			
			/*
			 * TODO: un peu de la même manière que la personne qui souhaite ajouter un champion qui
			 * est déjà dans la liste, tu lui signifies; Pourquoi ne pas signifier si un champion
			 * n'est pas dans la liste ? (il peut très bien taper directement l'url et dans ce cas là
			 * ça peut te péter une erreur
			 */
			unset($comparisonList[$champion->getSlug()]);
			$session->set('comparison_list', $comparisonList);
			
			$this->get('mvnerds.flash_manager')->setSuccessMessage('Flash.success.remove_from_compare.champions');
		}
		
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
		$session = $this->getRequest()->getSession();
		
		//Création du FlashManager
		/* @var $flashManager \MVNerds\CoreBundle\Flash\FlashManager */
		$flashManager = $this->get('mvnerds.flash_manager');
		
		if ($session->has('comparison_list') && count(($comparisonList = $session->get('comparison_list'))) > 0)
		{
			if (count($comparisonList) >= 2)
			{
				$fieldNames = ChampionPeer::getFieldNames();
				
				return $this->render('MVNerdsAdminBundle:Champion:compare_champions.html.twig', array(
					'comparison_list'	=> $comparisonList,
					'field_names'		=> $fieldNames
				));
			}
			else
			{
				$flashManager->setErrorMessage('Flash.error.not_enough.compare.champions');
			}
		}
		else
		{
			$flashManager->setErrorMessage('Flash.error.empty.compare.champions');
		}
		
		return $this->redirect($this->generateUrl('admin_champions_index'));
	}
	
	/**
	 * Permet de vide la liste de comparaison de champions
	 * 
	 * @Route("/vider-comparaison", name="admin_champions_clean_comparison")
	 */
	public function cleanComparisonAction()
	{		
		//récupération de la session
		$session = $this->getRequest()->getSession();
		
		//Si la liste des champions à comparer n'existe pas encore
		if ( $session->has('comparison_list') )
		{
			$session->set('comparison_list', null);
			
			$this->get('mvnerds.flash_manager')->setSuccessMessage('Flash.success.clean_comparison.champions');
		}
		
		// On redirige l'utilisateur vers la liste des utilisateurs
		return $this->redirect($this->generateUrl('admin_champions_index'));
	}
}
