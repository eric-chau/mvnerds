<?php

namespace MVNerds\ChampionHandlerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/champion-benchmark")
 */
class ChampionBenchmarkController extends Controller
{
	/**
	 * @Route("/", name="champion_benchmark_index")
	 */
	public function indexAction()
	{
		$request = $this->getRequest();	
		if ($request->isXmlHttpRequest())
		{
			return $this->forward('MVNerdsChampionHandlerBundle:Front:championComparison');
		}
		
		$comparisonListSlugs = $this->get('mvnerds.champion_comparison_manager')->getListSlugs();
		$comparisonList = $this->get('mvnerds.champion_manager')->findManyBySlugs($comparisonListSlugs);
		
		return $this->render('MVNerdsChampionHandlerBundle:ChampionBenchmark:champion_benchmark_index.html.twig', array(
			'comparison_list'	=> $comparisonList,
			'champions'			=> $this->get('mvnerds.champion_manager')->findAllWithTags()
		));
	}
	
	/**
	 * Permet de rediriger vers l action de comparaison
	 * 
	 * @Route("/compare/{lvl}", name="champion_benchmark_compare", defaults={"lvl" = 1}, options={"expose"=true})
	 */
	public function compareChampionsAction($lvl)
	{
		$request = $this->getRequest();
		
		$comparisonManager = $this->get('mvnerds.champion_comparison_manager');
		
		if($comparisonManager->isComparable())
		{
			$championManager = $this->get('mvnerds.champion_manager');
			
			$referenceChampionArray = $comparisonManager->getReferenceChampion();
			
			$comparisonListSlugs = $comparisonManager->getListSlugs();
			$comparisonList = $championManager->findManyBySlugs($comparisonListSlugs);	
			$comparisonList = $championManager->setLevelToChampions($lvl, $comparisonList);
			
			foreach($comparisonList as $champion)
			{
				if ($champion->getSlug() == $referenceChampionArray['slug'])
				{
					$referenceChampion = $champion;
					break;
				}
			}
			
			return $this->render('MVNerdsChampionHandlerBundle:ChampionBenchmark:champion_benchmark_compare.html.twig', array(
				'comparison_list'		=> $comparisonList,
				'reference_champion'	=> $referenceChampion,
				'lvl'					=> $lvl
			));
		}
		else
		{
			return $this->redirect($this->generateUrl('champion_benchmark_index'));
		}
	}
	
