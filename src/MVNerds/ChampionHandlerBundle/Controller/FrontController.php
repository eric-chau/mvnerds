<?php

namespace MVNerds\ChampionHandlerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

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
	 * @Route("/champion-comparison", name="champion_handler_front_champion_comparison", options={"expose"=true})
	 */
	public function championComparisonAction()
	{		
		return $this->render('MVNerdsChampionHandlerBundle:Front:champion_comparison.html.twig', array(
			'champions' => $this->get('mvnerds.champion_manager')->findAllWithTags()
		));
	}
	
	/**
	 * @Route("/get-champions-name", name="champion_handler_front_get_champions_name", options={"expose"=true})
	 */
	public function getChampionsNameAction()
	{
		$request = $this->getRequest();
		if  (!$request->isXmlHttpRequest() || !$request->isMethod('POST')) {
			throw new HttpException(500, 'La requête doit être effectuée en AJAX et en method POST !');
		}
		return new Response(json_encode($this->get('mvnerds.champion_manager')->getChampionsName()->toArray()));
	}
	
	/**
	 * @Route("/{championSlug}", name="champion_handler_detail")
	 */
	public function previewChampionAction($championSlug)
	{		
		return $this->render('MVNerdsChampionHandlerBundle:Champion:champion_detail.html.twig', array(
			'champion' => $this->get('mvnerds.champion_manager')->findBySlug($championSlug)
		));
	}

}
