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
		/* @var $videoManager \MVNerds\CoreBundle\Video\VideoManager */
		$videoManager = $this->get('mvnerds.video_manager');
		
		return $this->render('MVNerdsVideoBundle:Front:list_index.html.twig', array(
			'video_categories'	=> $videoManager->findAllVideoCatgories(),
			'videos'		=> $videoManager->findAllActive()
		));
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
			try {
				$videoManager->findVideoCategoryById($category);
				$video->setVideoCategoryId($category);
			} catch (\Exception $e) {
				throw new \Exception('Video catgory not valid');
			}
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
		
		$video->save();
		return new Response(json_encode($video->getSlug()));
	}
	
	/**
	 * Action appelée par datatables pour charger le tableau de vidéos en ajax
	 * @Route("/list-ajax", name="videos_list_ajax", options={"expose"=true})
	 */
	public function listAjaxAction()
	{
		return new Response(json_encode(array()));
	}
	
	/**
	 * @Route("/detail/{slug}", name="videos_detail", options={"expose"=true})
	 */
	public function detailAction($slug)
	{
		/* @var $videoManager \MVNerds\CoreBundle\Video\VideoManager */
		$videoManager = $this->get('mvnerds.video_manager');
		
		try {
			/* @var $video \MVNerds\CoreBundle\Model\Video */
			$video = $videoManager->findBySlug($slug);
			$video->setView($video->getView() + 1);
			$video->keepUpdateDateUnchanged();
			$video->save();
		} catch (\Exception $e) {
			return $this->redirect($this->generateUrl('videos_index'));
		}
		
		$canEdit = false;
		if ($this->get('security.context')->isGranted('ROLE_USER')) {
			$user = $this->getUser();
			if (($video->getUser()->getId() == $user->getId()) || $this->get('security.context')->isGranted('ROLE_ADMIN')) {
				$canEdit = true;
			}
		}
		
		return $this->render('MVNerdsVideoBundle:Front:detail.html.twig', array(
			'video'		=> $video,
			'can_edit'	=> $canEdit
		));
	}
	
	/**
	 * Permet d'afficher la vidéo qu'elle provienne de youtube ou bien de dailymotion
	 */
	public function renderVideoAction(Video $video)
	{
		if (strpos($video->getLink(), 'youtube.com') !== false) { 
			$link = 'http://www.youtube.com/v/';
			$exploded = explode('&', str_replace('http://www.youtube.com/watch?v=', '', $video->getLink()));
			
			return $this->render('MVNerdsVideoBundle:Videos:youtube.html.twig', array(
				'link'		=> $link . $exploded[0]
			));
		} elseif (strpos($video->getLink(), 'youtu.be') !== false) {
			$link = 'http://www.youtube.com/v/';
			$exploded = explode('&', str_replace('http://youtu.be/', '', $video->getLink()));
			
			return $this->render('MVNerdsVideoBundle:Videos:youtube.html.twig', array(
				'link'		=> $link . $exploded[0]
			));
		} elseif (strpos($video->getLink(),'dailymotion.com') !== false) {
			$link = 'http://www.dailymotion.com/embed/video/';
			if (strpos($video->getLink(),'/video/') !== false) {
				$exploded = explode('_', str_replace('http://www.dailymotion.com/video/', '', $video->getLink()));
			
				return $this->render('MVNerdsVideoBundle:Videos:dailymotion.html.twig', array(
					'link'		=> $link . $exploded[0]
				));
			} elseif (strpos($video->getLink(),'#video=') !== false) {
				$link .= preg_replace('/http:\/\/www\.dailymotion\.com\/.*#video=/', '', $video->getLink());
				
				return $this->render('MVNerdsVideoBundle:Videos:dailymotion.html.twig', array(
					'link'		=> $link
				));
			} else {
				return new Response();
			}
		}
		return $this->redirect($this->generateUrl('videos_index'));
	}
}