	/**
	 * Permet d'ajouter un champion à la liste des champions à comparer grâce à son slug
	 * 
	 * @param string $slug le slug du champion à ajouter à la comparaison
	 * 
	 * @Route("/{slug}/add-to-comparaison-list", name="champion_handler_comparison_add_to_compare", options={"expose"=true})
	 */
	public function addToCompareAction($slug)
	{
		//Récupération du champion
		$champion = $this->get('mvnerds.champion_manager')->findBySlug($slug);
		//récupération du champion_comparison_manager
		/* @var $comparisonManager \MVNerds\CoreBundle\ChampionComparison\ChampionComparisonManager */
		$comparisonManager = $this->get('mvnerds.champion_comparison_manager');
		//Récupération du flashManager
		/* @var $flahManager MVNerds\CoreBundle\Flash\FlashManager */
		$flashManager = $this->get('mvnerds.flash_manager');
		//On vérifie si la requete est une requete AJAX
		$isXHR = $this->getRequest()->isXmlHttpRequest();

		//On essaie d'ajouter le champion à la liste
		try
		{
			$comparisonManager->addChampion($champion);
		}
		catch (\Exception $e)
		{
			//on affiche le message d'erreur
			$flashManager->setErrorMessage($e->getMessage());
			//Si c'est une requete ajax
			if ($isXHR)
			{
				//On renvoie une réponse nulle
				return new Response(null);
			}
			else
			{
				// Sinon on redirige l'utilisateur vers la liste des champions
				return $this->redirect($this->generateUrl('champion_handler_comparison_index'));
			}
		}

		//On affiche un message de succes
		$flashManager->setSuccessMessage('Flash.success.add_to_compare.champions');
		//Si c'est une requete ajax
		if ($isXHR)
		{
			//On renvoie le champion comparison row
			return $this->render('MVNerdsChampionHandlerBundle:CompareList:comparison_row.html.twig', array(
				'champion' => $champion,
			));
		}
		else
		{
			// On redirige l'utilisateur vers la liste des champions
			return $this->redirect($this->generateUrl('launch_site_front'));
		}
	}
	/**
	 * Permet d'ajouter plusieurs champions à la liste des champions à comparer grâce à un tableau de slugs
	 * 
	 * @Route("/add-many-champions", name="champion_handler_comparison_add_many_to_compare", options={"expose"=true})
	 */
	public function addManyToCompareAction()
	{
		$request = $this->getRequest();
		//Si c est bien une requete AJAX
		if($request->isXmlHttpRequest())
		{
			//Récupération du flashManager
			/* @var $flahManager MVNerds\CoreBundle\Flash\FlashManager */
			$flashManager = $this->get('mvnerds.flash_manager');
			
			//Il faut que la requete soit envoyée en post 
			if($request->isMethod('POST'))
			{
				//récupération du champion_comparison_manager
				/* @var $comparisonManager \MVNerds\CoreBundle\ChampionComparison\ChampionComparisonManager */
				$comparisonManager = $this->get('mvnerds.champion_comparison_manager');
				
				if (( $championsSlug = $request->get('championsSlug') ))
				{
					$championManager = $this->get('mvnerds.champion_manager');
					
					//Récupération du champion
					$championsArray = $championManager->findManyBySlugs($championsSlug);
					
					try {
						$comparisonManager->addManyChampions($championsArray);

						$flashManager->setSuccessMessage('Les champions du filtre ont bien été ajoutés à la liste de comparaison.');
						
						$comparisonListSlugs = $comparisonManager->getListSlugs();
						$comparisonList = $championManager->findManyBySlugs($comparisonListSlugs);
						
						return $this->render('MVNerdsChampionHandlerBundle:CompareList:comparison_list.html.twig',array(
							'champions' => $comparisonList
						));
					}
					catch(\Exception $e) {
						$comparisonListSlugs = $comparisonManager->getListSlugs();
						$comparisonList = $championManager->findManyBySlugs($comparisonListSlugs);
						$flashManager->setErrorMessage($e->getMessage());
						return $this->render('MVNerdsChampionHandlerBundle:CompareList:comparison_list.html.twig',array(
							'champions' => $comparisonList
						));
					}
				}
				else
				{
					$flashManager->setErrorMessage('No champions slug found');
				
					return new Response(null);
				}
			}
			else
			{
				$flashManager->setErrorMessage('Method must be POST');
				
				return new Response(null);
			}
		}
		else
		{
			// Sinon on redirige l'utilisateur vers la liste des champions
			return $this->redirect($this->generateUrl('launch_site_index'));
		}
	}

	/**
	 * Permet de retirer un champion de la liste des champions à comparer grâce à son slug
	 * 
	 * @param string $slug le slug du champion à retirer de la comparaison
	 * 
	 * @Route("/{slug}/remove-from-comparaison-list", name="champion_handler_comparison_remove_from_compare", options={"expose"=true}, requirements={"_locale"="en|fr"}, defaults={"_locale" = "fr"})
	 */
	public function removeFromCompareAction($slug)
	{
		//récupération du champion grâce à son slug
		$champion = $this->get('mvnerds.champion_manager')->findBySlug($slug);

		//récupération du champion_comparison_manager
		/* @var $comparisonManager \MVNerds\CoreBundle\ChampionComparison\ChampionComparisonManager */
		$comparisonManager = $this->get('mvnerds.champion_comparison_manager');

		//Récupération du flashManager
		/* @var $flahManager MVNerds\CoreBundle\Flash\FlashManager */
		$flashManager = $this->get('mvnerds.flash_manager');
		
		//On vérifie di la requete est une requete AJAX
		$isXHR = $this->getRequest()->isXmlHttpRequest();
		
		//On essaue de retirer le champion de la liste
		try{
			$comparisonManager->removeChampion($champion);
		}
		catch(\Exception $e){
			$flashManager->setErrorMessage($e->getMessage());
			if ($isXHR)
			{
				//On renvoie false
				return new Response(json_encode(array(false)));
			}
			else
			{
				// On redirige l'utilisateur vers la liste des utilisateurs
				return $this->redirect($this->generateUrl('launch_site_front'));
			}
		}
		$flashManager->setSuccessMessage('Flash.success.remove_from_compare.champions');
		
		//Si c'est une requete ajax
		if ($isXHR)
		{
			//On renvoie true
			return new Response(json_encode(array(true)));
		}
		else
		{
			if($comparisonManager->isComparable())
			{
				// On redirige l'utilisateur vers la liste des utilisateurs
				return $this->redirect($this->generateUrl('launch_site_front_compare_champions').'#champion-comparator');
			}
			else
			{
				return $this->redirect($this->generateUrl('launch_site_front'));
			}
		}
	}

