<?php

namespace MVNerds\LaunchSiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use MVNerds\CoreBundle\Form\Type\UserType;

class FrontController extends Controller
{

	/**
	 * Affiche la page d'accueil du site de présentation
	 * 
	 * @Route("/{_locale}", name="launch_site_front", requirements={"_locale"="en|fr"}, defaults={"_locale" = "fr"})
	 */
	public function indexAction()
	{		
		$form = $this->createForm(new UserType());
		$request = $this->getRequest();	
		if ($request->isXmlHttpRequest())
		{
			return $this->forward('MVNerdsChampionHandlerBundle:Front:championComparison');
		}
		elseif($request->isMethod('POST'))
		{
			$form->bind($request);
			
		}
		$comparisonListSlugs = $this->get('mvnerds.champion_comparison_manager')->getListSlugs();
		$comparisonList = $this->get('mvnerds.champion_manager')->findManyBySlugs($comparisonListSlugs);
		
		return $this->render('MVNerdsLaunchSiteBundle:Front:index.html.twig', array(
			'form' => $form->createView(),
			'comparison_list' => $comparisonList,
			'page' => 'champions'
		));
	}
	
	/**
	 * Permet de rediriger vers l action de comparaison
	 * 
	 * @Route("/{_locale}/compare-champions/level-{lvl}", name="launch_site_front_compare_champions", requirements={"_locale"="en|fr"}, defaults={"_locale" = "fr", "lvl" = 1}, options={"expose"=true})
	 */
	public function compareChampionsAction($lvl)
	{
		$form = $this->createForm(new UserType());
		$request = $this->getRequest();
		
		$comparisonManager = $this->get('mvnerds.champion_comparison_manager');
		
		if($comparisonManager->isComparable())
		{
			$championManager = $this->get('mvnerds.champion_manager');
			
			$referenceChampionArray = $comparisonManager->getReferenceChampion();
			
			$comparisonListSlugs = $comparisonManager->getListSlugs();
			$comparisonList = $championManager->findManyBySlugs($comparisonListSlugs);	
			$comparisonList = $championManager->setLevelToChampions($lvl, $comparisonList);
			
			foreach($comparisonList as $champion)
			{
				if ($champion->getSlug() == $referenceChampionArray['slug'])
				{
					$referenceChampion = $champion;
					break;
				}
			}
			
			if ($request->isXmlHttpRequest())
			{
				
				
				return $this->render('MVNerdsChampionHandlerBundle:Comparison:compare.html.twig', array(
					'reference_champion'	=> $referenceChampion,
					'comparison_list'		=> $comparisonList,
					'lvl'				=> $lvl
				));
			}
			elseif($request->isMethod('POST'))
			{
				$form->bind($request);		
			}
			
			return $this->render('MVNerdsLaunchSiteBundle:Front:index.html.twig', array(
				'form'				=> $form->createView(),
				'comparison_list'		=> $comparisonList,
				'reference_champion'	=> $referenceChampion,
				'page'				=> 'compare',
				'lvl'				=> $lvl
			));
		}
		else
		{
			return $this->forward('MVNerdsLaunchSiteBundle:Front:index');
		}
	}

	/**
	 * Action qui permet de vérifier si un e-mail est déjà utilisé ou non
	 * 
	 * @Route("/verifier-adresse-mail", name="launch_site_check_email", options={"expose"=true})
	 * 
	 * @return json retourne true si l'e-mail est libre, false sinon (réponse au format JSON)
	 */
	public function checkEmailAvailabilityAction()
	{
		$request = $this->getRequest();
		if (!$request->isXmlHttpRequest() && !$request->isMethod('POST'))
		{
			throw new HttpException(500, 'Request must be XmlHttp and POST method!');
		}

		$email = $request->get('email_to_check', null);
		if (null == $email)
		{
			throw new HttpException(500, 'Missing parameters: `email_to_check`');
		}

		return new Response(json_encode($this->get('mvnerds.user_manager')->isEmailAvailable($email)));
	}

