<?php

namespace MVNerds\VideoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

use MVNerds\CoreBundle\Model\Video;

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
	 * @Route("/publish-ajax", name="videos_publish_ajax",  options={"expose"=true})
	 */
	public function publishAjaxAction()
	{
		$request = $this->getRequest();
		if (!$request->isXmlHttpRequest()) {
			throw new HttpException(500, 'Request must be AJAX');
		}
		
		/* @var $videoManager \MVNerds\CoreBundle\Video\VideoManager */
		$videoManager = $this->get('mvnerds.video_manager');
		
		$video = new Video();
		
		if ( isset( $_POST['title'] ) && ($title = $_POST['title']) != '' ) {
			$video->setTitle($title);
		} else {
			throw new HttpException(500, 'Missing title');
		}
		
		if ( isset( $_POST['category'] ) && ($category = $_POST['category']) != '' ) {
			$video->setCategoryId($category);
		} else {
			throw new HttpException(500, 'Missing category');
		}
		
		if ( isset( $_POST['link'] ) && ($link = $_POST['link']) != '' ) {
			if ($videoManager->isVideoLinkValid($link)) {
				$video->setLink($link);
			} else {
				throw new \Exception('Link not valid');
			}
		} else {
			throw new HttpException(500, 'Missing link');
		}
		
		if ( isset( $_POST['description'] ) && ($description = $_POST['description']) != '' ) {
			$video->setDescription($description);
		}
		
		$video->setUser($this->getUser());
		
		//$video->save();
		return new Response(json_encode($video->toArray()));
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
