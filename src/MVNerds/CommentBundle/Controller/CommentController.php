<?php

namespace MVNerds\CommentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use JMS\SecurityExtraBundle\Exception\InvalidArgumentException;
use Exception;

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
			'content'			=> $comment->getContent(),
			'last_edition_date'	=> $this->get('translator')->trans('Comment.last_edition.DATE', array(
				'DATE' => $this->renderView(':Extension:custom_format_date.html.twig', array(
					'object' => $comment->getUpdateTime(), 
					'lowercase' => true
				))
			))
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
	 * @Route("/{_locale}/comment/add-response", name="comment_reply", options={"expose"=true})
	 * @Secure(roles="ROLE_USER")
	 */
	public function addResponseAction()
	{
		$request = $this->getRequest();
		if (!$request->isXmlHttpRequest() || !$request->isMethod('POST'))
		{
			throw new HttpException(500, 'Request must be AJAX and POST method');
		}
		
		$userSlug = $request->get('user_slug', null);
		$commentID = $request->get('comment_id', null);
		$replyMsg = $request->get('reply_msg', null);
		if ($userSlug == null || $commentID == null || $replyMsg == null) {
			throw new HttpException(500, 'Des paramètres sont manquants !');
		}
		
		if (0 != strcmp($userSlug, $this->getUser()->getSlug())) {
			throw new AccessDeniedException();
		}
		
		$response = null;
		try {
			$response = $this->get('mvnerds.comment_manager')->addResponseToComment($commentID / 47, $this->getUser(), $replyMsg);
		}
		catch(Exception $e) {
			throw new InvalidArgumentException('Comment not found for id:`'. $commentID .'`');
		}
		
		return $this->render('MVNerdsCommentBundle:Comment:response_row.html.twig', array(
			'response' => $response
		));
	}
	
	/**
	 * @Route("/{_locale}/response/edit", name="response_edit", options={"expose"=true})
	 * @Secure(roles="ROLE_USER")
	 */
	public function editResponseAction()
	{
		$request = $this->getRequest();
		if (!$request->isXmlHttpRequest() || !$request->isMethod('POST'))
		{
			throw new HttpException(500, 'Request must be AJAX and POST method');
		}
		
		$responseID = $request->get('response_id', null);
		$userSlug = $request->get('user_slug', null);
		$responseMsg = $request->get('response_msg', null);
		if ($userSlug == null || $responseMsg == null || $responseID == null) {
			throw new HttpException(500, 'Des paramètres sont manquants !');
		}
		
		if (0 != strcmp($userSlug, $this->getUser()->getSlug())) {
			throw new AccessDeniedException();
		}
		
		$response = null;
		try {
			$response = $this->get('mvnerds.comment_manager')->editResponse($responseID / 47, $this->getUser(), $responseMsg);
		}
		catch (Exception $e) {
			throw new AccessDeniedException();
		}
		
		return new Response(json_encode(array(
			'content'			=> $response->getContent(),
			'last_edition_date'	=> $this->get('translator')->trans('Comment.last_edition.DATE', array(
				'DATE' => $this->renderView(':Extension:custom_format_date.html.twig', array(
					'object' => $response->getUpdateTime(), 
					'lowercase' => true
				))
			))
		)));
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
	
	/**
	 * @Route("/{_locale}/comment-{commentId}/redirect-to-related-object", name="profile_redirect_to_related_object")
	 */
	public function redirectCommentToRelatedObjectDetailAction($commentId)
	{
		$relatedObject = null;
		try {
			$relatedObject = $this->get('mvnerds.comment_manager')->getRelatedObjectByCommentId($commentId / 47);
		}
		catch (Exception $e) {
			throw new AccessDeniedException();
		}
		
		$type = get_class($relatedObject);
		
		$routeName = '';
		switch ($type) {
			case 'MVNerds\CoreBundle\Model\ItemBuild':
				$routeName = 'pmri_list_detail';
				break;
			case 'MVNerds\CoreBundle\Model\User':
				$routeName = 'summoner_profile_view';
				break;
			case 'MVNerds\\CoreBundle\\Model\\Video':
				$routeName = 'videos_detail';
				break;
			case 'MVNerds\\CoreBundle\\Model\\Champion':
				$routeName = 'champion_detail';
				break;
			case 'MVNerds\\CoreBundle\\Model\\News':
				$routeName = 'news_detail';
				break;
			default:
				throw new HttpException(500, 'Unknow type.');
		}
		
		return $this->redirect($this->generateUrl($routeName, array(
			'slug' => $relatedObject->getSlug()
		)));
	}
}
