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
		return $this->render('MVNerdsProfileBundle:Profile:profile_index.html.twig', array(
			'user' => $this->getUser()
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
}
