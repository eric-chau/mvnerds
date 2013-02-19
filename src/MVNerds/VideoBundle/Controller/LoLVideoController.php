<?php

namespace MVNerds\VideoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use MVNerds\CoreBundle\Model\Video;
use MVNerds\CoreBundle\Model\UserReport;
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
		
		$translator = $this->get('translator');
		
		//Si le paramètre slug est défini c'est une edition de vidéo
		if ( isset( $_POST['slug'] ) && ($slug = $_POST['slug']) != '' ) {
			try {
				/* @var $itemBuild \MVNerds\CoreBundle\Model\ItemBuild */
				$video = $videoManager->findBySlug($slug);
			} catch (\Exception $e ) {
				return new Response($translator->trans('error.video_not_found'), 400);
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
			return new Response($translator->trans('error.missing_title'), 400);
		}
		
		if ( isset( $_POST['category'] ) && ($category = $_POST['category']) != '' ) {
			try {
				$videoManager->findVideoCategoryById($category);
				$video->setVideoCategoryId($category);
			} catch (\Exception $e) {
				return new Response($translator->trans('error.video_category_not_valid'), 400);
			}
		} else {
			return new Response($translator->trans('error.missing_category'), 400);
		}
		
		if ( isset( $_POST['link'] ) && ($link = $_POST['link']) != '' ) {
			if (($formatedLink = $videoManager->formatVideoLink($link))) {
				$video->setLink($formatedLink);
			} else {
				return new Response($translator->trans('error.link_not_valid'), 400);
			}
		} else {
			return new Response($translator->trans('error.missing_link'), 400);
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
		
		/* @var $reportManager \MVNerds\CoreBundle\Report\ReportManager */
		$reportManager = $this->get('mvnerds.report_manager');
		
		try {
			/* @var $video \MVNerds\CoreBundle\Model\Video */
			$video = $videoManager->findBySlug($slug);
			$video->setView($video->getView() + 1);
			$video->keepUpdateDateUnchanged();
			$video->save();
		} catch (\Exception $e) {
			return $this->redirect($this->generateUrl('lol_video_index'));
		}
		
		$user = $this->getUser();
		
		try {
			$reportManager->findByObjectAndUser($video, $user);
			$canReportVideo = false;
		} catch (\Exception $e) {
			$canReportVideo = true;
		}
		$videoType = null;
		if (strpos($video->getLink(), 'youtube.com') !== false) {
			$videoType = 'youtube';
		} elseif (strpos($video->getLink(), 'dailymotion.com') !== false) {
			$videoType = 'dailymotion';
		}
		
		$relatedVideos = $videoManager->findRelatedVideos($video);
				
		$params = array(
			'video'			=> $video,
			'can_edit'		=> false,
			'video_type'		=> $videoType,
			'related_videos'	=> $relatedVideos,
			'can_report_video'	=> $canReportVideo,
			'report_motives'	=> UserReport::$REPORT_MOTIVES['video']
		);
		
		if ($this->get('security.context')->isGranted('ROLE_USER')) {
			if (($video->getUser()->getId() == $user->getId()) || $this->get('security.context')->isGranted('ROLE_ADMIN')) {
				$params['can_edit'] = true;
				
				$params['video_categories'] = $videoManager->findAllVideoCatgories();
			}
		}
		
		return $this->render('MVNerdsVideoBundle:LoLVideoCenter:lol_video_detail.html.twig', $params);
	}
}
