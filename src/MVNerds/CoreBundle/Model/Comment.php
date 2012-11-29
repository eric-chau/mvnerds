<?php

namespace MVNerds\CoreBundle\Model;

use MVNerds\CoreBundle\Model\om\BaseComment;
use MVNerds\CoreBundle\Model\User;

/**
 * Skeleton subclass for representing a row from the 'comment' table.
 *
 * 
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package    propel.generator.src.MVNerds.CoreBundle.Model
 */
class Comment extends BaseComment {
	public function didIReportThisComment(User $user)
	{
		foreach ($this->getUserReportComments() as $userReportComment) {
			if ($user->getId() == $userReportComment->getUserId()) {
				return true;
			}
		}
		
		return false;
	}
} // Comment
