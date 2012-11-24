<?php

namespace MVNerds\CommentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use JMS\SecurityExtraBundle\Annotation\Secure;

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
	 * @Route("/leave-comment", name="leave_comment", options={"expose"=true})
	 * @Secure(roles="ROLE_USER")
	 */
	public function leaveCommentAction()
	{
		
	}
}
