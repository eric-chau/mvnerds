<?php

namespace MVNerds\ProfileBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use JMS\SecurityExtraBundle\Annotation\Secure;

class FrontController extends Controller
{
    /**
	 * Affiche la page de profil de l'invocateur authentifié et connecté
	 * 
	 * @Route("/{_locale}/profile", name="summoner_profile_index")
	 * @Secure(roles="ROLE_USER")
	 */
	public function loggedSummonerIndexAction()
	{		
		$user = $this->getUser();
		
		return $this->render('MVNerdsProfileBundle:Profile:profile_index.html.twig', array(
			'user'				=> $user,
			'user_items_builds' => $this->get('mvnerds.item_build_manager')->findByUserId($user->getId())
		));
	}
	
	/**
	 * @Route("/profile", name="summoner_profile_proxy")
	 */	
	public function summonerProfileProxyAction()
	{
		$this->redirect($this->generateUrl('summoner_profile_index', array(
			'_locale' => $this->getRequest()->getLocale()
		)));
	}
	
	/**
	 * @Route("/{_locale}/profile/{userSlug}", name="summoner_profile_view")
	 */
	
	public function viewProfileAction($userSlug)
	{
		$user = $this->get('mvnerds.user_manager')->findBySlug($userSlug);
		
		return $this->render('MVNerdsProfileBundle:Profile:profile_index.html.twig', array(
			'user'				=> $user,
			'user_items_builds' => $this->get('mvnerds.item_build_manager')->findByUserId($user->getId())
		));
	}
}
