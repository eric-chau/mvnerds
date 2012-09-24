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
     * @Route("/")
     */
    public function indexAction()
    {
		$form = $this->createForm(new UserType());
		
        return $this->render('MVNerdsLaunchSiteBundle:Front:index.html.twig', array(
			'form' => $form->createView()
		));
    }
	
	/**
	 * @Route("/verifier-adresse-mail", name="launch_site_check_email", options={"expose"=true})
	 */
	public function checkEmailAvailabilityAction()
	{
		$request = $this->getRequest();
		if (!$request->isXmlHttpRequest() && !$request->isMethod('POST')) {
			throw new HttpException(500, 'Request must be XmlHttp and POST method!');
		}
		
		if (null == !$request->get('email_to_check', null)) {
			throw new HttpException(500, 'Missing parameters: `email_to_check`');
		}
		
		return new Response(json_encode($this->get('mvnerds.user_manager')->isEmailAvailable($request)));
	}
	
	/**
	 * @Route("/laisser-mon-email", name="launch_site_leave_email")
	 */
	public function createUserAction()
	{
		$form = $this->createForm(new UserType());

        $request = $this->getRequest();
        if ($request->isMethod('POST')) 
        {
            $form->bind($request);
            if ($form->isValid()) 
            {
                $user = $form->getData();
                // On créé l'utilisateur s'il contient des données valides
				$this->get('mvnerds.user_manager')->save($user);

                // Ajout d'un message de flash de succès
                $this->get('mvnerds.flash_manager')->setSuccessMessage('Flash.success.add.user');
            }
        }
		else {
			throw new HttpException(500, 'La méthode de la requête doit être en POST !');
		}

        return $this->render('MVNerdsLaunchSiteBundle:Front:index.html.twig', array(
            'form' => $form->createView()
        ));
	}
}
