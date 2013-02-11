<?php

namespace MVNerds\VoteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use JMS\SecurityExtraBundle\Annotation\Secure;

use MVNerds\CoreBundle\Vote\IVote;
/**
 * @Route("/vote")
 */
class VoteController extends Controller
{	
	public function renderVoteBlockAction(IVote $object)
	{		
		/* @var $voteManager MVNerds\CoreBundle\Vote\VoteManager */
		$voteManager  = $this->get('mvnerds.vote_manager');
		
		$likesCount = $object->getLikeCount();
		$dislikesCount = $object->getDislikeCount();
		$votesCount = $likesCount + $dislikesCount;
		
		$canLike = false;
		$canDislike = false;
		
		if ($this->get('security.context')->isGranted('ROLE_USER'))
		{
			$user = $this->getUser();
			try {
				/* @var $vote \MVNerds\CoreBundle\Model\Vote */
				$vote = $voteManager->findByObjectAndUser($object, $user);
				if ($vote->getLike()) {
					$canDislike = true;
				} else {
					$canLike = true;
				}
			} catch (\Exception $e) {}
		}
		
		return $this->render('MVNerdsVoteBundle:Vote:vote_block.html.twig', array(
			'can_like'		=> $canLike,
			'can_dislike'		=> $canDislike,
			'votes_count'	=> $votesCount,
			'likes_count'		=> $likesCount,
			'dislikes_count'	=> $dislikesCount
		));
	}
	
	/**
	 * @Route("/like", name="vote_like", options={"expose"=true})
	 * @Secure(roles="ROLE_USER")
	 */
	public function likeAction()
	{
		return $this->render('MVNerdsVoteBundle:Default:index.html.twig', array('name' => $name));
	}
	
	/**
	 * @Route("/dislike", name="vote_dislike", options={"expose"=true})
	 * @Secure(roles="ROLE_USER")
	 */
	public function dislikeAction()
	{
		return $this->render('MVNerdsVoteBundle:Default:index.html.twig', array('name' => $name));
	}

}
