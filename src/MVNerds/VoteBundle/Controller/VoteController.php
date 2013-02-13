<?php

namespace MVNerds\VoteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

use MVNerds\CoreBundle\Vote\IVote;
use MVNerds\CoreBundle\Model\Vote;
/**
 * @Route("/vote")
 */
class VoteController extends Controller
{	
	public function renderVoteBlockAction(IVote $object, $objectType)
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
			} catch (\Exception $e) {
				$canLike = true;
				$canDislike = true;
			}
		}
		
		return $this->render('MVNerdsVoteBundle:Vote:vote_block.html.twig', array(
			'can_like'		=> $canLike,
			'can_dislike'		=> $canDislike,
			'votes_count'	=> $votesCount,
			'likes_count'		=> $likesCount,
			'dislikes_count'	=> $dislikesCount,
			'object_slug'		=> $object->getSlug(),
			'object_type'		=> $objectType
		));
	}
	
	/**
	 * @Route("/vote", name="vote_vote", options={"expose"=true})
	 * @Secure(roles="ROLE_USER")
	 */
	public function voteAction()
	{
		$request = $this->getRequest();
		if (!$request->isXmlHttpRequest() || !$request->isMethod('POST'))
		{
			throw new HttpException(500, 'Request must be AJAX and POST method');
		}
		
		$objectSlug = $request->get('object_slug', null);
		$objectType = $request->get('object_type', null);
		$like = $request->get('like', null);
		
		if ($objectSlug == null || $objectType == null || $like == null) {
			throw new HttpException(500, 'Missing parameters !');
		}
		
		$user = $this->getUser();
		
		try {
			$object = $this->get('mvnerds.' . $objectType . '_manager')->findBySlug($objectSlug);
		}
		catch(Exception $e) {
			throw new InvalidArgumentException('Object not found for slug:`'. $objectSlug .'`');
		}
		
		/* @var $voteManager \MVNerds\CoreBundle\Vote\VoteManager */
		$voteManager = $this->get('mvnerds.vote_manager');
		$vote = $voteManager->vote($object, $user, $like);
		
		$canDislike = false;
		$canLike = false;
		if ($vote->getLike()) {
			$canDislike = true;
		} else {
			$canLike = true;
		}
		
		return new Response(json_encode(array(
			'likeCount'		 => $object->getLikeCount(),
			'dislikeCount'	 => $object->getDislikeCount(),
			'canLike'		=> $canLike,
			'canDislike'		=> $canDislike
		)));
	}
}
