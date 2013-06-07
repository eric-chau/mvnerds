<?php

namespace MVNerds\MainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class SecurityController extends Controller
{

	/**
	 * @Route("/{_locale}/summoner/login", name="security_summoner_login")
	 */
	public function loginAction()
	{		
		$request = $this->getRequest();
		$session = $request->getSession();

		// get the login error if there is one
		if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
			$error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
		}
		else {
			$error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
			$session->remove(SecurityContext::AUTHENTICATION_ERROR);
		}

		return $this->render('MVNerdsSkeletonBundle:Front:login_index.html.twig', array(
			'last_username'				=> $session->get(SecurityContext::LAST_USERNAME),
			'error'						=> $error
		));
	}
	
	/**
	 * @Route("/summoner/redirect-to-login", name="security_store_current_route_and_redirect_to_login")
	 */
	public function storeCurrentRouteAndRedirectToLoginAction()
	{
		$this->get('session')->set('_security.user_area.target_path', $this->getRequest()->headers->get('referer'));
		
		return $this->redirect($this->generateUrl('security_summoner_login'));
	}
	
	/**
	 * @Route("/summoner/redirect-to-registration", name="security_store_current_route_and_redirect_to_registration")
	 */
	public function storeCurrentRouteAndRedirectToRegistrationAction()
	{
		$this->get('session')->set('_security.user_area.target_path', $this->getRequest()->headers->get('referer'));
		
		return $this->redirect($this->generateUrl('site_summoner_registration'));
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