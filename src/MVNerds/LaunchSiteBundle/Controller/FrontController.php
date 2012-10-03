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
			return $this->redirect($this->generateUrl('champion_handler_front_champion_comparison'));
		}
		elseif($request->isMethod('POST'))
		{
			$form->bind($request);
			
		}
		return $this->render('MVNerdsLaunchSiteBundle:Front:index.html.twig', array(
			'form' => $form->createView(),
			'page' => 'champions'
		));
	}
	
	/**
	 * Permet de rediriger vers l action de comparaison
	 * 
	 * @Route("/{_locale}/comparer-champions", requirements={"_locale"="en|fr"}, defaults={"_locale" = "fr"})
	 * @Route("/comparer-champions", name="launch_site_front_compare_champions", options={"expose"=true})
	 */
	public function compareChampionsAction()
	{
		$form = $this->createForm(new UserType());
		$request = $this->getRequest();
		if ($request->isXmlHttpRequest())
		{
			return $this->redirect($this->generateUrl('champion_handler_comparison_compare'));
		}
		elseif($request->isMethod('POST'))
		{
			$form->bind($request);
			
		}
		return $this->render('MVNerdsLaunchSiteBundle:Front:index.html.twig', array(
			'form' => $form->createView(),
			'page' => 'compare'
		));
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

}
