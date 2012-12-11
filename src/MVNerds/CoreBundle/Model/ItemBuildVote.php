<?php

namespace MVNerds\CoreBundle\Model;



/**
 * Skeleton subclass for representing a row from one of the subclasses of the 'vote' table.
 *
 * 
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package    propel.generator.src.MVNerds.CoreBundle.Model
 */
class ItemBuildVote extends Vote {

	/**
	 * Constructs a new ItemBuildVote class, setting the class_key column to VotePeer::CLASSKEY_2.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->setClassKey(VotePeer::CLASSKEY_2);
	}

} // ItemBuildVote
