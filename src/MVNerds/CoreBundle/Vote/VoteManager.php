<?php

namespace MVNerds\CoreBundle\Vote;

use MVNerds\CoreBundle\Model\VotePeer;
use MVNerds\CoreBundle\Model\VoteQuery;
use MVNerds\CoreBundle\Vote\IVote;
use MVNerds\CoreBundle\Model\Vote;

class VoteManager
{
	public function findByObject($object)
	{
		$votes = VoteQuery::create()
			->joinWith('User')
			->add(VotePeer::OBJECT_NAMESPACE, get_class($object))
			->add(VotePeer::OBJECT_ID, $object->getId())
		->find();
		
		return $votes;
	}
	
	public function findByObjectAndUser($object, $user)
	{
		$votes = VoteQuery::create()
			->joinWith('User')
			->add(VotePeer::OBJECT_NAMESPACE, get_class($object))
			->add(VotePeer::OBJECT_ID, $object->getId())
			->add(VotePeer::USER_ID, $user->getId())
		->find();
		
		if (null === $votes || null === $votes[0])
		{
			throw new InvalidArgumentException('No votes foud for this object and user !');
		}

		return $votes[0];
	}
	
	public function vote(IVote $object ,$user, $like)
	{
		$like = $like == 'true' ? true : false;
		try {
			$vote = $this->findByObjectAndUser($object, $user);
			if ($vote->getLike() == $like) {
				return $vote;
			} else {
				$vote->setLike($like);
				$vote->save();
				if ($like) {
					$this->incrementObjectLikeCount($object);
					$this->decrementObjectDislikeCount($object);
				} else {
					$this->incrementObjectDislikeCount($object);
					$this->decrementObjectLikeCount($object);
				}
			}
			
		} catch (\Exception $e) {
			$vote = new Vote();
			$vote->setObjectId($object->getId());
			$vote->setObjectNamespace(get_class($object));
			$vote->setUser($user);
			$vote->setLike($like);
			$vote->save();
			if ($like) {
				$this->incrementObjectLikeCount($object);
			} else {
				$this->incrementObjectDislikeCount($object);
			}
		}
		
		
		return $vote;
	}
	
	private function incrementObjectLikeCount(IVote $object)
	{
		$object->setLikeCount($object->getLikeCount() + 1);
		if (method_exists($object, 'keepUpdateDateUnchanged')) {
			$object->keepUpdateDateUnchanged();
		}
		
		$object->save();
	}
	private function incrementObjectDislikeCount(IVote $object)
	{
		$object->setDislikeCount($object->getDislikeCount() + 1);
		if (method_exists($object, 'keepUpdateDateUnchanged')) {
			$object->keepUpdateDateUnchanged();
		}
		
		$object->save();
	}
	private function decrementObjectLikeCount(IVote $object)
	{
		if ($object->getLikeCount() >= 1) {
			$object->setLikeCount($object->getLikeCount() - 1);
			if (method_exists($object, 'keepUpdateDateUnchanged')) {
				$object->keepUpdateDateUnchanged();
			}

			$object->save();
		}
	}
	private function decrementObjectDislikeCount(IVote $object)
	{
		if ($object->getDislikeCount() >= 1) {
			$object->setDislikeCount($object->getDislikeCount() - 1);
			if (method_exists($object, 'keepUpdateDateUnchanged')) {
				$object->keepUpdateDateUnchanged();
			}

			$object->save();
		}
	}
}
