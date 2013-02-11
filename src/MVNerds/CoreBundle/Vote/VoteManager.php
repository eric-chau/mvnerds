<?php

namespace MVNerds\CoreBundle\Vote;

use MVNerds\CoreBundle\Model\VotePeer;
use MVNerds\CoreBundle\Model\VoteQuery;

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
	
	public function getVotesCount($object)
	{
		return VoteQuery::create()
			->select(array(VotePeer::LIKE))
			->addAsColumn('nbVotes', 'COUNT(*)')
			->groupBy(VotePeer::LIKE)
			->add(VotePeer::OBJECT_NAMESPACE, get_class($object))
			->add(VotePeer::OBJECT_ID, $object->getId())
		->count();
	}
}
