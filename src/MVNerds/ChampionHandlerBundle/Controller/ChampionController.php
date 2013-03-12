<?php

namespace MVNerds\ChampionHandlerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Exception;
use Symfony\Component\HttpFoundation\Response;

class ChampionController extends Controller
{
	/**
	 * Retourne la page de détail d'un champion
	 * 
	 * @Route("/champion/{slug}", name="champion_detail")
	 */
	public function championDetailAction($slug)
	{
		try {
			$champion = $this->get('mvnerds.champion_manager')->findBySlugWithSkillsAndSkins($slug);
		} catch (Exception $e) {
			return $this->redirect($this->generateUrl('site_homepage'));
		}		
		
		return $this->render('MVNerdsChampionHandlerBundle:Champion:champion_detail.html.twig', array(
			'champion' => $champion,
			'item_builds' => $this->get('mvnerds.item_build_manager')->findBestBuildsForChampion($champion)
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
	
	/**
	 * Retourne la page de détail d'un champion en se basant sur son nom
	 * 
	 * @Route("/champion-by-name/{name}", name="champion_detail_by_name", options={"expose"=true})
	 */
	public function championDetailByNameAction($name)
	{
		try {
			$champion = $this->get('mvnerds.champion_manager')->findByName($name);
		} catch (Exception $e) {
			return $this->redirect($this->generateUrl('site_homepage'));
		}		
		
		return $this->redirect($this->generateUrl('champion_detail', array('slug' => $champion->getSlug())));
	}
}
