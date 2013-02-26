<?php

namespace MVNerds\CoreBundle\Vote;

use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

use MVNerds\CoreBundle\Model\VotePeer;
use MVNerds\CoreBundle\Model\VoteQuery;
use MVNerds\CoreBundle\Vote\IVote;
use MVNerds\CoreBundle\Model\Vote;

class VoteManager
{
	/**
	 * Le nombre minimum de votes à atteindre avant que l'objet ne passe en vote_status : APPROVED
	 */
	const MIN_VOTES = 20;
	
	/**
	 * Le taux minimum de votes à atteindre avant que l'objet ne passe en vote_status : APPROVED
	 */
	const MIN_RATING = 75;
	
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
	
		if (null === $votes || count($votes) <= 0)
		{
			throw new InvalidArgumentException('No votes foud for this object and user !');
		}

		return $votes[0];
	}
	
	public function vote(IVote $object ,$user, $like)
	{
		$like = $like == 'true' ? true : false;
		try {
			//Si un vote a déjà été trouvé pour cet utilisateur et cet objet
			$vote = $this->findByObjectAndUser($object, $user);
			//Si la valeur du vote reste inchangée
			if ($vote->getLike() == $like) {
				//On retourne directement le vote
				return $vote;
			} else {
				//Sinon on met à jour la valeur du vote
				$vote->setLike($like);
				$vote->save();
				//et on met à jour les compteurs de vote de l objet
				if ($like) {
					$this->incrementObjectLikeCount($object);
					$this->decrementObjectDislikeCount($object);
					//Si c est un like on met également à jour le vote_status
					$this->updateObjectVoteStatus($object);
				} else {
					$this->incrementObjectDislikeCount($object);
					$this->decrementObjectLikeCount($object);
				}
			}
			
		} catch (\Exception $e) {
			//Si une exception est levée celà signifie qu'aucun vote n'a été trouvé pour cet utilisateur et cet objet
			//On crée alors un nouvel objet Vote
			$vote = new Vote();
			$vote->setObjectId($object->getId());
			$vote->setObjectNamespace(get_class($object));
			$vote->setUser($user);
			$vote->setLike($like);
			$vote->save();
			//On met à jour les compteurs de l objet
			if ($like) {
				$this->incrementObjectLikeCount($object);
				//Si c est un like on met également à jour le vote_status
				$this->updateObjectVoteStatus($object);
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
	
	private function updateObjectVoteStatus(IVote $object)
	{
		$votesCount = $object->getLikeCount() + $object->getDislikeCount();
		$rating = round($object->getLikeCount() / $votesCount * 100);
		
		if ($object->getVoteStatus() != 'FEATURED' &&  $votesCount >= self::MIN_VOTES && $rating >= self::MIN_RATING) {
			$object->setReportStatus('APPROVED');
			if (method_exists($object, 'keepUpdateDateUnchanged')) {
				$object->keepUpdateDateUnchanged();
			}
			
			$object->save();
		}
	}
}
