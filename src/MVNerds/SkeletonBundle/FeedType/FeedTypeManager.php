<?php

namespace MVNerds\SkeletonBundle\FeedType;

use \Criteria;

use MVNerds\CoreBundle\Exception\ObjectNotFoundException;
use MVNerds\CoreBundle\Model\FeedType;
use MVNerds\CoreBundle\Model\FeedTypeQuery;
use MVNerds\CoreBundle\Model\FeedTypePeer;

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
	public function customSave(FeedType $newFeedType, FeedType $oldFeedType)
	{
		$con = \Propel::getConnection(FeedTypePeer::DATABASE_NAME);
		
		$sql = "UPDATE `feed_type` 
				SET `unique_name`=:new_unique_name, 
					`is_private`=:new_is_private
				WHERE feed_type.unique_name=:old_unique_name";

		$stmt = $con->prepare($sql);
		$stmt->execute(array(
			':new_unique_name' => $newFeedType->getUniqueName(),
			':new_is_private' => $newFeedType->getIsPrivate(),
			':old_unique_name' => $oldFeedType->getUniqueName()
		));
	}
}