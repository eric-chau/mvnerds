<?php

namespace MVNerds\VideoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use MVNerds\CoreBundle\Model\Video;

/**
 * @Route("/lol-video-center")
 */
class LoLVideoController extends Controller
{
	/**
	 * @Route("/", name="lol_video_index")
	 */
	public function indexAction()
	{
		/* @var $videoManager \MVNerds\CoreBundle\Video\VideoManager */
		$videoManager = $this->get('mvnerds.video_manager');
		
		return $this->render('MVNerdsVideoBundle:LoLVideoCenter:lol_video_index.html.twig', array(
			'video_categories'	=> $videoManager->findAllVideoCatgories(),
			'videos'		=> $videoManager->findAllActive(),
			'video'			=> new Video()
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
				
		//Si le paramètre slug est défini c'est une edition de vidéo
		if ( isset( $_POST['slug'] ) && ($slug = $_POST['slug']) != '' ) {
			try {
				/* @var $itemBuild \MVNerds\CoreBundle\Model\ItemBuild */
				$video = $videoManager->findBySlug($slug);
			} catch (\Exception $e ) {
				throw new HttpException(500, 'Video not found');
			}

			if( ! ($this->getUser()->getId() == $video->getUserId() || $this->get('security.context')->isGranted('ROLE_ADMIN'))) {
				throw new AccessDeniedException();
			}
		} else {
			$video = new Video();
			$video->setUser($this->getUser());
		}
				
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
		
		$video->save();
		return new Response(json_encode($video->getSlug()));
	}
	
	/**
	 * Action appelée par datatables pour charger le tableau de vidéos en ajax
	 * @Route("/list-ajax", name="videos_list_ajax", options={"expose"=true})
	 */
	public function listAjaxAction()
	{
		$request = $this->getRequest();
		if (!$request->isXmlHttpRequest())
		{
			throw new HttpException(500, 'Request must be AJAX');
		}
		
		$aColumns = array(
			'',
			'',
			'',
			'user.USERNAME',
			'CreateTime',
			'UpdateTime',
			'Title',
			'View',
			'video_category.UNIQUE_NAME',
			'CommentCount'
		);
		
		$limitStart = 0;
		$limitLength = -1;
		//Pagination
		if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )
		{
			$limitStart = $_GET['iDisplayStart'];
			$limitLength = $_GET['iDisplayLength'];
		}
		//Tri
		$orderArr = array();
		if ( isset( $_GET['iSortCol_0'] ) )
		{
			for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ )
			{
				if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" )
				{
					$orderArr[$aColumns[intval($_GET['iSortCol_'.$i])]] = ($_GET['sSortDir_'.$i]);
				}
			}
		}
		if (count($orderArr) <= 0) {
			$orderArr = array('CreateTime' => 'desc');
		}
		
		//Recherche par colonne
		$whereArr = array();
		for ( $i=0 ; $i<count($aColumns) ; $i++ )
		{
			if ( isset($_GET['bSearchable_'.$i]) && $_GET['bSearchable_'.$i] == "true" && $_GET['sSearch_'.$i] != '' )
			{
				if ($aColumns[$i] == 'user.USERNAME' || $aColumns[$i] == 'Title' || $aColumns[$i] == 'video_category.UNIQUE_NAME')
				{
					$whereArr[$aColumns[$i]] = ($_GET['sSearch_'.$i]);
				}
			}
		}
		
		$translator = $this->get('translator');
		$videoManager = $this->get('mvnerds.video_manager');
		
		$videos = $videoManager->findAllActiveAjax($limitStart, $limitLength, $orderArr, $whereArr);
		
		$jsonVideos = array(
			"tab" => $videos->count(),
			"sEcho" => intval($_GET['sEcho']),
			"iTotalRecords" => $videoManager->countAllActive(),
			"iTotalDisplayRecords" => $videoManager->countAllActiveAjax($whereArr),
			'aaData' => array()
		);
		
		foreach($videos as $video)
		{			
			$jsonVideos['aaData'][] = array(
				$this->renderView('MVNerdsVideoBundle:LoLVideoCenter:lol_video_index_table_preview_cell.html.twig', array('video' => $video)),
				$this->renderView('MVNerdsVideoBundle:LoLVideoCenter:lol_video_index_table_title_cell.html.twig', array('video' => $video, 'user' => $video->getUser())),
				$translator->trans($video->getVideoCategory()->getUniqueName()),
				$video->getUser()->getUsername(),
				$video->getCreateTime('YmdHims'),
				$video->getUpdateTime('YmdHims'),
				$video->getTitle(),
				$video->getView(),
				$video->getVideoCategory()->getUniqueName(),
				$video->getCommentCount()
			);
		}
		return new Response(json_encode($jsonVideos));
	}
	
	/**
	 * @Route("/{slug}", name="videos_detail", options={"expose"=true})
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
			return $this->redirect($this->generateUrl('lol_video_index'));
		}
		
		$params = array(
			'video' => $video,
			'can_edit'	=> false
		);
		
		if ($this->get('security.context')->isGranted('ROLE_USER')) {
			$user = $this->getUser();
			if (($video->getUser()->getId() == $user->getId()) || $this->get('security.context')->isGranted('ROLE_ADMIN')) {
				$params['can_edit'] = true;
				
				$params['video_categories'] = $videoManager->findAllVideoCatgories();
			}
		}
		
		return $this->render('MVNerdsVideoBundle:LoLVideoCenter:lol_video_detail.html.twig', $params);
	}
	
	/**
	 * Permet d'afficher la vidéo qu'elle provienne de youtube ou bien de dailymotion
	 */
	public function renderVideoAction(Video $video)
	{
		//On supprime la chaine "http://" si elle existe puis on supprime la chaine "www" si elle existe
		$videoLink = preg_replace('/^www\./', '', str_replace('http://', '', $video->getLink()));
		
		$response = null;
		// On vérifie si la vidéo provient de youtube
		if (strpos($videoLink, 'youtube.com/watch?v=') !== false || strpos($videoLink, 'youtu.be/') !== false) { 
			$embed = 'http://www.youtube.com/v/';
			
			if (strpos($videoLink, 'youtube.com') !== false) {
				$exploded = explode('&', str_replace('youtube.com/watch?v=', '', $videoLink));
			} 
			else {
				$exploded = explode('?', str_replace('youtu.be/', '', $videoLink));
			}
			
			$response = $this->render('MVNerdsVideoBundle:Videos:youtube.html.twig', array(
				'link'		=> $embed . $exploded[0]
			));
		} 
		// Sinon on vérifie si elle provient de dailymotion
		elseif (strpos($videoLink,'dailymotion.com') !== false) {
			$embed = 'http://www.dailymotion.com/embed/video/';
			
			if (strpos($videoLink, '/video/') !== false) {
				$exploded = explode('_', str_replace('dailymotion.com/video/', '', $videoLink));
			
				$response = $this->render('MVNerdsVideoBundle:Videos:dailymotion.html.twig', array(
					'link'		=> $embed . $exploded[0]
				));
			} 
			elseif (strpos($videoLink,'#video=') !== false) {
				$embed .= preg_replace('/dailymotion\.com\/.*#video=/', '', $videoLink);
				
				$response = $this->render('MVNerdsVideoBundle:Videos:dailymotion.html.twig', array(
					'link'		=> $embed
				));
			}
		}
		
		if (null == $response) {
			throw new HttpException(500, 'The provided link is not legal.');
		}
		
		return $response;
	}
}
