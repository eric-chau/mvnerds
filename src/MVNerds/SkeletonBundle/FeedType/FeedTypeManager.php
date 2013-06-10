<?php

namespace MVNerds\SkeletonBundle\FeedType;

use \Criteria;

use MVNerds\CoreBundle\Exception\ObjectNotFoundException;
use MVNerds\CoreBundle\Model\FeedType;
use MVNerds\CoreBundle\Model\FeedTypeQuery;
use MVNerds\CoreBundle\Model\FeedTypePeer;
use MVNerds\CoreBundle\Model\FeedQuery;
use MVNerds\CoreBundle\Model\FeedPeer;

class FeedTypeManager
{
	/**
	 * @param string $uniqueName le unique_name du feed_type à récupérer
	 * @throws ObjectNotFoundException si aucun feedType n'est associé au unique_name $uniqueName
	 * 
	 * @return FeedType l'objet FeedType qui correspond au unique_name $uniqueName
	 */
	public function findByUniqueName($uniqueName)
	{
		$feedType = FeedTypeQuery::create()->findPk($uniqueName);

		if (!$feedType instanceof FeedType) {
			throw new ObjectNotFoundException('Le FeedType ayant pour UniqueName :' . $uniqueName . ' est introuvable');
		}

		return $feedType;
	}
	
	/**
	 * @param string $uniqueName le unique_name du feed_type à supprimer
	 * @throws ObjectNotFoundException si aucun feed_type n'est associé au unique_name $uniqueName
	 */
	public function deleteByUniqueName($uniqueName)
	{
		$feedType = FeedTypeQuery::create()->findPk($uniqueName);

		if (!$feedType instanceof FeedType) {
			throw new ObjectNotFoundException('Le FeedType ayant pour UniqueName :' . $uniqueName . ' est introuvable');
		}

		$feedType->delete();
	}
	
	/**
	 * @return \PropelCollection<MVNerds\CoreBundle\Model\FeedType> retourne un objet PropelCollection qui contient
	 * tous les feed_type de la base de données
	 */
	public function findAll()
	{
		return FeedTypeQuery::create()
			->OrderBy(FeedTypePeer::UNIQUE_NAME, Criteria::ASC)
		->find();
	}
	
	/**
	 * Permet d'éditer un feed type en changeant sa primary key 'unique_name'
	 * 
	 * @param \MVNerds\CoreBundle\Model\FeedType $newFeedType Les nouvelles données à affecter à l'ancien feed type
	 * @param \MVNerds\CoreBundle\Model\FeedType $oldFeedType Le feed type que l'on veut éditer
	 */
	public function update(FeedType $newFeedType, FeedType $oldFeedType)
	{		
		//Si le unique_name a été modifié
		if ($newFeedType->getUniqueName() != $oldFeedType->getUniqueName()) {
			//On met à jour manuellement le feed_type
			FeedTypeQuery::create()
				->add(FeedTypePeer::UNIQUE_NAME, $oldFeedType->getUniqueName())
			->update(array(
				'UniqueName' => $newFeedType->getUniqueName(),
				'IsPrivate' => $newFeedType->getIsPrivate()
			));
			
			//Et on met à jour les objets feed reliés à ce feed_type
			FeedQuery::create()
				->add(FeedPeer::TYPE_UNIQUE_NAME, $oldFeedType->getUniqueName())
			->update(array('TypeUniqueName' => $newFeedType->getUniqueName()));
		} else {
			//Sinon on fait un simple save
			$newFeedType->save();
		}
	}
}