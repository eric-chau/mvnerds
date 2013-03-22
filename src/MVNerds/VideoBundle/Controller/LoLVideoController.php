<?php

namespace MVNerds\VideoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use JMS\SecurityExtraBundle\Annotation\Secure;

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
		$videoManager->findAllActiveAjax();
		return $this->render('MVNerdsVideoBundle:LoLVideoCenter:lol_video_index.html.twig', array(
			'video_categories'	=> $videoManager->findAllVideoCatgories(),
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
		
		if ( isset( $_POST['description'] )) {
			$video->setDescription($_POST['description']);
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
		if (!$request->isXmlHttpRequest()) {
			throw new HttpException(500, 'Request must be AJAX');
		}
		
		$aColumns = array(
			'',
			'',
			'',
			'user.username',
			'create_time',
			'update_time',
			'title',
			'video.view',
			'video_category.unique_name',
			'video.comment_count',
			'like_count / (like_count + dislike_count) * 100'
		);
		
		$limitStart = 0;
		$limitLength = -1;
		//Pagination
		if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' ) {
			$limitStart = $_GET['iDisplayStart'];
			$limitLength = $_GET['iDisplayLength'];
		}
		//Tri
		$orderArr = array();
		if ( isset( $_GET['iSortCol_0'] ) ) {
			for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ ) {
				if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" ) {
					$orderArr[$aColumns[intval($_GET['iSortCol_'.$i])]] = ($_GET['sSortDir_'.$i]);
					//Si on veut trier par votes
					if (intval($_GET['iSortCol_'.$i]) == 10) {
						$orderArr['like_count'] = 'desc';
					}
				}
			}
		}
		if (count($orderArr) <= 0) {
			$orderArr = array('Create_Time' => 'desc');
		}
		//Recherche par colonne
		$whereArr = array();
		for ( $i=0 ; $i<count($aColumns) ; $i++ ) {
			if ( isset($_GET['bSearchable_'.$i]) && $_GET['bSearchable_'.$i] == "true" && $_GET['sSearch_'.$i] != '' ) {
				if ($aColumns[$i] == 'user.username' || $aColumns[$i] == 'title' || $aColumns[$i] == 'video_category.unique_name') {
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
		
		foreach($videos as $video) {	
			$voteCount = $video->getLikeCount() + $video->getDislikeCount();
			$rating = $voteCount > 0 ? $video->getLikeCount() / ($voteCount) * 100 : 0;
			
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
				$video->getCommentCount(),
				$rating
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
		
		$relatedVideos = $videoManager->findRelatedVideos($video);
		
		$params = array(
			'video'				=> $video,
			'can_edit'			=> false,
			'related_videos'	=> $relatedVideos
		);
		
		if ($this->get('security.context')->isGranted('ROLE_USER')) {
			if ( ($user = $this->getUser()) && (($video->getUser()->getId() == $user->getId()) || $this->get('security.context')->isGranted('ROLE_ADMIN')) ) {
				$params['can_edit'] = true;
				
				$params['video_categories'] = $videoManager->findAllVideoCatgories();
			}
		}
		
		return $this->render('MVNerdsVideoBundle:LoLVideoCenter:lol_video_detail.html.twig', $params);
	}
	
	/**
	 * @Route("/delete/{slug}", name="videos_delete")
	 * @Secure(roles="ROLE_USER")
	 */
	public function deleteAction($slug) 
	{
		/* @var $videoManager \MVNerds\CoreBundle\Video\VideoManager */
		$videoManager = $this->get('mvnerds.video_manager');
			
		try {
			/* @var $video \MVNerds\CoreBundle\Model\Video */
			$video = $videoManager->findBySlug($slug);
		} catch (\Exception $e ) {
			return $this->redirect($this->generateUrl('summoner_profile_index'));
		}
		
		if($this->getUser()->getId() == $video->getUserId() || $this->get('security.context')->isGranted('ROLE_ADMIN')) {
			$video->delete();
		}
		
		return $this->redirect($this->generateUrl('summoner_profile_index'));
	}
	
	public function renderVideosAction()
	{
		return $this->render('MVNerdsVideoBundle:LoLVideoCenter:render_videos_block.html.twig', array(
			'newest_videos'			=> $this->get('mvnerds.video_manager')->findNewestVideos(),
			'most_viewed_videos'	=> $this->get('mvnerds.video_manager')->findMostViewedVideos()
		));
	}
}
