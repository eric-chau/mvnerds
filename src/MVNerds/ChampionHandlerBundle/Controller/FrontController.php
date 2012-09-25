<?php

namespace MVNerds\ChampionHandlerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class FrontController extends Controller
{

	/**
	 * Permet d'afficher le module de comparaison à savoir la liste des champions avec le filtre 
	 * ainsi que la liste des champions à comparer
	 *
	 * @Route("/", name="champion_handler_front_index")
	 */
	public function indexAction()
	{		
		return $this->render('MVNerdsChampionHandlerBundle:Front:index.html.twig', array(
			'champions' => $this->get('mvnerds.champion_manager')->findAll()
		));
	}
	
	/**
	 * Permet d'afficher le module de comparaison à savoir la liste des champions avec le filtre 
	 * ainsi que la liste des champions à comparer via le front controller du launch site bundle
	 *
	 * @Route("/comparaison-champion", name="champion_handler_front_champion_comparison", options={"expose"=true})
	 */
	public function championComparisonAction()
	{		
		return $this->render('MVNerdsChampionHandlerBundle:Front:champion_comparison.html.twig', array(
			'champions' => $this->get('mvnerds.champion_manager')->findAll()
		));
	}

}
