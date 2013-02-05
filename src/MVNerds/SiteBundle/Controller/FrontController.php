<?php

namespace MVNerds\SiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class FrontController extends Controller
{
    /**
     * @Route("/{_locale}", name="site_homepage", defaults={"_locale" = "fr"})
     */
    public function indexAction()
    {
        return $this->render('MVNerdsSiteBundle:Front:index.html.twig', array(
			'lastest_items_builds' => $this->get('mvnerds.item_build_manager')->findLatestBuilds(),
			'popular_items_builds' => $this->get('mvnerds.item_build_manager')->findMostDownloadedBuilds()
		));
    }
	
	/**
	 * Renvoi la liste des champions formater pour l'affichage dans le footer du site
	 */
	public function renderFooterChampionAction()
	{
		return $this->render('MVNerdsSiteBundle:Common:footer_champion_list.html.twig', array(
			'champions' => $this->get('mvnerds.champion_manager')->findAll()
		));
	}
	
	/**
     * @Route("/test-api")
     */
    public function testAPIAction()
    {
		$this->get('mvnerds.elophant_api_manager')->getSummonerAccoundId('kopovlie', 'euw');
    }
}
