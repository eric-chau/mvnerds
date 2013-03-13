<?php

namespace MVNerds\NewsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/news")
 */
class NewsController extends Controller
{

	/**
	 * @Route("/", name="news_index")
	 */
	public function indexAction()
	{
		$newsManager = $this->get('mvnerds.news_manager');
		if ($this->get('security.context')->isGranted('ROLE_NEWSER')){
			$news = $newsManager->findAllNotPrivate();
		} else {
			$news = $newsManager->findAllPublic();
		}
		
		return $this->render('MVNerdsNewsBundle:News:news_index.html.twig', array(
			'news'	=> $news,
			'news_categories' => $newsManager->findAllNewsCategories()
		));
	}
	
	/**
	 * Action appelée par datatables pour charger le tableau de news en ajax
	 * @Route("/list-ajax", name="news_list_ajax", options={"expose"=true})
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
			'user.USERNAME',
			'Create_Time',
			'Update_Time',
			'news_i18n.Title',
			'View',
			'news_category.UNIQUE_NAME',
			'Comment_Count'
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
				}
			}
		}
		if (count($orderArr) <= 0) {
			$orderArr = array('Create_Time' => 'desc');
		}
		//Recherche par colonne
		$whereArr = array();
		for ( $i=0 ; $i<count($aColumns) ; $i++ )
		{
			if ( isset($_GET['bSearchable_'.$i]) && $_GET['bSearchable_'.$i] == "true" && $_GET['sSearch_'.$i] != '' )
			{
				if ($aColumns[$i] == 'user.USERNAME' || $aColumns[$i] == 'news_i18n.Title' || $aColumns[$i] == 'news_category.UNIQUE_NAME')
				{
					$whereArr[$aColumns[$i]] = ($_GET['sSearch_'.$i]);
				}
			}
		}
		
		$translator = $this->get('translator');
		$newsManager = $this->get('mvnerds.news_manager');
		
		$onlyPublic = true;
		if ($this->get('security.context')->isGranted('ROLE_NEWSER')){
			$onlyPublic = false;
		}
		
		$news = $newsManager->findAllAjax($onlyPublic, $limitStart, $limitLength, $orderArr, $whereArr);
		
		$jsonNews = array(
			"tab" => $news->count(),
			"sEcho" => intval($_GET['sEcho']),
			"iTotalRecords" => $newsManager->countAll($onlyPublic),
			"iTotalDisplayRecords" => $newsManager->countAllAjax($onlyPublic, $whereArr),
			'aaData' => array()
		);
		
		foreach($news as $pieceOfNews)
		{
			$jsonNews['aaData'][] = array(
				$this->renderView('MVNerdsNewsBundle:News:news_index_table_preview_cell.html.twig', array('news' => $pieceOfNews)),
				$this->renderView('MVNerdsNewsBundle:News:news_index_table_title_cell.html.twig', array('news' => $pieceOfNews, 'user' => $pieceOfNews->getUser())),
				$translator->trans($pieceOfNews->getNewsCategory()->getUniqueName()),
				$pieceOfNews->getUser()->getUsername(),
				$pieceOfNews->getCreateTime('YmdHims'),
				$pieceOfNews->getUpdateTime('YmdHims'),
				$pieceOfNews->getTitle(),
				$pieceOfNews->getView(),
				$pieceOfNews->getNewsCategory()->getUniqueName(),
				$pieceOfNews->getCommentCount()
			);
		}
		return new Response(json_encode($jsonNews));
	}
	
	/**
	 * @Route("/{slug}", name="news_detail")
	 */
	public function viewAction($slug)
	{
		try {
			/* @var $news \MVNerds\CoreBundle\Model\News */
			if ($this->get('security.context')->isGranted('ROLE_NEWSER'))
			{
				$news = $this->get('mvnerds.news_manager')->findBySlug($slug);
			} else {
				$news = $this->get('mvnerds.news_manager')->findPublicBySlug($slug);
			}
			
			$news->setView($news->getView() + 1);
			$news->keepUpdateDateUnchanged();
			$news->save();
			$news->setContent($this->get('mvnerds.bbcode_manager')->BBCode2Html($news->getContent()));
		} catch (\Exception $e) {
			return $this->redirect($this->generateUrl('site_homepage'));
		}
		return $this->render('MVNerdsNewsBundle:News:news_detail.html.twig', array(
			'news'			=> $news,
			'related_news'	=> $this->get('mvnerds.news_manager')->findRelatedNews($news)
		));
	}
}
