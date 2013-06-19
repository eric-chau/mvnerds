<?php

namespace MVNerds\SkeletonBundle\FeedComment;

use MVNerds\CoreBundle\Exception\ObjectNotFoundException;
use MVNerds\CoreBundle\Model\FeedComment;
use MVNerds\CoreBundle\Model\FeedCommentQuery;

class FeedCommentManager
{
	/**
	 * @param integer $id l'id du feedComment à récupérer
	 * @throws ObjectNotFoundException si aucun feedComment n'est associé à l'id $id
	 * 
	 * @return FeedComment l'objet Feedcomment qui correspond à l'id $id 
	 */
	public function findById($id)
	{
		$feedComment = FeedCommentQuery::create()->findPk($id);

		if (!$feedComment instanceof Feed) {
			throw new ObjectNotFoundException('Le FeedComment ayant pour ID :' . $id . ' est introuvable');
		}

		return $feedComment;
	}
}