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
		->find();
		
		return $votes;
	}
	
	public function getVotesCount($object)
	{
		$votes = VoteQuery::create()
			->select(array(VotePeer::LIKE))
			->addAsColumn('nbVotes', 'COUNT(*)')
			->groupBy(VotePeer::LIKE)
			->add(VotePeer::OBJECT_NAMESPACE, get_class($object))
			->add(VotePeer::OBJECT_ID, $object->getId())
		->find();
		return $votes;
	}
}
