<?php

namespace MVNerds\LaunchSiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Exception;

use MVNerds\LaunchSiteBundle\CustomException\DisabledUserException;
use MVNerds\LaunchSiteBundle\CustomException\UnknowUserException;
use MVNerds\LaunchSiteBundle\CustomException\UserAlreadyEnabledException;
use MVNerds\LaunchSiteBundle\CustomException\WrongActivationCodeException;
use MVNerds\LaunchSiteBundle\Form\Model\SummonerModel;
use MVNerds\LaunchSiteBundle\Form\Type\SummonerType;
use MVNerds\LaunchSiteBundle\Form\Model\ForgotPasswordModel;
use MVNerds\LaunchSiteBundle\Form\Type\ForgotPasswordType;
use MVNerds\LaunchSiteBundle\Form\Model\ResetPasswordModel;
use MVNerds\LaunchSiteBundle\Form\Type\ResetPasswordType;

class RegistrationController extends Controller
{

	/**
	 * Affiche le formulaire d'inscription
	 * 
	 * @Route("/{_locale}/summoner-registration", name="launch_site_summoner_registration", requirements={"_locale"="en|fr"})
	 */
	public function indexAction()
	{
		$form = $this->createForm(new SummonerType(), new SummonerModel($this->get('mvnerds.user_manager')));
		$request = $this->getRequest();
		if ($request->isMethod('POST')) {
			$form->bind($request);
			if ($form->isValid()) {
				$user = $form->getData()->save();
				
				return $this->render('MVNerdsLaunchSiteBundle:Login:registration_success.html.twig', array(
					'user' => $user
				));
			}
		}
		
		return $this->render('MVNerdsLaunchSiteBundle:Login:registration_index.html.twig', array(
			'form' => $form->createView(),
		));
	}
	
	/**
	 * @Route("/{_locale}/summoner/{slug}/account-activation/{activationCode}", name="launch_site_account_activation")
	 */
	public function activateAccountAction($slug, $activationCode)
	{
		try {
			$this->get('mvnerds.user_manager')->activateAccount($slug, $activationCode);
		}
		catch (Exception $e) {
			if ($e instanceof UnknowUserException || $e instanceof WrongActivationCodeException) {
				return $this->render('MVNerdsLaunchSiteBundle:Login:activation_fail.html.twig', array(
					'slug'				=> $slug,
					'activation_code'	=> $activationCode
				));
			}
			else if ($e instanceof UserAlreadyEnabledException) {
				return $this->render('MVNerdsLaunchSiteBundle:Login:activation_fail.html.twig', array(
					'user' => $this->get('mvnerds.user_manager')->findBySlug($slug)
				));
			}
		}
		
		return $this->render('MVNerdsLaunchSiteBundle:Login:activation_success.html.twig', array(
			'user' => $this->get('mvnerds.user_manager')->findBySlug($slug)
		));
	}
	
	/**
	 * @Route("/{_locale}/summoner/forgot-password", name="launch_site_forgot_password")
	 */
	public function forgotPasswordAction()
	{
		$form = $this->createForm(new ForgotPasswordType(), new ForgotPasswordModel($this->get('mvnerds.user_manager')));
		$request = $this->getRequest();
		if ($request->isMethod('POST')) {
			$form->bind($request);
			if ($form->isValid()) {
				$form->getData()->save();
				
				return $this->render('MVNerdsLaunchSiteBundle:Login:forgot_password_success.html.twig', array());
			}
		}
		
		return $this->render('MVNerdsLaunchSiteBundle:Login:forgot_password_index.html.twig', array(
			'form' => $form->createView()
		));
	}
	
	/**
	 * @Route("/{_locale}/summoner/{slug}/reset-password/{activationCode}", name="launch_site_reset_password")
	 */
	public function resetPasswordAction($slug, $activationCode)
	{
		try {
			$this->get('mvnerds.user_manager')->isValidResetPasswordAction($slug, $activationCode);
		}
		catch (Exception $e) {
			if ($e instanceof UnknowUserException || $e instanceof WrongActivationCodeException) {
				return $this->render('MVNerdsLaunchSiteBundle:Login:reset_password_fail.html.twig', array(
					'slug'				=> $slug,
					'activation_code'	=> $activationCode
				));
			}
			else if ($e instanceof DisabledUserException) {
				return $this->render('MVNerdsLaunchSiteBundle:Login:reset_password_fail.html.twig', array(
					'user' => $this->get('mvnerds.user_manager')->findBySlug($slug)
				));
			}
		}
		
		$this->get('session')->set('forgot_password_user_slug', $slug);
		
		return $this->render('MVNerdsLaunchSiteBundle:Login:reset_password.html.twig', array(
			'form'	=> $this->createForm(new ResetPasswordType(), new ResetPasswordModel($this->get('mvnerds.user_manager')))->createView()
		));
	}
	
	/**
	 * @Route("/{_locale}/summoner/change-password", name="launch_site_reset_password_save")
	 */
	public function changePasswordAction()
	{
		$request = $this->getRequest();
		if ($request->isMethod('POST')) {
			$userManager = $this->get('mvnerds.user_manager');
			$userSlug = $this->get('session')->get('forgot_password_user_slug', null);
			$form = $this->createForm(new ResetPasswordType(), new ResetPasswordModel($userManager, $userSlug));
			$form->bind($request);
			if ($form->isValid()) {
				$user = $form->getData()->save();
				if (null != $user) {
					return $this->render('MVNerdsLaunchSiteBundle:Login:reset_password_success.html.twig', array(
						'user' => $user
					));
				}
			}
			
			return $this->render('MVNerdsLaunchSiteBundle:Login:reset_password.html.twig', array(
				'form'	=> $form->createView()
			));
		}
		
		return $this->redirect($this->generateUrl('launch_site_forgot_password'));
		
	}
}
