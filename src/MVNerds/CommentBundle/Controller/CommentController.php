<?php

namespace MVNerds\CommentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class CommentController extends Controller
{
	public function renderCommentsAction($object, $objectType)
	{
		$commentArray = $this->get('mvnerds.comment_manager')->findByObject($object);
		
		return $this->render('MVNerdsCommentBundle:Comment:render_comments_block.html.twig', array(
			'object'		=> $object,
			'comments'		=> $commentArray['comments'],
			'object_type'	=> $objectType
		));
	}
	
	/**
	 * @Secure(roles="ROLE_USER")
	 * @Route("/{_locale}/comment/add", name="leave_comment", options={"expose"=true})
	 */
	public function leaveCommentAction()
	{
		$request = $this->getRequest();
		if (!$request->isXmlHttpRequest() || !$request->isMethod('POST'))
		{
			throw new HttpException(500, 'Request must be AJAX and POST method');
		}
		
		$objectSlug = $request->get('object_slug', null);
		$objectType = $request->get('object_type', null);
		$userSlug = $request->get('user_slug', null);
		$commentMsg = $request->get('comment_msg', null);
		$lastCommentID = $request->get('last_comment_id', 0);
		if ($objectSlug == null || $objectType == null || $userSlug == null || $commentMsg == null) {
			throw new HttpException(500, 'Des paramètres sont manquants !');
		}
		
		if (0 != strcmp($userSlug, $this->getUser()->getSlug())) {
			throw new AccessDeniedException();
		}
		
		$object = null;
		try {
			$object = $this->get('mvnerds.' . $objectType . '_manager')->findBySlug($objectSlug);
		}
		catch(Exception $e) {
			throw new InvalidArgumentException('Object not found for slug:`'. $objectSlug .'`');
		}
		
		$commentManager = $this->get('mvnerds.comment_manager');
		$commentManager->addComment($object, $this->getUser(), $commentMsg);
		
		$comments = $commentManager->getLastestComments($object, $lastCommentID / 47);

		return $this->render('MVNerdsCommentBundle:Comment:load_more_comments_list.html.twig', array(
			'comments'		=> $comments,
			'comment_count'	=> $object->getCommentCount()
		));
	}
	
	/**
	 * @Route("/{_locale}/comment/edit", name="comment_edit", options={"expose"=true})
	 * @Secure(roles="ROLE_USER")
	 */
	public function editCommentAction()
	{
		$request = $this->getRequest();
		if (!$request->isXmlHttpRequest() || !$request->isMethod('POST'))
		{
			throw new HttpException(500, 'Request must be AJAX and POST method');
		}
		
		$commentID = $request->get('comment_id', null);
		$userSlug = $request->get('user_slug', null);
		$commentMsg = $request->get('comment_msg', null);
		if ($userSlug == null || $commentMsg == null || $commentID == null) {
			throw new HttpException(500, 'Des paramètres sont manquants !');
		}
		
		if (0 != strcmp($userSlug, $this->getUser()->getSlug())) {
			throw new AccessDeniedException();
		}
		
		$comment = null;
		try {
			$comment = $this->get('mvnerds.comment_manager')->editComment($commentID / 47, $this->getUser(), $commentMsg);
		}
		catch (Exception $e) {
			throw new AccessDeniedException();
		}
		
		return new Response(json_encode(array(
			'content'			=> $comment->getContent()
			//'last_edition_date'	=> $comment->getLastUpdate()
		)));
	}
	
	/**
	 * @Route("/{_locale}/comment/load-more", name="comment_load_more", options={"expose"=true})
	 */
	public function loadMoreCommentAction()
	{
		$request = $this->getRequest();
		if (!$request->isXmlHttpRequest() || !$request->isMethod('POST'))
		{
			throw new HttpException(500, 'Request must be AJAX and POST method');
		}
		
		$objectSlug = $request->get('object_slug', null);
		$objectType = $request->get('object_type', null);
		$firstCommentID = $request->get('first_comment_id', null);
		if ($objectSlug == null || $objectType == null || $firstCommentID == null) {
			throw new HttpException(500, 'Des paramètres sont manquants !');
		}
		
		$object = null;
		try {
			$object = $this->get('mvnerds.' . $objectType . '_manager')->findBySlug($objectSlug);
		}
		catch(Exception $e) {
			throw new InvalidArgumentException('Object not found for slug:`'. $objectSlug .'`');
		}
		
		$commentArray = $this->get('mvnerds.comment_manager')->findByObject($object, $firstCommentID / 47);

		return $this->render('MVNerdsCommentBundle:Comment:load_more_comments_list.html.twig', array(
			'comments'		=> $commentArray['comments'],
			'comment_count'	=> $object->getCommentCount() - $commentArray['comment_count_since_first_load']
		));
	}

	/**
	 * @Route("/comment/report", name="comment_report", options={"expose"=true})
	 * @Secure(roles="ROLE_USER")
	 */
	public function reportCommentAction()
	{
		$request = $this->getRequest();
		if (!$request->isXmlHttpRequest() || !$request->isMethod('POST'))
		{
			throw new HttpException(500, 'Request must be AJAX and POST method');
		}

		$commentID = $request->get('comment_id', null);
		if (null == $commentID)
		{
			throw new HttpException(500, 'comment_id parameter is missing!');
		}

		$this->get('mvnerds.comment_manager')->doReportComment($this->getUser(), $commentID);

		return $this->render('MVNerdsCommentBundle:Common:report_success.html.twig');
	}
}
