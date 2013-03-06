<?php

namespace MVNerds\SiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Exception;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use MVNerds\LaunchSiteBundle\CustomException\DisabledUserException;
use MVNerds\LaunchSiteBundle\CustomException\UnknowUserException;
use MVNerds\LaunchSiteBundle\CustomException\UserAlreadyEnabledException;
use MVNerds\LaunchSiteBundle\CustomException\WrongActivationCodeException;
use MVNerds\CoreBundle\Model\PioneerUserPeer;
use MVNerds\CoreBundle\Model\PioneerUserQuery;
use MVNerds\LaunchSiteBundle\Form\Model\SummonerModel;
use MVNerds\LaunchSiteBundle\Form\Type\SummonerType;
use MVNerds\LaunchSiteBundle\Form\Model\ResetPasswordModel;
use MVNerds\LaunchSiteBundle\Form\Type\ResetPasswordType;

class RegistrationController extends Controller
{

	/**
	 * Affiche le formulaire d'inscription
	 * 
	 * @Route("/{_locale}/summoner/registration", name="site_summoner_registration", requirements={"_locale"="en|fr"})
	 */
	public function indexAction()
	{
		$this->forbidIfConnected();
		
		$emailFromRequest = $this->getRequest()->get('email', null);
		$isValidPioneerUser = false;
		if (null != $emailFromRequest) {
			$pioneerUser = PioneerUserQuery::create()
				->add(PioneerUserPeer::EMAIL, $emailFromRequest)
			->findOne();
			
			if (null != $pioneerUser) {
				$isValidPioneerUser = true;
			}
		}
		
		$form = $this->createForm(new SummonerType(), new SummonerModel($this->get('mvnerds.user_manager'), $isValidPioneerUser? $emailFromRequest : null));
		$request = $this->getRequest();
		if ($request->isMethod('POST')) {
			$form->bind($request);
			if ($form->isValid()) {
				$user = $form->getData()->save();
				
				return $this->render('MVNerdsSiteBundle:Registration:registration_success.html.twig', array(
					'user' => $user
				));
			}
		}
		
		return $this->render('MVNerdsSiteBundle:Registration:registration_index.html.twig', array(
			'form' => $form->createView()
		));
	}
	
	/**
	 * @Route("/{_locale}/summoner/{slug}/account-activation/{activationCode}", name="site_account_activation")
	 */
	public function activateAccountAction($slug, $activationCode)
	{
		$this->forbidIfConnected();
		
		try {
			$this->get('mvnerds.user_manager')->activateAccount($slug, $activationCode);
		}
		catch (Exception $e) {
			if ($e instanceof UnknowUserException || $e instanceof WrongActivationCodeException) {
				return $this->render('MVNerdsSiteBundle:Registration:activation_fail.html.twig', array(
					'slug'				=> $slug,
					'activation_code'	=> $activationCode
				));
			}
			else if ($e instanceof UserAlreadyEnabledException) {
				return $this->render('MVNerdsSiteBundle:Registration:activation_fail.html.twig', array(
					'user' => $this->get('mvnerds.user_manager')->findBySlug($slug)
				));
			}
		}
		
		return $this->render('MVNerdsSiteBundle:Registration:activation_success.html.twig', array(
			'user' => $this->get('mvnerds.user_manager')->findBySlug($slug)
		));
	}
	
	/**
	 * @Route("/{_locale}/summoner/forgot-password/{email}", name="site_forgot_password", options={"expose"=true})
	 */
	public function forgotPasswordAction($email)
	{
		$this->forbidIfConnected();
		
		$request = $this->getRequest();
		if (!$request->isMethod('POST') && !$request->isXmlHttpRequest()) {
			throw new HttpException(500, 'Action avortée !');
		}
		
		$userManager = $this->get('mvnerds.user_manager');
		$user = null;
		$isSuccess = false;
		try {
			$user = $userManager->findOneByEmail($email);
		}
		catch (Exception $e) {
			$user = null;
		}
		
		if ($user != null && $user->isEnabled()) {
			$userManager->initForgotPasswordProcess($user);
			$isSuccess = true;
		}
		
		return new Response(json_encode($isSuccess));
	}
	
	/**
	 * @Route("/{_locale}/summoner/{slug}/reset-password/{activationCode}", name="site_reset_password")
	 */
	public function resetPasswordAction($slug, $activationCode)
	{
		try {
			$this->get('mvnerds.user_manager')->isValidResetPasswordAction($slug, $activationCode);
		}
		catch (Exception $e) {
			if ($e instanceof UnknowUserException || $e instanceof WrongActivationCodeException) {
				return $this->render('MVNerdsSiteBundle:Registration:reset_password_fail.html.twig', array(
					'slug'				=> $slug,
					'activation_code'	=> $activationCode
				));
			}
			else if ($e instanceof DisabledUserException) {
				return $this->render('MVNerdsSiteBundle:Registration:reset_password_fail.html.twig', array(
					'user' => $this->get('mvnerds.user_manager')->findBySlug($slug)
				));
			}
		}
		
		$this->get('session')->set('forgot_password_user_slug', $slug);
		
		return $this->render('MVNerdsSiteBundle:Registration:reset_password.html.twig', array(
			'form'	=> $this->createForm(new ResetPasswordType(), new ResetPasswordModel($this->get('mvnerds.user_manager')))->createView(),
			'user'	=> $this->get('mvnerds.user_manager')->findBySlug($slug)
		));
	}
	
	/**
	 * @Route("/{_locale}/summoner/change-password", name="site_reset_password_save")
	 */
	public function changePasswordAction()
	{
		$this->forbidIfConnected();
		
		$request = $this->getRequest();
		if ($request->isMethod('POST')) {
			$userManager = $this->get('mvnerds.user_manager');
			$userSlug = $this->get('session')->get('forgot_password_user_slug', null);
			$form = $this->createForm(new ResetPasswordType(), new ResetPasswordModel($userManager, $userSlug));
			$form->bind($request);
			if ($form->isValid()) {
				$user = $form->getData()->save();
				if (null != $user) {
					return $this->render('MVNerdsSiteBundle:Registration:reset_password_success.html.twig', array(
						'user' => $user
					));
				}
			}
			
			return $this->render('MVNerdsSiteBundle:Registration:reset_password.html.twig', array(
				'form'	=> $form->createView(),
				'user'	=> $this->get('mvnerds.user_manager')->findBySlug($this->get('session')->get('forgot_password_user_slug'))
			));
		}
		
		return $this->redirect($this->generateUrl('security_summoner_login'));
	}
	
	private function forbidIfConnected()
	{
		if (null != $this->getUser()) {
			throw new AccessDeniedException();
		}
	}
}
