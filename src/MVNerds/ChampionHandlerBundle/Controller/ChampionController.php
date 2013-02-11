<?php

namespace MVNerds\ChampionHandlerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Exception;

class ChampionController extends Controller
{
	/**
	 * Retourne la page de détail d'un champion
	 * 
	 * @Route("/champion/{slug}", name="champion_detail")
	 */
	public function championDetailAction($slug)
	{
		$champion = null;
		try {
			$champion = $this->get('mvnerds.champion_manager')->findBySlugWithSkillsAndSkins($slug);
		}
		catch (Exception $e) {
			
		}		
		
		return $this->render('MVNerdsChampionHandlerBundle:Champion:champion_detail.html.twig', array(
			'champion' => $champion
		));
	}
	
	/**
	 * @Route("/get-champions-name", name="champions_names", options={"expose"=true})
	 */
	public function getChampionsNameAction()
	{
		$request = $this->getRequest();
		if  (!$request->isXmlHttpRequest() || !$request->isMethod('POST')) {
			throw new HttpException(500, 'La requête doit être effectuée en AJAX et en method POST !');
		}
		return new Response(json_encode($this->get('mvnerds.champion_manager')->getChampionsName()->toArray()));
	}
}
