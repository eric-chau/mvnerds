<?php

namespace MVNerds\MainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Security\Core\SecurityContext;

class SecurityController extends Controller
{

	/**
	 * @Route("/summoner/login", name="security_summoner_login")
	 */
	public function loginAction()
	{
		$request = $this->getRequest();
		$session = $request->getSession();

		// get the login error if there is one
		if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR))
		{
			$error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
		}
		else
		{
			$error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
			$session->remove(SecurityContext::AUTHENTICATION_ERROR);
		}

		return $this->render('MVNerdsLaunchSiteBundle:Login:login_index.html.twig', array(
			'last_username'				=> $session->get(SecurityContext::LAST_USERNAME),
			'error'						=> $error
		));
	}
	
	/**
	 * @Route("/summoner/store-redirect-and-forward/{routeName}", name="security_summoner_store_redirect_and_forward")
	 */
	public function storeRedirectionAndForwardAction($routeName)
	{
		$this->get('session')->set('_security.user_area.target_path', $this->generateUrl('item_builder_create'));
		return $this->redirect($this->generateUrl($routeName));
	}

	/**
	 * @Route("/summoner/login-check", name="security_summoner_login_check")
	 */
	public function loginCheckAction()
	{
		
	}

	/**
	 * @Route("/summoner/logout", name="security_summoner_logout")
	 */
	public function logoutCheckAction()
	{
		
	}

}