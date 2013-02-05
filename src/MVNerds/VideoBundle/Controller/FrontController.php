<?php

namespace MVNerds\VideoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/videos")
 */
class FrontController extends Controller
{
	/**
	 * @Route("/", name="videos_index")
	 */
	public function indexAction()
	{
		return $this->render('MVNerdsVideoBundle:Front:list_index.html.twig');
	}
	
	/**
	 * @Route("/publish", name="videos_publish")
	 */
	public function publishAction()
	{
		return $this->render('MVNerdsVideoBundle:Front:list_publish.html.twig');
	}
	
	/**
	 * Action appelée par datatables pour charger le tableau de vidéos en ajax
	 * @Route("/list-ajax", name="videos_list_ajax", options={"expose"=true})
	 */
	public function listAjaxAction()
	{
		return new Response(json_encode(array()));
	}
}
