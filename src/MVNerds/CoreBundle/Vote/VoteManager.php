<?php

namespace MVNerds\CoreBundle\Vote;

use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

use MVNerds\CoreBundle\Model\VotePeer;
use MVNerds\CoreBundle\Model\VoteQuery;
use MVNerds\CoreBundle\Vote\IVote;
use MVNerds\CoreBundle\Model\Vote;
use MVNerds\CoreBundle\Model\User;
use MVNerds\CoreBundle\Vote\IRate;

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
	
	/**
	 * Contrairement à la méthode vote, uniqueVote ne permet pas à un utilisateur de voter pour un objet pour lequel 
	 * il a déjà voté au paravant même si c'est un vote différent.
	 * 
	 * @param \MVNerds\CoreBundle\Vote\IVote $object L'objet pour lequel l'utilisateur souhaite voter
	 * @param User $user L'utilisateur qui souhaite voter pour l'objet
	 * @param string $like "true" si l'utilisateur apprécie l'objet, et n'importe quelle autre chaine sinon
	 * 
	 * @return Vote L'objet correspondant au vote qui vient d'être effectué
	 */
	public function uniqueVote(IRate $object ,User $user, $like)
	{
		$like = $like == 'true' ? true : false;
		try {
			//On essaie de récupérer un vote pour cet utilisateur et cet objet
			$vote = $this->findByObjectAndUser($object, $user);
			//Si le vote est trouvé on ne permet pas de voter à nouveau et retourne directement l'objet Vote
			return $vote;
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
			$this->updateObjectRating($object, $like);
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
			$object->setVoteStatus('APPROVED');
			if (method_exists($object, 'keepUpdateDateUnchanged')) {
				$object->keepUpdateDateUnchanged();
			}
			
			$object->save();
		}
	}
	
	/**
	 * Permet de mettre à jour le compteur de votes de l'objet pour lequel l'utilisateur a voté
	 * 
	 * @param \MVNerds\CoreBundle\Vote\IRate $object L'objet pour lequel on veut mettre à jour le compte de votes
	 * @param $boolean $vote true si c'est un vote positif, false sinon
	 */
	private function updateObjectRating(IRate $object, $vote)
	{
		if ($vote) {
			$object->setRating($object->getRating() + 1);
		} else {
			$object->setRating($object->getRating() - 1 );
		}
		
		//TODO : Ajouter la prise en charge du passage au statut "is_red_post" pour les Feeds
		
		if (method_exists($object, 'keepUpdateDateUnchanged')) {
			$object->keepUpdateDateUnchanged();
		}
		
		$object->save();
	}
}
