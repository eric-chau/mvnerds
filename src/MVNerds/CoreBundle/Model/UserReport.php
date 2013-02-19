<?php

namespace MVNerds\CoreBundle\Model;

use MVNerds\CoreBundle\Model\om\BaseUserReport;


/**
 * Skeleton subclass for representing a row from the 'user_report' table.
 *
 * 
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package    propel.generator.src.MVNerds.CoreBundle.Model
 */
class UserReport extends BaseUserReport {

	public static $REPORT_MOTIVES = array(
		'video' => array(
			'Contenu non approprié !',
			'Vidéo dupliquée sur le site.'
		)
	);
} // UserReport
