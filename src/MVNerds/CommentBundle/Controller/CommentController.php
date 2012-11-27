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
		$comments = $this->get('mvnerds.comment_manager')->findByObject($object);
		
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
}
