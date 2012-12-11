<?php

namespace MVNerds\VoteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use JMS\SecurityExtraBundle\Annotation\Secure;

use MVNerds\CoreBundle\Model\ItemBuild;

/**
 * @Route("/vote")
 */
class VoteController extends Controller
{	
	/**
	 * @Route("/render-block", name="vote_render_block", options={"expose"=true})
	 */
	public function renderVoteBlockAction($object)
	{		
		/* @var $voteManager MVNerds\CoreBundle\Vote\VoteManager */
		$voteManager  = $this->get('mvnerds.vote_manager');
		
		$votesCount = $voteManager->getVotesCount($object);
		$likesCount = 0;
		$dislikesCount = 0;
		if ($votesCount) {
			foreach($votesCount as $voteCount)
			{
				if ($voteCount['vote.LIKE'] == 1) {
					$likesCount = $voteCount['nbVotes'];
				} elseif ($voteCount['vote.LIKE'] == 0) {
					$dislikesCount = $voteCount['nbVotes'];
				}
			}
		}
		$totalVotesCount = $likesCount + $dislikesCount;
		
		$canVote = false;
		
		if ($this->get('security.context')->isGranted('ROLE_USER'))
		{
			$user = $this->get('security.context')->getToken()->getUser();
			try {
				$userVote = $voteManager->findByObjectAndUser($object, $user);
			} catch (\Exception $e) {
				$userVote = null;
			}
		}
		
		return $this->render('MVNerdsVoteBundle:Vote:vote_block.html.twig', array(
			'can_vote'		=> $canVote,
			'total_votes_count'	=> $totalVotesCount,
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