	/**
	 * Action accessible seulement par AJAX !
	 * Action qui permet d'enregistrer un dépôt de mail si ce dernier satisfait au contrainte de validation
	 * 
	 * @Route("/laisser-mon-email", name="launch_site_leave_email")
	 */
	public function leaveMyEmailAction()
	{
		$request = $this->getRequest();
		if (!$request->isXmlHttpRequest())
		{
			throw new HttpException(500, 'Cette méthode n\'est accessible qu\'en AJAX !');
		}

		$isSuccessAction = false;
		$email = '';
		$form = $this->createForm(new UserType());

		if ($request->isMethod('POST'))
		{
			$form->bind($request);
			if ($form->isValid())
			{
				$user = $form->getData();
				// On sauvegarde l'utilisateur
				$this->get('mvnerds.user_manager')->save($user);

				$isSuccessAction = true;
				$email = $user->getEmail();
				$form = $this->createForm(new UserType());
			}
		}
		else
		{
			throw new HttpException(500, 'La méthode de la requête doit être en POST !');
		}

		return $this->render('MVNerdsLaunchSiteBundle:Front:leave_email_form.html.twig', array(
					'form' => $form->createView(),
					'is_success_action' => $isSuccessAction,
					'email' => $email
				));
	}
	
	/**
	 * Permet d'accéder à la page de la politique de confidentialité
	 * 
	 * @Route("/{_locale}/privacy-policy", name="launch_site_privacy_policy", requirements={"_locale"="en|fr"}, defaults={"_locale" = "fr"})
	 */
	public function privacyPolicyAction()
	{
		return $this->render('MVNerdsLaunchSiteBundle:Front:privacy_policy.html.twig');
	}
	
	/**
	 * Permet d'accéder à la page de conditions générales d'utilisation
	 * 
	 * @Route("/{_locale}/terms-of-use", name="launch_site_terms_of_use", requirements={"_locale"="en|fr"}, defaults={"_locale" = "fr"})
	 */
	public function termsOfUseAction()
	{
		return $this->render('MVNerdsLaunchSiteBundle:Front:terms_of_use.html.twig');
	}
	
	/**
	 * Permet d'accéder à la page de contact
	 * 
	 * @Route("/{_locale}/contact-us", name="launch_site_contact_us", requirements={"_locale"="en|fr"}, defaults={"_locale" = "fr"})
	 */
	public function contactUsAction()
	{
		$request = $this->getRequest();
		if ($request->isMethod('POST'))
		{
			$mail = $request->get('contact-mail');
			$subject = $request->get('contact-subject');
			$message = $request->get('contact-message');
			
			/* $flashManager \MVNerds\CoreBundle\Flash\FlashManager */
			$flashManager = $this->get('mvnerds.flash_manager');
			
			if ($mail &&  strlen($mail) > 4)
			{
				if ($subject && strlen($subject) > 5)
				{
					if ($message && strlen($message) > 10)
					{
						$message = \Swift_Message::newInstance()
							->setSubject('Contact from : '.$mail)
							->setFrom($mail)
							->setTo('hani.yagoub@gmail.com')
							->setBody($this->renderView('MVNerdsLaunchSiteBundle:Front:contact_mail.txt.twig', array(
								'mail' => $mail,
								'subject'	=> $subject,
								'message'	=> $message
						)));
						$this->get('mailer')->send($message);
						$flashManager->setSuccessMessage('Flash.success.send_mail_contact');
					}
					else
					{
						$flashManager->setErrorMessage('Flash.error.send_mail_contact.message_invalid');
					}
				}
				else
				{
					$flashManager->setErrorMessage('Flash.error.send_mail_contact.subject_invalid');
				}
			}
			else
			{
				$flashManager->setErrorMessage('Flash.error.send_mail_contact.mail_invalid');
			}
		}
		
		return $this->render('MVNerdsLaunchSiteBundle:Front:contact_us.html.twig');
	}
	
	/**
	 * Permet d'accéder à la page des mentions légales
	 * 
	 * @Route("/{_locale}/legal", name="launch_site_legal", requirements={"_locale"="en|fr"}, defaults={"_locale" = "fr"})
	 */
	public function legalAction()
	{
		return $this->render('MVNerdsLaunchSiteBundle:Front:legal.html.twig');
	}

}
