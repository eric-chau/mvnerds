<?php

namespace MVNerds\LaunchSiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class SideBarController extends Controller
{

	/**
	 * Méthode qui permet de rendre la barre de navigation à gauche
	 */
	public function renderSideBarAction()
	{		
		return $this->render('MVNerdsLaunchSiteBundle:SideBar:index.html.twig', array(
			
		));
	}
	
}
