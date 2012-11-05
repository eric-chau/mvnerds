<?php

namespace MVNerds\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Security\Core\SecurityContext;

class SecurityController extends Controller
{

	/**
	 * @Route("/login", name="_security_login")
	 */
	public function indexAction()
	{
		if ($this->get('request')->attributes->has(SecurityContext::AUTHENTICATION_ERROR))
		{
			$error = $this->get('request')->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
		}
		else
		{
			$error = $this->get('request')->getSession()->get(SecurityContext::AUTHENTICATION_ERROR);
		}

		return $this->render('MVNerdsAdminBundle:Security:index.html.twig', array(
					'last_username' => $this->get('request')->getSession()->get(SecurityContext::LAST_USERNAME),
					'error' => $error,
				));
	}

	/**
	 * @Route("/login_check", name="_security_check")
	 */
	public function securityCheckAction()
	{
		// The security layer will intercept this request
	}

	/**
	 * @Route("/logout", name="_security_logout")
	 */
	public function logoutAction()
	{
		// The security layer will intercept this request
	}

}