	/**
	 * Permet de vider la liste de comparaison de champions
	 * 
	 * @Route("/clean-comparaison-list", name="champion_handler_comparison_clean_comparison", options={"expose"=true})
	 */
	public function cleanComparisonAction()
	{
		//récupération du champion_comparison_manager
		/* @var $comparisonManager \MVNerds\CoreBundle\ChampionComparison\ChampionComparisonManager */
		$comparisonManager = $this->get('mvnerds.champion_comparison_manager');

		//On vide la liste
		$comparisonManager->cleanList();
		
		//On vérifie di la requete est une requete AJAX
		$isXHR = $this->getRequest()->isXmlHttpRequest();
		//Si c'est une requete ajax
		if ($isXHR)
		{
			//On renvoie true
			return new Response(json_encode(array(true)));
		}
		else
		{
			// On redirige l'utilisateur vers la liste des utilisateurs
			return $this->redirect($this->generateUrl('launch_site_front'));
		}
	}

	/**
	 * Permet de sélectionner un champion de référence pour la comparaison de champions
	 * 
	 * @param string le slug du champion à déclarer comme champion de référence
	 * 
	 * @Route("/{slug}/define-as-reference", name="champion_handler_comparison_set_reference_champion", requirements={"_locale"="en|fr"}, defaults={"_locale" = "fr"})
	 */
	public function setReferenceChampionAction($slug)
	{
		//récupération du champion_comparison_manager
		/* @var $comparisonManager \MVNerds\CoreBundle\ChampionComparison\ChampionComparisonManager */
		$comparisonManager = $this->get('mvnerds.champion_comparison_manager');

		//récupération du champion grâce à son slug
		/* @var $champion \MVNerds\CoreBundle\Model\Champion */
		$champion = $this->get('mvnerds.champion_manager')->findBySlug($slug);

		$comparisonManager->setReferenceChampion($champion);

		//On redirige vers la comparaison des champions
		return $this->redirect($this->generateUrl('champion_benchmark_compare').'#champion-comparator');
	}

	/**
	 * Permet de retirer le champion de référence pour la comparaison de champions
	 * 
	 * @Route("/remove-benchmark-champion", name="champion_handler_comparison_remove_reference_champion")
	 */
	public function removeReferenceChampionAction()
	{
		//récupération du champion_comparison_manager
		/* @var $comparisonManager \MVNerds\CoreBundle\ChampionComparison\ChampionComparisonManager */
		$comparisonManager = $this->get('mvnerds.champion_comparison_manager');

		$comparisonManager->cleanReferenceChampion();

		//On redirige vers la comparaison des champions
		return $this->redirect($this->generateUrl('launch_site_front_compare_champions'));
	}

	/**
	 * Permet de réupérer les messages d'erreur du flash manager de manière asynchrone
	 * 
	 * @Route("/get-error-message", name="champion_handler_comparison_get_error_message", options={"expose"=true})
	 */
	public function getErrorMessageAction()
	{
		//Si c'est bien un requete AJAX
		if ($this->getRequest()->isXmlHttpRequest())
		{
			//On renvoie une reponse contenant le message d erreur traduit
			return new Response($this->get('translator')->trans($this->get('mvnerds.flash_manager')->getErrorMessage()));
		}
		else
		{
			//Sinon on redirige vers l index des champions
			return $this->redirect($this->generateUrl('launch_site_front'));
		}
	}

	/**
	 * Permet de réupérer les messages de succes du flash manager de manière asynchrone
	 * 
	 * @Route("/get-success-message", name="champion_handler_comparison_get_success_message", options={"expose"=true})
	 */
	public function getSuccessMessageAction()
	{
		//Si c'est bien un requete AJAX
		if ($this->getRequest()->isXmlHttpRequest())
		{
			//On renvoie une reponse contenant le message d erreur traduit
			return new Response($this->get('translator')->trans($this->get('mvnerds.flash_manager')->getSuccessMessage()));
		}
		else
		{
			//Sinon on redirige vers l index des champions
			return $this->redirect($this->generateUrl('launch_site_front'));
		}
	}

}
