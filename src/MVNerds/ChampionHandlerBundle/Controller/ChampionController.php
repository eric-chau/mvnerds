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
}
