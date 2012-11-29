<?php

namespace MVNerds\CommentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use JMS\SecurityExtraBundle\Annotation\Secure;

use MVNerds\CoreBundle\Comment\IComment;
use MVNerds\CoreBundle\Model\User;

class CommentController extends Controller
{
    public function renderLightCommentsAction($object)
    {
		$comments = $this->get('mvnerds.comment_manager')->findByObject($object, $this->getUser());
		
        return $this->render('MVNerdsCommentBundle:Light:render_comment_block.html.twig', array(
			'comments' => $comments
		));
    }
	
	/**
	 * @Secure(roles="ROLE_USER")
	 */
	public function leaveCommentAction(IComment $object, User $user, $commentMsg)
	{
		$comment = $this->get('mvnerds.comment_manager')->addComment($object, $user, $commentMsg);
		
		return $this->render('MVNerdsCommentBundle:Common:comment_row.html.twig', array(
			'comment' => $comment
		));
	}
	
	/**
	 * @Route("/comment/report", name="comment_report", options={"expose"=true})
	 * Secure(roles="ROLE_USER")
	 */
	public function reportCommentAction()
	{
		$request = $this->getRequest();
		if (!$request->isXmlHttpRequest() || !$request->isMethod('POST'))
		{
			throw new HttpException(500, 'Request must be AJAX and POST method');
		}
		
		$commentID = $request->get('comment_id', null);
		if (null == $commentID) {
			throw new HttpException(500, 'comment_id parameter is missing!');
		}
		
		$this->get('mvnerds.comment_manager')->doReportComment($this->getUser(), $commentID);
		
		return $this->render('MVNerdsCommentBundle:Common:report_success.html.twig');
	}
}
