<?php

namespace MVNerds\LaunchSiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

use MVNerds\LaunchSiteBundle\Form\Type\SummonerType;
use MVNerds\LaunchSiteBundle\Form\Model\SummonerModel;

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
		$success = $this->get('mvnerds.user_manager')->activateAccount($slug, $activationCode);
		if (!$success) {
			return $this->render('MVNerdsLaunchSiteBundle:Login:activation_fail.html.twig', array(
				'slug'				=> $slug,
				'activation_code'	=> $activationCode
			));
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
		$error = '';
		$request = $this->getRequest();
		if ($request->isMethod('POST')) {
			$email = $request->request->get('_email', null);
			if (null != $email) {
				$userManager = $this->get('mvnerds.user_manager');
				try {
					$user = $userManager->findOneByEmail($email);
					$userManager->initForgotPasswordProcess($user);
				}
				catch(Exception $e) {
					$error = 'Désolé, nous ne sommes pas parvenus à vous retrouver à l\'aide des informations que vous avez saisies. Veuillez réessayer.';
				}
			}
			else {
				$error = 'Vous devez renseigner l\'adresse email que vous avez saisi lors de votre inscription pour pouvoir réinitialiser votre mot de passe.';
			}
		}
		
		return $this->render('MVNerdsLaunchSiteBundle:Login:forgot_password_index.html.twig', array(
			'error' => $error
		));
	}
}
