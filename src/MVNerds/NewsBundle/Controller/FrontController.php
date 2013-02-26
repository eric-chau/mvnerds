<?php

namespace MVNerds\NewsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/news/old")
 */
class FrontController extends Controller
{

	/**
	 * @Route("/", name="old_news_index")
	 */
	public function indexAction()
	{
		if ($this->get('security.context')->isGranted('ROLE_NEWSER'))
		{
			$news = $this->get('mvnerds.news_manager')->findAllNotPrivate();
		} else {
			$news = $this->get('mvnerds.news_manager')->findAllPublic();
		}
		
		return $this->render('MVNerdsNewsBundle:Front:list_index.html.twig', array(
			'news'	=> $news
		));
	}
	
	/**
	 * @Route("/leave-comment", name="old_news_leave_comment", options={"expose"=true})
	 */
	public function leaveCommentAction()
	{
		$request = $this->getRequest();
		if (!$request->isXmlHttpRequest() || !$request->isMethod('POST'))
		{
			throw new HttpException(500, 'Request must be AJAX and POST method');
		}
		
		$newsSlug = $request->get('object_slug', null);
		$userSlug = $request->get('user_slug', null);
		$commentMsg = $request->get('comment_msg', null);
		$lastCommentID = $request->get('last_comment_id', null);
		if (null == $newsSlug || null == $userSlug || null == $commentMsg) {
			throw new HttpException(500, 'news_slug | user_slug | comment_msg is/are missing!');
		}
		
		if (0 != strcmp($userSlug, $this->getUser()->getSlug())) {
			throw new AccessDeniedException();
		}
		
		try {
			$news = $this->get('mvnerds.news_manager')->findBySlug($newsSlug);
		}
		catch(Exception $e) {
			throw new InvalidArgumentException('News not found for slug:`'. $newsSlug .'`');
		}
		
		return $this->forward('MVNerdsCommentBundle:Comment:leaveComment', array(
			'object'		=> $news,
			'user'			=> $this->getUser(),
			'commentMsg'	=> $commentMsg,
			'lastCommentID' => $lastCommentID
		));
	}
	
	/**
	 * @Route("/load-more-comment", name="old_news_load_more_comment", options={"expose"=true})
	 */
	public function loadMoreCommentAction()
	{
		$request = $this->getRequest();
		if (!$request->isXmlHttpRequest() || !$request->isMethod('POST'))
		{
			throw new HttpException(500, 'Request must be AJAX and POST method');
		}
		
		$newsSlug = $request->get('object_slug', null);
		$page = $request->get('page', null);
		if (null == $newsSlug || null == $page) {
			throw new HttpException(500, 'news_slug | page is/are missing!');
		}
		
		try {
			$news = $this->get('mvnerds.news_manager')->findBySlug($newsSlug);
		}
		catch(Exception $e) {
			throw new InvalidArgumentException('News not found for slug:`'. $newsSlug .'`');
		}
		
		return $this->forward('MVNerdsCommentBundle:Comment:loadMoreComment', array(
			'object'	=> $news,
			'page'		=> $page
		));
	}
	
	/**
	 * @Route("/{slug}", name="old_news_detail")
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
			$news->save();
			$news->setContent($this->get('mvnerds.bbcode_manager')->BBCode2Html($news->getContent()));
		} catch (\Exception $e) {
			return $this->redirect($this->generateUrl('launch_site_front'));
		}
		return $this->render('MVNerdsNewsBundle:Front:view_index.html.twig', array(
			'news' => $news
		));
	}
}
